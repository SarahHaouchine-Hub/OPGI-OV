<?php

namespace App\Http\Controllers;

use App\Models\Aide;
use App\Models\CreditBancaire;
use App\Models\Logement;
use App\Models\Ov;
use App\Models\Paiement;
use App\Models\Souscripteur;
use App\Traits\RoleAccess;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Vinkla\Hashids\Facades\Hashids;

class OvController extends Controller
{
    use RoleAccess;

    // ── Tranches fixes LPA (% de Prix2 = Prix − BNH) ──────────────────────────
    private const LPA_TRANCHES = [
        1 => 20,
        2 => 15,
        3 => 35,
        4 => 25,
        5 => 5,
    ];

    // FNPOS toujours 500 000 DA (déduite uniquement de T4)
    private const FNPOS_MONTANT_FIXE = 500000.00;

    // =========================================================================
    // INDEX — liste filtrée selon le rôle
    // =========================================================================
    public function index(Request $request)
    {
        $query = Souscripteur::with([
            'logement.programme',
            'ovs.paiement',
            'aides',
            'creditBancaire',
        ])->whereHas('logement', fn($q) => $q->whereIn('flag', [1, 2]));

        // ── Restriction par programme selon le rôle ──────────────────────────
        $query = $this->applyProgrammeFilter($query->whereHas('logement', fn($q) => $q->whereIn('flag', [1, 2])));
        // On réapplique le filtre flag proprement :
        $query = Souscripteur::with([
            'logement.programme',
            'ovs.paiement',
            'aides',
            'creditBancaire',
        ])->whereHas('logement', function ($q) {
            $q->whereIn('flag', [1, 2]);
            // Filtre programme dans le même whereHas
            $allowed = $this->getAllowedProgrammes();
            if ($allowed) {
                $q->whereHas('site.programme', function ($prog) use ($allowed) {
                    $prog->where(function ($inner) use ($allowed) {
                        foreach ($allowed as $key) {
                            $inner->orWhereRaw('UPPER(libelle) LIKE ?', ['%' . $key . '%']);
                        }
                    });
                });
            }
        });

        if ($request->filled('programme')) {
            $query->whereHas('logement.programme', function ($q) use ($request) {
                $q->where('libelle', $request->programme);
            });
        }
        if ($request->filled('site')) {
            $query->whereHas('logement.programme', function ($q) use ($request) {
                $q->where('site', 'like', '%' . $request->site . '%');
            });
        }
        if ($request->filled('souscripteur')) {
            $search = $request->souscripteur;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%");
            });
        }
        if ($request->filled('code')) {
            $query->where('code_loge_lpl', 'like', '%' . $request->code . '%');
        }
        if ($request->filled('batiment')) {
            $query->whereHas('logement', function ($q) use ($request) {
                $q->where('num_batiment', 'like', '%' . $request->batiment . '%');
            });
        }

        $souscripteurs = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('listeOv', compact('souscripteurs'));
    }

    // =========================================================================
    // CREATE — routage par programme + vérification du rôle
    // =========================================================================
    public function create($id)
    {
        $souscripteur = Souscripteur::with([
            'logement.programme',
            'logement.site',
            'ovs.paiement',
            'aides',
            'creditBancaire',
        ])->findOrFail($id);

        // ── Restriction par programme selon le rôle ──────────────────────────
        $programmeLibelle = $souscripteur->logement->programme->libelle ?? '';
        if (!$this->canAccessProgramme($programmeLibelle)) {
            return redirect()->route('ov.index')
                ->with('error', $this->accessDeniedMessage());
        }

        // ── Restriction : 1er OV réservé au DG ──────────────────────────────
        if ($this->isFirstOv($souscripteur) && !$this->userCanGenerateFirstOv()) {
            return redirect()->route('ov.index')
                ->with('error', 'La génération du premier ordre de versement est réservée au profil DG.');
        }

        $programme = $this->getProgrammeType($souscripteur);

        return match ($programme) {
            'LPA' => $this->createLpa($souscripteur),
            'LSP' => $this->createLsp($souscripteur),
            default => $this->createLpl($souscripteur),
        };
    }

    // ── LPL ───────────────────────────────────────────────────────────────────
    private function createLpl(Souscripteur $souscripteur)
    {
        $prixLogement = $souscripteur->logement->prix ?? 0;
        $dernierOv    = $souscripteur->ovs()->latest()->first();
        $reste        = $dernierOv ? $dernierOv->montant_restant : $prixLogement;
        $code_loge    = $souscripteur->code_loge_lpl;

        if ($reste <= 0) {
            return redirect()->route('ov.index')
                ->with('error', 'Le souscripteur a déjà payé la totalité du prix.');
        }

        return view('createOv', compact('souscripteur', 'prixLogement', 'reste', 'code_loge'));
    }

    // ── LPA ───────────────────────────────────────────────────────────────────
    private function createLpa(Souscripteur $souscripteur)
    {
        $prixLogement       = (float) ($souscripteur->logement->prix ?? 0);
        $aideBnh            = $souscripteur->aides->firstWhere('type', 'bnh');
        $aideFnpos          = $souscripteur->aides->firstWhere('type', 'fnpos');
        $creditBancaire     = $souscripteur->creditBancaire ?? null;
        $ovsDone            = $souscripteur->ovs->sortBy('numero_tranche');
        $ovsDoneNormaux     = $ovsDone->where('type_ov', null);
        $prochaineTranche   = $ovsDoneNormaux->count() + 1;
        $code_loge          = $souscripteur->code_loge_lpl;
        $site               = $souscripteur->logement->site;

        if ($prochaineTranche > 5 && $creditBancaire === null) {
            return redirect()->route('ov.index')
                ->with('error', 'Toutes les tranches LPA ont été générées.');
        }

        if ($creditBancaire !== null) {
            $ovT2Fait  = $ovsDone->contains(fn($o) => $o->numero_tranche === 2 && in_array($o->type_ov, ['credit_reel', null]));
            $ovT3Fait  = $ovsDone->contains(fn($o) => $o->numero_tranche === 3 && in_array($o->type_ov, ['credit_diff', null]));
            $diffCredit = $creditBancaire->montant_attestation - $creditBancaire->montant_reel;
            $dossierSolde = $ovT2Fait && ($diffCredit <= 0 || $ovT3Fait);
            if ($dossierSolde) {
                return redirect()->route('ov.index')
                    ->with('success', 'Dossier soldé — crédit bancaire entièrement traité.');
            }
        }

        $montantBnh  = (float) ($aideBnh->montant ?? 0);
        $fnposMontant = self::FNPOS_MONTANT_FIXE;
        $prix2        = max(0.0, $prixLogement - $montantBnh);
        $totalPaye    = (float) $ovsDone->where('type_ov', null)->sum('montant_paye');
        $baseCalcul   = max(0.0, $prix2 - $totalPaye);

        if ($prochaineTranche === 5) {
            $montantTranche = $baseCalcul;
            $pourcentage    = round(($montantTranche / max(1, $prix2)) * 100, 2);
        } else {
            $pourcentage    = self::LPA_TRANCHES[$prochaineTranche] ?? 0;
            $montantTranche = round($prix2 * $pourcentage / 100, 2);
            if ($prochaineTranche === 4 && $aideFnpos) {
                $montantTranche = max(0.0, $montantTranche - $fnposMontant);
            }
        }

        $montantRestant = max(0.0, $baseCalcul - $montantTranche);
        $tranches       = self::LPA_TRANCHES;
        $ovT2Normal     = $ovsDone->where('type_ov', null)->where('numero_tranche', 2)->first();

        $peutAfficherCredit = (
            $ovsDone->where('type_ov', null)->count() >= 1 &&
            $aideBnh !== null &&
            $ovT2Normal === null
        );

        $montantAttestationAuto = null;
        if ($ovsDone->where('type_ov', null)->count() >= 1) {
            $montantT1              = (float) ($ovsDone->firstWhere('numero_tranche', 1)->montant_paye ?? 0);
            $fnposDeduit            = $aideFnpos ? $fnposMontant : 0.0;
            $montantAttestationAuto = max(0.0, ($prix2 - $montantT1) - $fnposDeduit);
        }

        return view('createOvLpa', compact(
            'souscripteur', 'prixLogement', 'aideBnh', 'aideFnpos',
            'ovsDone', 'prochaineTranche', 'tranches', 'pourcentage',
            'montantTranche', 'montantRestant', 'totalPaye',
            'montantBnh', 'fnposMontant',
            'prix2', 'baseCalcul', 'code_loge',
            'creditBancaire', 'peutAfficherCredit',
            'montantAttestationAuto', 'site'
        ));
    }

    // ── LSP ───────────────────────────────────────────────────────────────────
    private function createLsp(Souscripteur $souscripteur)
    {
        $prixLogement   = $souscripteur->logement->prix ?? 0;
        $aideBnh        = $souscripteur->aides->firstWhere('type', 'bnh');
        $aideFnpos      = $souscripteur->aides->firstWhere('type', 'fnpos');
        $totalAides     = ($aideBnh->montant ?? 0) + ($aideFnpos->montant ?? 0);
        $ovsDone        = $souscripteur->ovs->sortBy('numero_tranche');
        $totalPaye      = $ovsDone->sum('montant_paye');
        $resteAPayer    = max(0, $prixLogement - $totalAides - $totalPaye);
        $prochaineTranche = $ovsDone->count() + 1;
        $code_loge      = $souscripteur->code_loge_lpl;

        return view('createOvLsp', compact(
            'souscripteur', 'prixLogement', 'aideBnh', 'aideFnpos',
            'totalAides', 'resteAPayer', 'ovsDone', 'prochaineTranche', 'code_loge'
        ));
    }

    // =========================================================================
    // STORE — LPL
    // =========================================================================
    public function store(Request $request)
    {
        $request->validate([
            'souscripteur_id' => 'required|exists:souscripteurs,id',
            'code_loge'       => 'required|exists:logements,code_loge_lpl',
            'montant_total'   => 'required',
            'pourcentage'     => 'required|numeric|min:5|max:50',
            'montant_a_payer' => 'required',
            'montant_restant' => 'required',
            'solde_reste'     => 'required',
        ]);

        $souscripteur = Souscripteur::with('logement.programme')->findOrFail($request->souscripteur_id);

        // ── Restriction par programme selon le rôle ──────────────────────────
        if (!$this->canAccessProgramme($souscripteur->logement->programme->libelle ?? '')) {
            return back()->with('error', $this->accessDeniedMessage());
        }

        if ($this->isFirstOv($souscripteur) && !$this->userCanGenerateFirstOv()) {
            return back()->with('error', 'La génération du premier ordre de versement est réservée au profil DG.');
        }

        if ($request->solde_reste <= 0) {
            return back()->with('error', 'Le souscripteur a déjà payé la totalité du prix.');
        }

        DB::beginTransaction();
        try {
            $baseDeCalcul    = (float) $request->solde_reste;
            $pourcentage     = (int) $request->pourcentage;
            $total           = (float) $request->montant_total;
            $calculTheorique = ($total * $pourcentage) / 100;
            $montantAPayer   = min($calculTheorique, $baseDeCalcul);
            $nouveauReste    = max(0, $baseDeCalcul - $montantAPayer);

            [$qrDataPlain, $qrDataHashed, $qrcodeData] = $this->buildQr('LPL', $souscripteur, $montantAPayer);

            $ov = Ov::create([
                'souscripteur_id'    => $souscripteur->id,
                'montant_total'      => $total,
                'pourcentage'        => $pourcentage,
                'montant_paye'       => $montantAPayer,
                'montant_restant'    => $nouveauReste,
                'numero_tranche'     => $souscripteur->ovs()->count() + 1,
                'qr_content_plain'   => $qrDataPlain,
                'qr_content_hashed'  => $qrDataHashed,
                'qrcode'             => $qrcodeData,
                'user_id'            => Auth::id(),
            ]);

            Logement::where('code_loge_lpl', $request->code_loge)->update(['flag' => 2]);
            DB::commit();

            return redirect()->route('ov.index')
                ->with('pdf_url', route('ov.pdf', Hashids::encode($ov->id)))
                ->with('success', 'Ordre de versement LPL généré avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Erreur : ' . $e->getMessage());
        }
    }

    // =========================================================================
    // STORE — LPA
    // =========================================================================
    public function storeLpa(Request $request)
    {
        $request->validate([
            'souscripteur_id' => 'required|exists:souscripteurs,id',
            'code_loge'       => 'required',
            'numero_tranche'  => 'required|integer|between:1,5',
        ]);

        $souscripteur = Souscripteur::with(['logement.programme', 'ovs', 'aides'])->findOrFail($request->souscripteur_id);

        // ── Restriction par programme selon le rôle ──────────────────────────
        if (!$this->canAccessProgramme($souscripteur->logement->programme->libelle ?? '')) {
            return back()->with('error', $this->accessDeniedMessage());
        }

        // ── Restriction : 1er OV réservé au DG ──────────────────────────────
        if ($this->isFirstOv($souscripteur) && !$this->userCanGenerateFirstOv()) {
            return back()->with('error', 'La génération du premier ordre de versement est réservée au profil DG.');
        }

        $creditBancaire   = $souscripteur->creditBancaire ?? null;
        $ovsDoneNormaux   = $souscripteur->ovs->where('type_ov', null);
        $prochaineTranche = $ovsDoneNormaux->count() + 1;
        $numeroRecu       = (int) $request->numero_tranche;

        if ($numeroRecu !== $prochaineTranche) {
            return back()->with('error', 'Numéro de tranche invalide. Attendu : ' . $prochaineTranche);
        }
        if ($prochaineTranche > 5) {
            return back()->with('error', 'Toutes les tranches ont déjà été générées.');
        }
        if ($creditBancaire !== null && $prochaineTranche > 1) {
            return back()->with('error',
                'Un crédit bancaire est enregistré pour ce dossier. '
                . 'Les tranches T2→T5 ne peuvent pas être générées. '
                . 'Utilisez le bouton "Générer OV différence" si nécessaire.'
            );
        }

        $aideBnh = $souscripteur->aides->firstWhere('type', 'bnh');
        if (!$aideBnh) {
            return back()->with('error', "L'aide BNH doit être enregistrée avant de générer un OV LPA.");
        }

        DB::beginTransaction();
        try {
            $prixLogement   = (float) $souscripteur->logement->prix;
            $aideFnpos      = $souscripteur->aides->firstWhere('type', 'fnpos');
            $montantBnh     = (float) ($aideBnh->montant ?? 0);
            $totalPaye      = (float) $ovsDoneNormaux->sum('montant_paye');
            $prix2          = max(0.0, $prixLogement - $montantBnh);
            $resteGlobal    = max(0.0, $prix2 - $totalPaye);

            if ($resteGlobal <= 0) {
                DB::rollBack();
                return back()->with('error', 'Le montant est entièrement soldé.');
            }

            if ($prochaineTranche === 5) {
                $montantAPayer = $resteGlobal;
                $pourcentage   = round(($montantAPayer / max(1, $prix2)) * 100, 2);
            } else {
                $pourcentage   = self::LPA_TRANCHES[$prochaineTranche];
                $montantAPayer = round($prix2 * $pourcentage / 100, 2);
                if ($prochaineTranche === 4 && $aideFnpos) {
                    $montantAPayer = max(0.0, $montantAPayer - self::FNPOS_MONTANT_FIXE);
                }
            }

            $montantRestant = max(0.0, $resteGlobal - $montantAPayer);
            $vspDejaFait    = $souscripteur->ovs->contains(fn($o) => (bool) $o->vsp);
            $vsp            = (!$vspDejaFait) ? $request->boolean('vsp') : false;

            [$qrDataPlain, $qrDataHashed, $qrcodeData] = $this->buildQr(
                'LPA', $souscripteur, $montantAPayer, $prochaineTranche
            );

            $ovId = DB::table('ordres_versement')->insertGetId([
                'souscripteur_id'    => $souscripteur->id,
                'montant_total'      => $prixLogement,
                'pourcentage'        => $pourcentage,
                'montant_paye'       => $montantAPayer,
                'montant_restant'    => $montantRestant,
                'numero_tranche'     => $prochaineTranche,
                'vsp'                => $vsp ? 1 : 0,
                'type_ov'            => null,
                'qr_content_plain'   => $qrDataPlain,
                'qr_content_hashed'  => $qrDataHashed,
                'qrcode'             => $qrcodeData,
                'user_id'            => Auth::id(),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
            $ov = Ov::find($ovId);

            Logement::where('code_loge_lpl', $souscripteur->code_loge_lpl)->update(['flag' => 2]);
            DB::commit();

            return redirect()->route('ov.index')
                ->with('pdf_url', route('ov.pdf', Hashids::encode($ov->id)))
                ->with('success', "Tranche {$prochaineTranche} LPA générée avec succès.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // =========================================================================
    // STORE — LSP
    // =========================================================================
    public function storeLsp(Request $request)
    {
        $request->validate([
            'souscripteur_id' => 'required|exists:souscripteurs,id',
            'code_loge'       => 'required',
            'montant_a_payer' => 'required|numeric|min:1',
        ]);

        $souscripteur = Souscripteur::with(['logement.programme', 'ovs', 'aides'])->findOrFail($request->souscripteur_id);

        // ── Restriction par programme selon le rôle ──────────────────────────
        if (!$this->canAccessProgramme($souscripteur->logement->programme->libelle ?? '')) {
            return back()->with('error', $this->accessDeniedMessage());
        }

        // ── Restriction : 1er OV réservé au DG ──────────────────────────────
        if ($this->isFirstOv($souscripteur) && !$this->userCanGenerateFirstOv()) {
            return back()->with('error', 'La génération du premier ordre de versement est réservée au profil DG.');
        }

        $prixLogement   = (float) $souscripteur->logement->prix;
        $totalAides     = (float) $souscripteur->aides()->sum('montant');
        $totalPaye      = (float) $souscripteur->ovs()->sum('montant_paye');
        $resteAPayer    = max(0, $prixLogement - $totalAides - $totalPaye);
        $prochaineTranche = $souscripteur->ovs()->count() + 1;

        if ($resteAPayer <= 0) {
            return back()->with('error', 'Le montant est entièrement soldé. Aucun OV à générer.');
        }

        $montantSaisi = (float) $request->montant_a_payer;
        if ($montantSaisi > $resteAPayer) {
            return back()->with('error',
                'Le montant saisi dépasse le reste à payer (' . number_format($resteAPayer, 2, ',', ' ') . ' DA).'
            );
        }

        $nouveauReste = max(0, $resteAPayer - $montantSaisi);

        DB::beginTransaction();
        try {
            [$qrDataPlain, $qrDataHashed, $qrcodeData] = $this->buildQr(
                'LSP', $souscripteur, $montantSaisi, $prochaineTranche
            );

            $ov = Ov::create([
                'souscripteur_id'    => $souscripteur->id,
                'montant_total'      => $prixLogement,
                'pourcentage'        => round(($montantSaisi / max(1, $prixLogement - $totalAides)) * 100, 2),
                'montant_paye'       => $montantSaisi,
                'montant_restant'    => $nouveauReste,
                'numero_tranche'     => $prochaineTranche,
                'vsp'                => false,
                'qr_content_plain'   => $qrDataPlain,
                'qr_content_hashed'  => $qrDataHashed,
                'qrcode'             => $qrcodeData,
                'user_id'            => Auth::id(),
            ]);

            Logement::where('code_loge_lpl', $souscripteur->code_loge_lpl)->update(['flag' => 2]);
            DB::commit();

            return redirect()->route('ov.index')
                ->with('pdf_url', route('ov.pdf', Hashids::encode($ov->id)))
                ->with('success', "Tranche {$prochaineTranche} LSP générée avec succès.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // =========================================================================
    // STORE — AIDE (BNH / FNPOS)
    // =========================================================================
    public function storeAide(Request $request)
    {
        $rules = [
            'souscripteur_id' => 'required|exists:souscripteurs,id',
            'type'            => 'required|in:bnh,fnpos',
            'num_decision'    => 'required|string|max:100',
            'date'            => 'required|date',
            'pieces_jointes'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
        if ($request->type === 'bnh') {
            $rules['montant'] = 'required|numeric|min:1';
        }
        $request->validate($rules);

        $souscripteur = Souscripteur::with('logement.site', 'logement.programme')->findOrFail($request->souscripteur_id);

        // ── Restriction par programme selon le rôle ──────────────────────────
        if (!$this->canAccessProgramme($souscripteur->logement->programme->libelle ?? '')) {
            return back()->with('error', $this->accessDeniedMessage());
        }

        $existe = Aide::where('souscripteur_id', $request->souscripteur_id)
            ->where('type', $request->type)->exists();
        if ($existe) {
            return back()->with('error', 'Une aide ' . strtoupper($request->type) . ' est déjà enregistrée pour ce souscripteur.');
        }

        $montant = ($request->type === 'fnpos')
            ? self::FNPOS_MONTANT_FIXE
            : (float) $request->montant;

        $numConvention = null;
        if ($request->type === 'bnh') {
            $numConvention = $souscripteur->logement->site->num_convention_bnh ?? null;
            if (!$numConvention) {
                return back()->with('error',
                    'Le N° de convention BNH n\'est pas configuré pour ce projet. '
                    . 'Veuillez le renseigner dans les paramètres du site avant d\'enregistrer l\'aide.'
                );
            }
        }

        $filePath = null;
        if ($request->hasFile('pieces_jointes')) {
            $filePath = $request->file('pieces_jointes')->store('aides_pj', 'public');
        }

        Aide::create([
            'souscripteur_id' => $request->souscripteur_id,
            'type'            => $request->type,
            'montant'         => $montant,
            'num_convention'  => $numConvention,
            'num_decision'    => $request->num_decision,
            'date'            => $request->date,
            'pieces_jointes'  => $filePath,
            'user_id'         => Auth::id(),
        ]);

        return back()->with('success', 'Aide ' . strtoupper($request->type) . ' enregistrée avec succès.');
    }

    // =========================================================================
    // STORE — CRÉDIT BANCAIRE
    // =========================================================================
    public function storeCreditBancaire(Request $request)
    {
        $request->validate([
            'souscripteur_id'      => 'required|exists:souscripteurs,id',
            'montant_attestation'  => 'required|numeric|min:1',
            'montant_reel'         => 'required|numeric|min:1',
            'date_attestation'     => 'required|date',
            'date_versement_reel'  => 'nullable|date',
            'pieces_jointes'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $souscripteur = Souscripteur::with(['ovs', 'aides', 'creditBancaire', 'logement.site', 'logement.programme'])
            ->findOrFail($request->souscripteur_id);

        // ── Restriction par programme selon le rôle ──────────────────────────
        if (!$this->canAccessProgramme($souscripteur->logement->programme->libelle ?? '')) {
            return back()->with('error', $this->accessDeniedMessage());
        }

        $aideBnh = $souscripteur->aides->firstWhere('type', 'bnh');
        if (!$aideBnh) {
            return back()->with('error', "L'aide BNH doit être enregistrée avant d'enregistrer un crédit bancaire.");
        }

        $aideFnpos     = $souscripteur->aides->firstWhere('type', 'fnpos');
        $montantFnpos  = $aideFnpos ? self::FNPOS_MONTANT_FIXE : 0.0;

        if ($souscripteur->creditBancaire) {
            return back()->with('error', 'Un crédit bancaire est déjà enregistré pour ce souscripteur.');
        }

        $ovT1 = $souscripteur->ovs->where('type_ov', null)->where('numero_tranche', 1)->first();
        if (!$ovT1) {
            return back()->with('error', 'La Tranche 1 doit être générée avant d\'enregistrer un crédit bancaire.');
        }
        if (!$ovT1->paiement) {
            return back()->with('error', '⚠️ La Tranche 1 doit être PAYÉE avant d\'enregistrer un crédit bancaire.');
        }

        $ovT2Normal = $souscripteur->ovs->where('type_ov', null)->where('numero_tranche', 2)->first();
        if ($ovT2Normal) {
            return back()->with('error',
                'Impossible d\'enregistrer un crédit bancaire : la Tranche 2 normale a déjà été générée. '
                . 'Le crédit bancaire n\'est autorisé qu\'après la Tranche 1 uniquement.'
            );
        }

        $prixLogement    = (float) $souscripteur->logement->prix;
        $montantBnh      = (float) $aideBnh->montant;
        $montantT1       = (float) $ovT1->montant_paye;
        $montantAttendu  = $prixLogement - $montantBnh - $montantT1 - $montantFnpos;

        if ($montantAttendu <= 0) {
            return back()->with('error',
                'Le montant restant à payer est nul ou négatif. Aucun crédit bancaire nécessaire. '
                . 'Calcul : (' . number_format($prixLogement, 2, ',', ' ')
                . ' − ' . number_format($montantBnh, 2, ',', ' ')
                . ') − ' . number_format($montantT1, 2, ',', ' ')
                . ($montantFnpos > 0 ? ' − ' . number_format($montantFnpos, 2, ',', ' ') : '')
                . ' = ' . number_format($montantAttendu, 2, ',', ' ') . ' DA.'
            );
        }

        $montantAttestation = (float) $request->montant_attestation;
        $montantReel        = (float) $request->montant_reel;
        $tolerance          = 0.01;

        if (abs($montantAttestation - $montantAttendu) > $tolerance) {
            return back()->with('error',
                '❌ Le montant de l\'attestation (' . number_format($montantAttestation, 2, ',', ' ') . ' DA) '
                . 'ne correspond pas au montant attendu (' . number_format($montantAttendu, 2, ',', ' ') . ' DA). '
                . '<br><br><strong>Formule de calcul :</strong><br>'
                . '(Prix − BNH) − T1' . ($montantFnpos > 0 ? ' − FNPOS' : '') . '<br>'
                . '= (' . number_format($prixLogement, 2, ',', ' ')
                . ' − ' . number_format($montantBnh, 2, ',', ' ')
                . ') − ' . number_format($montantT1, 2, ',', ' ')
                . ($montantFnpos > 0 ? ' − ' . number_format($montantFnpos, 2, ',', ' ') : '')
                . '<br>= <strong>' . number_format($montantAttendu, 2, ',', ' ') . ' DA</strong>'
            )->withInput();
        }

        if ($montantReel > $montantAttestation) {
            return back()->with('error',
                '❌ Le montant réel (' . number_format($montantReel, 2, ',', ' ') . ' DA) '
                . 'ne peut pas dépasser le montant de l\'attestation ('
                . number_format($montantAttestation, 2, ',', ' ') . ' DA).'
            )->withInput();
        }

        $difference = max(0.0, $montantAttestation - $montantReel);

        $filePath = null;
        if ($request->hasFile('pieces_jointes')) {
            $filePath = $request->file('pieces_jointes')->store('credits_pj', 'public');
        }

        DB::beginTransaction();
        try {
            $credit = CreditBancaire::create([
                'souscripteur_id'     => $souscripteur->id,
                'montant_attestation' => $montantAttestation,
                'montant_reel'        => $montantReel,
                'date_attestation'    => $request->date_attestation,
                'date_versement_reel' => $request->date_versement_reel,
                'pieces_jointes'      => $filePath,
                'user_id'             => Auth::id(),
            ]);

            [$qrPlainT2, $qrHashedT2, $qrDataT2] = $this->buildQr('LPA-CREDIT', $souscripteur, $montantReel, 2);

            $ovT2 = Ov::create([
                'souscripteur_id'    => $souscripteur->id,
                'montant_total'      => $prixLogement,
                'pourcentage'        => round(($montantReel / max(1, $prixLogement)) * 100, 2),
                'montant_paye'       => $montantReel,
                'montant_restant'    => $difference,
                'numero_tranche'     => 2,
                'vsp'                => false,
                'type_ov'            => 'credit_reel',
                'qr_content_plain'   => $qrPlainT2,
                'qr_content_hashed'  => $qrHashedT2,
                'qrcode'             => $qrDataT2,
                'user_id'            => Auth::id(),
            ]);

            Paiement::create([
                'ov_id'          => $ovT2->id,
                'num_recu'       => 'CREDIT-AUTO-' . $souscripteur->id . '-' . time(),
                'nom_agence'     => $souscripteur->logement->site->nom_agence ?? 'Banque',
                'num_agence'     => $souscripteur->logement->site->num_agence ?? '—',
                'date_paiement'  => $request->date_versement_reel ?? $request->date_attestation,
                'recu_pdf'       => null,
                'user_id'        => Auth::id(),
            ]);

            $ovT3 = null;
            if ($difference > 0) {
                [$qrPlainT3, $qrHashedT3, $qrDataT3] = $this->buildQr('LPA-DIFF', $souscripteur, $difference, 3);
                $ovT3 = Ov::create([
                    'souscripteur_id'    => $souscripteur->id,
                    'montant_total'      => $prixLogement,
                    'pourcentage'        => round(($difference / max(1, $prixLogement)) * 100, 2),
                    'montant_paye'       => $difference,
                    'montant_restant'    => 0,
                    'numero_tranche'     => 3,
                    'vsp'                => false,
                    'type_ov'            => 'credit_diff',
                    'qr_content_plain'   => $qrPlainT3,
                    'qr_content_hashed'  => $qrHashedT3,
                    'qrcode'             => $qrDataT3,
                    'user_id'            => Auth::id(),
                ]);
            }

            Logement::where('code_loge_lpl', $souscripteur->code_loge_lpl)->update(['flag' => 2]);
            DB::commit();

            $pdfUrl      = route('ov.pdf', Hashids::encode($ovT2->id));
            $recapCalcul = '<br><br><strong>📊 Récapitulatif :</strong><br>'
                . '• Prix logement : ' . number_format($prixLogement, 2, ',', ' ') . ' DA<br>'
                . '• Aide BNH : −' . number_format($montantBnh, 2, ',', ' ') . ' DA<br>'
                . '• T1 payée : −' . number_format($montantT1, 2, ',', ' ') . ' DA<br>'
                . ($montantFnpos > 0 ? '• Aide FNPOS : −' . number_format($montantFnpos, 2, ',', ' ') . ' DA<br>' : '')
                . '• <strong>Reste (crédit) : ' . number_format($montantAttendu, 2, ',', ' ') . ' DA</strong><br>'
                . '• T2 (crédit réel) : ' . number_format($montantReel, 2, ',', ' ') . ' DA ✅ PAYÉE';

            if ($difference > 0) {
                $recapCalcul .= '<br>• T3 (différence) : ' . number_format($difference, 2, ',', ' ') . ' DA ⏳ EN ATTENTE';
                return redirect()->route('ov.index')
                    ->with('pdf_url', $pdfUrl)
                    ->with('warning',
                        '✅ Crédit bancaire enregistré avec succès !'
                        . $recapCalcul
                        . '<br><br>⚠️ <strong>Une T3 complémentaire a été générée</strong> pour la différence. '
                        . 'Le dossier sera soldé après paiement de cette T3.'
                    );
            }

            $recapCalcul .= '<br><br>✅ <strong>Dossier entièrement soldé !</strong>';
            return redirect()->route('ov.index')
                ->with('pdf_url', $pdfUrl)
                ->with('success', '✅ Crédit bancaire enregistré avec succès !' . $recapCalcul);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // =========================================================================
    // STORE — OV COMPLÉMENTAIRE
    // =========================================================================
    public function storeOvCredit(Request $request)
    {
        return redirect()->route('ov.index')
            ->with('error', 'L\'OV complémentaire est généré automatiquement lors de l\'enregistrement du crédit bancaire.');
    }

    // =========================================================================
    // PDF — DISPATCH
    // =========================================================================
    public function generatePDF($id)
    {
        $ov = Ov::with([
            'souscripteur.logement.site',
            'souscripteur.logement.programme',
            'souscripteur.aides',
            'paiement',
        ])->findOrFail($id);

        $programme = strtoupper(trim($ov->souscripteur->logement->programme->libelle ?? 'LPL'));

        return match ($programme) {
            'LPA' => $this->pdfLpa($ov),
            'LSP' => $this->pdfLsp($ov),
            default => $this->pdfLpl($ov),
        };
    }

    // =========================================================================
    // PAIEMENT
    // =========================================================================
    public function createPaiement($ovId)
    {
        $ov = Ov::with('souscripteur.logement.site', 'souscripteur.logement.programme')->findOrFail($ovId);

        // ── Restriction par programme selon le rôle ──────────────────────────
        if (!$this->canAccessProgramme($ov->souscripteur->logement->programme->libelle ?? '')) {
            return redirect()->route('ov.index')->with('error', $this->accessDeniedMessage());
        }

        $nomAgence = $ov->souscripteur->logement->site->nom_agence ?? '';
        $numAgence = $ov->souscripteur->logement->site->num_agence ?? '';

        return view('paiementCreate', compact('ov', 'nomAgence', 'numAgence'));
    }

    public function storePaiement(Request $request)
    {
        $request->validate([
            'ov_id'         => 'required|exists:ordres_versement,id',
            'num_recu'      => 'required|string|unique:paiements,num_recu',
            'date_paiement' => 'required|date',
            'pj.*'          => 'required|file|mimes:pdf',
        ]);

        $ov = Ov::with('souscripteur.logement.site', 'souscripteur.logement.programme')->findOrFail($request->ov_id);

        // ── Restriction par programme selon le rôle ──────────────────────────
        if (!$this->canAccessProgramme($ov->souscripteur->logement->programme->libelle ?? '')) {
            return back()->with('error', $this->accessDeniedMessage());
        }

        $nomAgence = $ov->souscripteur->logement->site->nom_agence ?? '—';
        $numAgence = $ov->souscripteur->logement->site->num_agence ?? '—';

        $filePath = null;
        if ($request->hasFile('pj')) {
            $file     = $request->file('pj')[0];
            $fileName = 'recu_' . $ov->id . '_' . time() . '.pdf';
            $filePath = $file->storeAs('recus_paiements', $fileName, 'public');
        }

        try {
            DB::beginTransaction();
            Paiement::create([
                'ov_id'         => $request->ov_id,
                'num_recu'      => $request->num_recu,
                'nom_agence'    => $nomAgence,
                'num_agence'    => $numAgence,
                'date_paiement' => $request->date_paiement,
                'recu_pdf'      => $filePath,
                'user_id'       => Auth::id(),
            ]);
            DB::commit();
            return redirect()->route('ov.index')->with('success', 'Paiement enregistré avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur SQL : ' . $e->getMessage())->withInput();
        }
    }

    // =========================================================================
    // EDIT — routage par programme + vérification du rôle
    // =========================================================================
    public function edit($id)
    {
        if (Auth::user()->role !== 'chef_service_com') {
            return redirect()->route('ov.index')
                ->with('error', 'Accès refusé. Cette action est réservée au Chef de service.');
        }

        $ov = Ov::with([
            'souscripteur.logement.programme',
            'souscripteur.logement.site',
            'souscripteur.ovs.paiement',
            'souscripteur.aides',
            'souscripteur.creditBancaire',
            'paiement',
        ])->findOrFail($id);

        if ($ov->paiement) {
            return redirect()->route('ov.index')
                ->with('error', 'Impossible de modifier un OV déjà payé.');
        }

        $programme = $this->getProgrammeType($ov->souscripteur);

        return match ($programme) {
            'LPA' => $this->editLpa($ov),
            'LSP' => $this->editLsp($ov),
            default => $this->editLpl($ov),
        };
    }

    // ── EDIT LPL ─────────────────────────────────────────────────────────────
    private function editLpl(Ov $ov)
    {
        $souscripteur    = $ov->souscripteur;
        $prixLogement    = (float) ($souscripteur->logement->prix ?? 0);
        $totalPayeAutres = (float) $souscripteur->ovs->where('id', '!=', $ov->id)->sum('montant_paye');
        $reste           = max(0, $prixLogement - $totalPayeAutres);
        $code_loge       = $souscripteur->code_loge_lpl;

        return response()->view('editOvLpl', compact('ov', 'souscripteur', 'prixLogement', 'reste', 'code_loge'));
    }

    // ── EDIT LSP ─────────────────────────────────────────────────────────────
    private function editLsp(Ov $ov)
    {
        $souscripteur    = $ov->souscripteur;
        $prixLogement    = (float) ($souscripteur->logement->prix ?? 0);
        $aideBnh         = $souscripteur->aides->firstWhere('type', 'bnh');
        $aideFnpos       = $souscripteur->aides->firstWhere('type', 'fnpos');
        $totalAides      = (float)($aideBnh->montant ?? 0) + (float)($aideFnpos ? self::FNPOS_MONTANT_FIXE : 0);
        $totalPayeAutres = (float) $souscripteur->ovs->where('id', '!=', $ov->id)->sum('montant_paye');
        $resteAPayer     = max(0, $prixLogement - $totalAides - $totalPayeAutres);
        $code_loge       = $souscripteur->code_loge_lpl;

        return response()->view('editOvLsp', compact(
            'ov', 'souscripteur', 'prixLogement',
            'aideBnh', 'aideFnpos', 'totalAides', 'resteAPayer', 'code_loge'
        ));
    }

    // ── EDIT LPA ─────────────────────────────────────────────────────────────
    private function editLpa(Ov $ov)
    {
        $souscripteur    = $ov->souscripteur;
        $prixLogement    = (float) ($souscripteur->logement->prix ?? 0);
        $aideBnh         = $souscripteur->aides->firstWhere('type', 'bnh');
        $aideFnpos       = $souscripteur->aides->firstWhere('type', 'fnpos');
        $creditBancaire  = $souscripteur->creditBancaire ?? null;
        $code_loge       = $souscripteur->code_loge_lpl;
        $site            = $souscripteur->logement->site;
        $montantBnh      = (float) ($aideBnh->montant ?? 0);
        $fnposMontant    = self::FNPOS_MONTANT_FIXE;
        $prix2           = max(0.0, $prixLogement - $montantBnh);

        $ovsDoneNormaux  = $souscripteur->ovs->where('type_ov', null)->where('id', '!=', $ov->id);
        $totalPayeAutres = (float) $ovsDoneNormaux->sum('montant_paye');
        $baseCalcul      = max(0.0, $prix2 - $totalPayeAutres);
        $tranche         = $ov->numero_tranche;
        $tranches        = self::LPA_TRANCHES;

        if ($tranche === 5) {
            $pourcentage    = round(($baseCalcul / max(1, $prix2)) * 100, 2);
            $montantTranche = $baseCalcul;
        } else {
            $pourcentage    = $tranches[$tranche] ?? 0;
            $montantTranche = round($prix2 * $pourcentage / 100, 2);
            if ($tranche === 4 && $aideFnpos) {
                $montantTranche = max(0.0, $montantTranche - $fnposMontant);
            }
        }

        $montantRestant = max(0.0, $baseCalcul - $montantTranche);

        return response()->view('editOvLpa', compact(
            'ov', 'souscripteur', 'prixLogement',
            'aideBnh', 'aideFnpos', 'creditBancaire',
            'prix2', 'baseCalcul', 'tranche', 'tranches',
            'pourcentage', 'montantTranche', 'montantRestant',
            'montantBnh', 'fnposMontant', 'code_loge', 'site'
        ));
    }

    // =========================================================================
    // UPDATE — dispatch par programme
    // =========================================================================
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'chef_service_com') {
            return redirect()->route('ov.index')
                ->with('error', 'Accès refusé. Cette action est réservée au Chef de service.');
        }

        $ov = Ov::with(['souscripteur.logement.programme', 'souscripteur.ovs', 'souscripteur.aides', 'paiement'])
            ->findOrFail($id);

        if ($ov->paiement) {
            return redirect()->route('ov.index')
                ->with('error', 'Impossible de modifier un OV déjà payé.');
        }

        $programme = $this->getProgrammeType($ov->souscripteur);

        return match ($programme) {
            'LPA' => $this->updateLpa($request, $ov),
            'LSP' => $this->updateLsp($request, $ov),
            default => $this->updateLpl($request, $ov),
        };
    }

    // ── UPDATE LPL ────────────────────────────────────────────────────────────
    private function updateLpl(Request $request, Ov $ov)
    {
        $request->validate(['pourcentage' => 'required|numeric|min:5|max:50', 'montant_paye' => 'required|numeric|min:1']);

        $souscripteur    = $ov->souscripteur;
        $prixLogement    = (float) ($souscripteur->logement->prix ?? 0);
        $totalPayeAutres = (float) $souscripteur->ovs->where('id', '!=', $ov->id)->sum('montant_paye');
        $reste           = max(0, $prixLogement - $totalPayeAutres);
        $pourcentage     = (float) $request->pourcentage;
        $montantSaisi    = (float) $request->montant_paye;
        $montantTheorique = round($prixLogement * $pourcentage / 100, 2);
        $montantAPayer    = min($montantTheorique, $reste, $montantSaisi);
        $montantRestant   = max(0, $reste - $montantAPayer);

        if ($montantAPayer <= 0) {
            return back()->with('error', 'Le montant calculé est nul ou invalide.');
        }

        [$qrPlain, $qrHashed, $qrData] = $this->buildQr('LPL', $souscripteur, $montantAPayer, $ov->numero_tranche);
        $ov->update([
            'pourcentage'        => $pourcentage,
            'montant_paye'       => $montantAPayer,
            'montant_restant'    => $montantRestant,
            'qr_content_plain'   => $qrPlain,
            'qr_content_hashed'  => $qrHashed,
            'qrcode'             => $qrData,
        ]);

        return redirect()->route('ov.index')->with('success', 'Ordre de versement LPL modifié avec succès.');
    }

    // ── UPDATE LSP ────────────────────────────────────────────────────────────
    private function updateLsp(Request $request, Ov $ov)
    {
        $request->validate(['montant_a_payer' => 'required|numeric|min:1']);

        $souscripteur    = $ov->souscripteur;
        $prixLogement    = (float) ($souscripteur->logement->prix ?? 0);
        $totalAides      = (float) $souscripteur->aides()->sum('montant');
        $totalPayeAutres = (float) $souscripteur->ovs->where('id', '!=', $ov->id)->sum('montant_paye');
        $resteAPayer     = max(0, $prixLogement - $totalAides - $totalPayeAutres);
        $montantSaisi    = (float) $request->montant_a_payer;

        if ($montantSaisi > $resteAPayer) {
            return back()->with('error',
                'Le montant saisi (' . number_format($montantSaisi, 2, ',', ' ') . ' DA) '
                . 'dépasse le reste à payer (' . number_format($resteAPayer, 2, ',', ' ') . ' DA).'
            );
        }

        $nouveauReste = max(0, $resteAPayer - $montantSaisi);
        $pourcentage  = round(($montantSaisi / max(1, $prixLogement - $totalAides)) * 100, 2);

        [$qrPlain, $qrHashed, $qrData] = $this->buildQr('LSP', $souscripteur, $montantSaisi, $ov->numero_tranche);
        $ov->update([
            'pourcentage'        => $pourcentage,
            'montant_paye'       => $montantSaisi,
            'montant_restant'    => $nouveauReste,
            'qr_content_plain'   => $qrPlain,
            'qr_content_hashed'  => $qrHashed,
            'qrcode'             => $qrData,
        ]);

        return redirect()->route('ov.index')->with('success', 'Ordre de versement LSP modifié avec succès.');
    }

    // ── UPDATE LPA ────────────────────────────────────────────────────────────
    private function updateLpa(Request $request, Ov $ov)
    {
        $souscripteur = $ov->souscripteur;
        if ($ov->type_ov !== null) {
            return redirect()->route('ov.index')
                ->with('error', 'Les OVs de type crédit bancaire ne peuvent pas être modifiés manuellement.');
        }

        $request->validate(['vsp' => 'nullable|boolean']);

        $prixLogement    = (float) ($souscripteur->logement->prix ?? 0);
        $aideBnh         = $souscripteur->aides->firstWhere('type', 'bnh');
        $aideFnpos       = $souscripteur->aides->firstWhere('type', 'fnpos');
        $montantBnh      = (float) ($aideBnh->montant ?? 0);
        $fnposMontant    = self::FNPOS_MONTANT_FIXE;
        $prix2           = max(0.0, $prixLogement - $montantBnh);
        $tranche         = $ov->numero_tranche;

        $totalPayeAutres = (float) $souscripteur->ovs->where('type_ov', null)->where('id', '!=', $ov->id)->sum('montant_paye');
        $baseCalcul      = max(0.0, $prix2 - $totalPayeAutres);

        if ($tranche === 5) {
            $montantAPayer = $baseCalcul;
            $pourcentage   = round(($montantAPayer / max(1, $prix2)) * 100, 2);
        } else {
            $pourcentage   = self::LPA_TRANCHES[$tranche] ?? 0;
            $montantAPayer = round($prix2 * $pourcentage / 100, 2);
            if ($tranche === 4 && $aideFnpos) {
                $montantAPayer = max(0.0, $montantAPayer - $fnposMontant);
            }
        }

        $montantRestant = max(0.0, $baseCalcul - $montantAPayer);
        $vspDejaFait    = $souscripteur->ovs->where('id', '!=', $ov->id)->contains(fn($o) => (bool) $o->vsp);
        $vsp            = (!$vspDejaFait) ? $request->boolean('vsp') : $ov->vsp;

        [$qrPlain, $qrHashed, $qrData] = $this->buildQr('LPA', $souscripteur, $montantAPayer, $tranche);
        $ov->update([
            'montant_total'      => $prixLogement,
            'pourcentage'        => $pourcentage,
            'montant_paye'       => $montantAPayer,
            'montant_restant'    => $montantRestant,
            'vsp'                => $vsp,
            'qr_content_plain'   => $qrPlain,
            'qr_content_hashed'  => $qrHashed,
            'qrcode'             => $qrData,
        ]);

        return redirect()->route('ov.index')
            ->with('success', "Tranche {$tranche} LPA recalculée et mise à jour avec succès.");
    }

    // =========================================================================
    // HELPERS PRIVÉS
    // =========================================================================
    private function getProgrammeType(Souscripteur $souscripteur): string
    {
        $prog = $souscripteur->logement->programme->libelle ?? 'LPL';
        return strtoupper(trim($prog));
    }

    private function buildQr(string $programmeCode, Souscripteur $souscripteur, float $montant, ?int $tranche = null): array
    {
        $plain = sprintf(
            'OPGI Dar El Beida %s | Nom: %s | Prénom: %s | Code: %s%s | Montant: %.2f',
            $programmeCode,
            strtoupper($souscripteur->nom),
            $souscripteur->prenom,
            $souscripteur->code_loge_lpl,
            $tranche ? " | Tranche: {$tranche}" : '',
            $montant
        );
        $hashed = hash('sha256', $plain);
        $svg    = QrCode::size(200)->margin(1)->generate($hashed);
        return [$plain, $hashed, base64_encode($svg)];
    }

    private function isFirstOv(Souscripteur $souscripteur): bool
    {
        return $souscripteur->ovs()->count() === 0;
    }

    private function userCanGenerateFirstOv(): bool
    {
        return Auth::user()?->role === 'dg';
    }

    // =========================================================================
    // PDF — LPA
    // =========================================================================
    private function pdfLpa(Ov $ov): \Illuminate\Http\Response
    {
        $Arabic  = new Arabic();
        $tranche = $this->trancheInfo($ov->numero_tranche);
        $site    = $ov->souscripteur->logement->site;

        $logoRepPath  = public_path('images/1203.webp');
        $logoOpgiPath = public_path('images/last.png');
        $logoRepB64   = file_exists($logoRepPath)  ? 'data:image/png;base64,'  . base64_encode(file_get_contents($logoRepPath))  : '';
        $logoOpgiB64  = file_exists($logoOpgiPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoOpgiPath)) : '';

        $ordinals = [1=>'1ÈRE',2=>'2ÈME',3=>'3ÈME',4=>'4ÈME',5=>'5ÈME'];
        $num      = $ov->numero_tranche ?? 1;
        $ordinal  = $ordinals[$num] ?? ($num . 'ÈME');
        switch ($ov->type_ov ?? null) {
            case 'credit_reel': $tLabel = $ordinal . ' TRANCHE — CRÉDIT RÉEL';       break;
            case 'credit_diff': $tLabel = $ordinal . ' TRANCHE — DIFFÉRENCE CRÉDIT'; break;
            default:            $tLabel = $ordinal . ' TRANCHE';
        }

        $data = [
            'ov'               => $ov,
            'tLabel'           => $tLabel,
            'trancheLabel'     => $tranche['upper'],
            'trancheLabelFr'   => $tranche['lower'],
            'typeProgramme'    => strtoupper(trim($ov->souscripteur->logement->programme->libelle ?? 'LPA')),
            'datePdf'          => now()->format('d/m/Y'),
            'userPrinted'      => Auth::user()->name ?? 'USER',
            'montantEnLettres' => $this->montantEnLettres($ov->montant_paye),
            'logoRepB64'       => $logoRepB64,
            'logoOpgiB64'      => $logoOpgiB64,
            'republique'       => $Arabic->utf8Glyphs('الجمهورية الجزائرية الديمقراطية الشعبية'),
            'ministere_ar'     => $Arabic->utf8Glyphs('وزارة السكن والعمران والمدينة والتهيئة العمرانية'),
            'wilaya_ar'        => $Arabic->utf8Glyphs('ولاية الجزائر'),
            'opgi_nom_ar'      => $Arabic->utf8Glyphs('ديوان الترقية والتسيير العقاري'),
            'dar_beida_ar'     => $Arabic->utf8Glyphs('الدار البيضاء'),
        ];

       return Pdf::loadView('ordre_versement_lpa_pdf', $data)
            ->setPaper([0, 0, 419.53, 245], 'portrait') // Hauteur réduite de 280 à 245
            ->stream('OV_LPA_' . $ov->id . '.pdf');
    }

    // =========================================================================
    // PDF — LPL
    // =========================================================================
    private function pdfLpl(Ov $ov): \Illuminate\Http\Response
    {
        $Arabic   = new Arabic();
        $tranche  = $this->trancheInfo($ov->numero_tranche);
        $site     = $ov->souscripteur->logement->site;
        $logement = $ov->souscripteur->logement;

        $logoRepPath  = public_path('images/1203.webp');
        $logoOpgiPath = public_path('images/last.png');
        $logoOpgiB64  = file_exists($logoOpgiPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoOpgiPath)) : '';
        $logoRepB64   = file_exists($logoRepPath)  ? 'data:image/webp;base64,'  . base64_encode(file_get_contents($logoRepPath))  : '';

        $ordinals = [1=>'1ÈRE',2=>'2ÈME',3=>'3ÈME',4=>'4ÈME',5=>'5ÈME'];
        $num      = $ov->numero_tranche ?? 1;
        $ordinal  = $ordinals[$num] ?? ($num . 'ÈME');
        $tLabel   = $ordinal . ' TRANCHE';

        $data = [
            'ov'                 => $ov,
            'tLabel'             => $tLabel,
            'trancheShort'       => $tranche['short'],
            'trancheLabelFr'     => $tranche['lower'],
            'datePdf'            => now()->format('d/m/Y'),
            'annee'              => now()->year,
            'userPrinted'        => Auth::user()->name ?? 'USER',
            'montantEnLettres'   => $this->montantEnLettres($ov->montant_paye),
            'montantEnLettresAr' => $this->montantEnLettresArabe($ov->montant_paye),
            'logoOpgiB64'        => $logoOpgiB64,
            'logoRepB64'         => $logoRepB64,
            'siteLibelle'        => $site->libelle ?? ($logement->programme->libelle ?? '—'),
            'ribLpl'             => $site->num_compte_agence ?? '—',
            'banqueNom'          => $site->nom_agence ?? '—',
            'boxNum'             => $logement->box_num ?? null,
            'boxSuperficie'      => $logement->box_superficie ?? null,
            'boxPrix'            => $logement->box_prix ?? null,
            'republique'         => $Arabic->utf8Glyphs('الجمهورية الجزائرية الديمقراطية الشعبية'),
            'ministere_ar'       => $Arabic->utf8Glyphs('وزارة السكن والعمران والمدينة والتهيئة العمرانية'),
            'wilaya_ar'          => $Arabic->utf8Glyphs('ولاية الجزائر'),
            'opgi_nom_ar'        => $Arabic->utf8Glyphs('ديوان الترقية والتسيير العقاري'),
            'dar_beida_ar'       => $Arabic->utf8Glyphs('الدار البيضاء'),
        ];

    return Pdf::loadView('ordre_versement_lpl_pdf', $data)
            ->setPaper([0, 0, 419.53, 245], 'portrait') // Hauteur réduite de 280 à 245
            ->stream('OV_LPL_' . $ov->id . '.pdf');
    }

    // =========================================================================
    // PDF — LSP
    // =========================================================================
    private function pdfLsp(Ov $ov): \Illuminate\Http\Response
    {
        $Arabic   = new Arabic();
        $tranche  = $this->trancheInfo($ov->numero_tranche);
        $site     = $ov->souscripteur->logement->site;
        $logement = $ov->souscripteur->logement;

        $logoRepPath  = public_path('images/1203.webp');
        $logoOpgiPath = public_path('images/last.png');
        $logoRepB64   = file_exists($logoRepPath)  ? 'data:image/webp;base64,' . base64_encode(file_get_contents($logoRepPath))  : '';
        $logoOpgiB64  = file_exists($logoOpgiPath) ? 'data:image/png;base64,'  . base64_encode(file_get_contents($logoOpgiPath)) : '';

        $ordinals = [1=>'1ÈRE',2=>'2ÈME',3=>'3ÈME',4=>'4ÈME',5=>'5ÈME'];
        $num      = $ov->numero_tranche ?? 1;
        $ordinal  = $ordinals[$num] ?? ($num . 'ÈME');
        $tLabel   = $ordinal . ' TRANCHE';

        $aideBnh    = $ov->souscripteur->aides->firstWhere('type', 'bnh');
        $aideFnpos  = $ov->souscripteur->aides->firstWhere('type', 'fnpos');
        $totalAides = (float)($aideBnh->montant ?? 0) + (float)($aideFnpos ? self::FNPOS_MONTANT_FIXE : 0);

        $data = [
            'ov'                 => $ov,
            'tLabel'             => $tLabel,
            'trancheLabel'       => $tranche['upper'],
            'trancheLabelFr'     => $tranche['lower'],
            'trancheShort'       => $tranche['short'],
            'typeProgramme'      => 'LSP',
            'aideBnh'            => $aideBnh,
            'aideFnpos'          => $aideFnpos,
            'totalAides'         => $totalAides,
            'datePdf'            => now()->format('d/m/Y'),
            'annee'              => now()->year,
            'userPrinted'        => Auth::user()->name ?? 'USER',
            'montantEnLettres'   => $this->montantEnLettres($ov->montant_paye),
            'montantEnLettresAr' => $this->montantEnLettresArabe($ov->montant_paye),
            'logoRepB64'         => $logoRepB64,
            'logoOpgiB64'        => $logoOpgiB64,
            'siteLibelle'        => $site->libelle ?? ($logement->programme->libelle ?? '—'),
            'ribLsp'             => $site->num_compte_agence ?? '—',
            'banqueNom'          => $site->nom_agence ?? '—',
            'republique'         => $Arabic->utf8Glyphs('الجمهورية الجزائرية الديمقراطية الشعبية'),
            'ministere_ar'       => $Arabic->utf8Glyphs('وزارة السكن والعمران والمدينة والتهيئة العمرانية'),
            'wilaya_ar'          => $Arabic->utf8Glyphs('ولاية الجزائر'),
            'opgi_nom_ar'        => $Arabic->utf8Glyphs('ديوان الترقية والتسيير العقاري'),
            'dar_beida_ar'       => $Arabic->utf8Glyphs('الدار البيضاء'),
        ];

       return Pdf::loadView('ordre_versement_lsp_pdf', $data)
            ->setPaper([0, 0, 419.53, 245], 'portrait') // Hauteur réduite de 280 à 245
            ->stream('OV_LSP_' . $ov->id . '.pdf');
    }

    // =========================================================================
    // HELPER — Libellés de tranche
    // =========================================================================
    private function trancheInfo(?int $n): array
    {
        $map = [
            1 => ['upper' => 'PREMIERE TRANCHE',  'lower' => 'première',  'short' => '1ère'],
            2 => ['upper' => 'DEUXIEME TRANCHE',   'lower' => 'deuxième',  'short' => '2ème'],
            3 => ['upper' => 'TROISIEME TRANCHE',  'lower' => 'troisième', 'short' => '3ème'],
            4 => ['upper' => 'QUATRIEME TRANCHE',  'lower' => 'quatrième', 'short' => '4ème'],
            5 => ['upper' => 'CINQUIEME TRANCHE',  'lower' => 'cinquième', 'short' => '5ème'],
        ];
        return $map[$n ?? 1] ?? ['upper' => "TRANCHE {$n}", 'lower' => "tranche {$n}", 'short' => "{$n}ème"];
    }

    private function montantEnLettres($montant)
    {
        $montant = floor($montant);
        if ($montant == 0) return "zéro dinar algérien";
        $unites   = ["","un","deux","trois","quatre","cinq","six","sept","huit","neuf","dix","onze","douze","treize","quatorze","quinze","seize","dix-sept","dix-huit","dix-neuf"];
        $dizaines = ["","","vingt","trente","quarante","cinquante","soixante","soixante","quatre-vingt","quatre-vingt"];
        return ucfirst($this->convertirNombreEnLettres($montant, $unites, $dizaines) . " Dinars Algériens");
    }

    private function convertirNombreEnLettres($nombre, $unites, $dizaines)
    {
        if ($nombre < 20) return $unites[$nombre];
        if ($nombre < 100) {
            $d = floor($nombre / 10); $u = $nombre % 10; $r = $dizaines[$d];
            if ($d == 7 || $d == 9)               $r = $dizaines[$d] . "-" . $unites[10 + $u];
            elseif ($d == 8 && $u == 0)           $r .= "s";
            elseif ($u == 1 && $d > 1 && $d != 8) $r .= " et un";
            elseif ($u > 0)                        $r .= "-" . $unites[$u];
            return $r;
        }
        if ($nombre < 1000) {
            $c = floor($nombre / 100); $r = $nombre % 100;
            $res = $c == 1 ? "cent" : $unites[$c] . " cent";
            if ($r == 0 && $c > 1) $res .= "s";
            if ($r > 0) $res .= " " . $this->convertirNombreEnLettres($r, $unites, $dizaines);
            return $res;
        }
        if ($nombre < 1000000) {
            $m = floor($nombre / 1000); $r = $nombre % 1000;
            $res = $m == 1 ? "mille" : $this->convertirNombreEnLettres($m, $unites, $dizaines) . " mille";
            if ($r > 0) $res .= " " . $this->convertirNombreEnLettres($r, $unites, $dizaines);
            return $res;
        }
        if ($nombre < 1000000000) {
            $m = floor($nombre / 1000000); $r = $nombre % 1000000;
            $res = $m == 1 ? "un million" : $this->convertirNombreEnLettres($m, $unites, $dizaines) . " millions";
            if ($r > 0) $res .= " " . $this->convertirNombreEnLettres($r, $unites, $dizaines);
            return $res;
        }
        return "Montant trop élevé";
    }

    private function montantEnLettresArabe($montant)
    {
        $montant = floor($montant);
        if ($montant == 0) return "صفر دينار جزائري";
        $unites   = ["","واحد","اثنان","ثلاثة","أربعة","خمسة","ستة","سبعة","ثمانية","تسعة","عشرة","أحد عشر","اثنا عشر","ثلاثة عشر","أربعة عشر","خمسة عشر","ستة عشر","سبعة عشر","ثمانية عشر","تسعة عشر"];
        $dizaines = ["","","عشرون","ثلاثون","أربعون","خمسون","ستون","سبعون","ثمانون","تسعون"];
        return trim($this->convertirNombreEnLettresArabe($montant, $unites, $dizaines)) . " دينار جزائري";
    }

    private function convertirNombreEnLettresArabe($nombre, $unites, $dizaines)
    {
        if ($nombre < 20) return $unites[$nombre];
        if ($nombre < 100) {
            $d = floor($nombre / 10); $u = $nombre % 10;
            return $u > 0 ? $unites[$u] . " و" . $dizaines[$d] : $dizaines[$d];
        }
        if ($nombre < 1000) {
            $c = floor($nombre / 100); $r = $nombre % 100;
            $centaines = ["","مائة","مئتان","ثلاثمائة","أربعمائة","خمسمائة","ستمائة","سبعمائة","ثمانمائة","تسعمائة"];
            $res = $centaines[$c];
            if ($r > 0) $res .= " و" . $this->convertirNombreEnLettresArabe($r, $unites, $dizaines);
            return $res;
        }
        if ($nombre < 1000000) {
            $m = floor($nombre / 1000); $r = $nombre % 1000;
            if ($m == 1) $res = "ألف"; elseif ($m == 2) $res = "ألفان";
            elseif ($m <= 10) $res = $unites[$m] . " آلاف";
            else $res = $this->convertirNombreEnLettresArabe($m, $unites, $dizaines) . " ألف";
            if ($r > 0) $res .= " و" . $this->convertirNombreEnLettresArabe($r, $unites, $dizaines);
            return $res;
        }
        if ($nombre < 1000000000) {
            $m = floor($nombre / 1000000); $r = $nombre % 1000000;
            if ($m == 1) $res = "مليون"; elseif ($m == 2) $res = "مليونان";
            elseif ($m <= 10) $res = $unites[$m] . " ملايين";
            else $res = $this->convertirNombreEnLettresArabe($m, $unites, $dizaines) . " مليون";
            if ($r > 0) $res .= " و" . $this->convertirNombreEnLettresArabe($r, $unites, $dizaines);
            return $res;
        }
        return "مبلغ كبير جدا";
    }
}