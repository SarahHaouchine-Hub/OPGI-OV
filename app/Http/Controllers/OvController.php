<?php

namespace App\Http\Controllers;

use ArPHP\I18N\Arabic;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Logement;
use App\Models\Aide;
use App\Models\CreditBancaire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Souscripteur;
use Illuminate\Http\Request;
use App\Models\Ov;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Paiement;

class OvController extends Controller
{
    // ── Tranches fixes LPA (% de Prix2 = Prix − BNH) ──────────────────────────
    private const LPA_TRANCHES = [
        1 => 25,
        2 => 15,
        3 => 35,
        4 => 25,
        5 =>  5,
    ];

    // FNPOS toujours 500 000 DA (déduite uniquement de T4)
    private const FNPOS_MONTANT_FIXE = 500000.00;

    // =========================================================================
    // INDEX
    // =========================================================================
    public function index(Request $request)
    {
        $query = Souscripteur::with([
                'logement.programme',
                'ovs.paiement',
                'aides',
                 'creditBancaire',
            ])
            ->whereHas('logement', fn($q) => $q->whereIn('flag', [1, 2]));

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

        $souscripteurs = $query->orderBy('created_at', 'desc')->get();
        return view('listeOv', compact('souscripteurs'));
    }

    // =========================================================================
    // CREATE — routage par programme
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

        $programme = $this->getProgrammeType($souscripteur);

        return match ($programme) {
            'LPA'   => $this->createLpa($souscripteur),
            'LSP'   => $this->createLsp($souscripteur),
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
        $prixLogement     = (float)($souscripteur->logement->prix ?? 0);
        $aideBnh          = $souscripteur->aides->firstWhere('type', 'bnh');
        $aideFnpos        = $souscripteur->aides->firstWhere('type', 'fnpos');
        $creditBancaire   = $souscripteur->creditBancaire ?? null;
        $ovsDone          = $souscripteur->ovs->sortBy('numero_tranche');
       // ✅ APRÈS — cohérent avec storeLpa()
$ovsDoneNormaux   = $ovsDone->where('type_ov', null);
$prochaineTranche = $ovsDoneNormaux->count() + 1;
        $code_loge        = $souscripteur->code_loge_lpl;
        $site             = $souscripteur->logement->site;

        // ── Toutes les tranches normales faites (sans crédit) ─────────────────────
        if ($prochaineTranche > 5 && $creditBancaire === null) {
            return redirect()->route('ov.index')
                ->with('error', 'Toutes les tranches LPA ont été générées.');
        }

        // ── Crédit enregistré : vérifier l'état du dossier ───────────────────────
       // Dans createLpa(), remplacez le bloc "Crédit enregistré"
if ($creditBancaire !== null) {
    // Utilise numero_tranche ET type_ov (double sécurité)
    $ovT2Fait = $ovsDone->contains(
        fn($o) => $o->numero_tranche === 2 && 
                  in_array($o->type_ov, ['credit_reel', null])
                  // null accepté pour anciens enregistrements
    );
    $ovT3Fait = $ovsDone->contains(
        fn($o) => $o->numero_tranche === 3 && 
                  in_array($o->type_ov, ['credit_diff', null])
    );
    $diffCredit   = $creditBancaire->montant_attestation - $creditBancaire->montant_reel;
    $dossierSolde = $ovT2Fait && ($diffCredit <= 0 || $ovT3Fait);

    if ($dossierSolde) {
        return redirect()->route('ov.index')
            ->with('success', 'Dossier soldé — crédit bancaire entièrement traité.');
    }
}

        $montantBnh   = (float)($aideBnh->montant ?? 0);
        $fnposMontant = self::FNPOS_MONTANT_FIXE;
        $prix2        = max(0.0, $prixLogement - $montantBnh);
        $totalPaye    = (float)$ovsDone->where('type_ov', null)->sum('montant_paye');
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

        // ── Le crédit bancaire est affichable SEULEMENT si :
        //    - Au moins T1 générée
        //    - BNH enregistrée
        //    - Aucune T2 normale déjà générée (type_ov = null ET numero_tranche = 2)
        $ovT2Normal = $ovsDone->where('type_ov', null)->where('numero_tranche', 2)->first();

        $peutAfficherCredit = (
            $ovsDone->where('type_ov', null)->count() >= 1 &&
            $aideBnh !== null &&
            $ovT2Normal === null  // ← NOUVEAU : bloqué si T2 normale existe
        );

        $montantAttestationAuto = null;
        if ($aideFnpos && $ovsDone->where('type_ov', null)->count() >= 1) {
            $montantT1 = (float)($ovsDone->firstWhere('numero_tranche', 1)->montant_paye ?? 0);
            $montantAttestationAuto = max(0.0, ($prix2 - $montantT1) - $fnposMontant);
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
        $prixLogement     = $souscripteur->logement->prix ?? 0;
        $aideBnh          = $souscripteur->aides->firstWhere('type', 'bnh');
        $aideFnpos        = $souscripteur->aides->firstWhere('type', 'fnpos');
        $totalAides       = ($aideBnh->montant ?? 0) + ($aideFnpos->montant ?? 0);
        $ovsDone          = $souscripteur->ovs->sortBy('numero_tranche');
        $totalPaye        = $ovsDone->sum('montant_paye');
        $resteAPayer      = max(0, $prixLogement - $totalAides - $totalPaye);
        $prochaineTranche = $ovsDone->count() + 1;
        $code_loge        = $souscripteur->code_loge_lpl;

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

        if ($request->solde_reste <= 0) {
            return back()->with('error', 'Le souscripteur a déjà payé la totalité du prix.');
        }

        DB::beginTransaction();
        try {
            $baseDeCalcul    = (float) $request->solde_reste;
            $pourcentage     = (int)   $request->pourcentage;
            $total           = (float) $request->montant_total;
            $calculTheorique = ($total * $pourcentage) / 100;
            $montantAPayer   = min($calculTheorique, $baseDeCalcul);
            $nouveauReste    = max(0, $baseDeCalcul - $montantAPayer);

            $souscripteur = Souscripteur::with('logement')->findOrFail($request->souscripteur_id);
            [$qrDataPlain, $qrDataHashed, $qrcodeData] = $this->buildQr(
                'LPL', $souscripteur, $montantAPayer
            );

            $ov = Ov::create([
                'souscripteur_id'   => $souscripteur->id,
                'montant_total'     => $total,
                'pourcentage'       => $pourcentage,
                'montant_paye'      => $montantAPayer,
                'montant_restant'   => $nouveauReste,
                'qr_content_plain'  => $qrDataPlain,
                'qr_content_hashed' => $qrDataHashed,
                'qrcode'            => $qrcodeData,
                'user_id'           => Auth::id(),
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

        $souscripteur     = Souscripteur::with(['logement', 'ovs', 'aides'])->findOrFail($request->souscripteur_id);
        $creditBancaire   = $souscripteur->creditBancaire ?? null;

        // Compter uniquement les tranches normales (hors credit_diff)
        $ovsDoneNormaux   = $souscripteur->ovs->where('type_ov', null);
        $prochaineTranche = $ovsDoneNormaux->count() + 1;
        $numeroRecu       = (int) $request->numero_tranche;

        if ($numeroRecu !== $prochaineTranche) {
            return back()->with('error', 'Numéro de tranche invalide. Attendu : ' . $prochaineTranche);
        }

        if ($prochaineTranche > 5) {
            return back()->with('error', 'Toutes les tranches ont déjà été générées.');
        }

        // ── Bloquer T2→T5 si crédit bancaire déjà enregistré ─────────────────────
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
            $prixLogement = (float) $souscripteur->logement->prix;
            $aideFnpos    = $souscripteur->aides->firstWhere('type', 'fnpos');
            $montantBnh   = (float)($aideBnh->montant ?? 0);

            // Total versé sur tranches normales uniquement
            $totalPaye    = (float) $ovsDoneNormaux->sum('montant_paye');

            $prix2       = max(0.0, $prixLogement - $montantBnh);
            $resteGlobal = max(0.0, $prix2 - $totalPaye);

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

            // ── VSP : cochable une seule fois sur toute la vie du dossier ─────────
            $vspDejaFait = $souscripteur->ovs->contains(fn($o) => (bool)$o->vsp);
            $vsp         = (!$vspDejaFait) ? $request->boolean('vsp') : false;

            [$qrDataPlain, $qrDataHashed, $qrcodeData] = $this->buildQr(
                'LPA', $souscripteur, $montantAPayer, $prochaineTranche
            );

            $ov = Ov::create([
                'souscripteur_id'   => $souscripteur->id,
                'montant_total'     => $prixLogement,
                'pourcentage'       => $pourcentage,
                'montant_paye'      => $montantAPayer,
                'montant_restant'   => $montantRestant,
                'numero_tranche'    => $prochaineTranche,
                'vsp'               => $vsp,
                  'type_ov'           => null,            // ← EXPLICITE : tranche normale
                'qr_content_plain'  => $qrDataPlain,
                'qr_content_hashed' => $qrDataHashed,
                'qrcode'            => $qrcodeData,
                'user_id'           => Auth::id(),
            ]);

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

        $souscripteur = Souscripteur::with(['logement', 'ovs', 'aides'])->findOrFail($request->souscripteur_id);

        $prixLogement     = (float) $souscripteur->logement->prix;
        $totalAides       = (float) $souscripteur->aides()->sum('montant');
        $totalPaye        = (float) $souscripteur->ovs()->sum('montant_paye');
        $resteAPayer      = max(0, $prixLogement - $totalAides - $totalPaye);
        $prochaineTranche = $souscripteur->ovs()->count() + 1;

        if ($resteAPayer <= 0) {
            return back()->with('error', 'Le montant est entièrement soldé. Aucun OV à générer.');
        }

        $montantSaisi = (float) $request->montant_a_payer;

        if ($montantSaisi > $resteAPayer) {
            return back()->with('error', 'Le montant saisi dépasse le reste à payer (' . number_format($resteAPayer, 2, ',', ' ') . ' DA).');
        }

        $nouveauReste = max(0, $resteAPayer - $montantSaisi);

        DB::beginTransaction();
        try {
            [$qrDataPlain, $qrDataHashed, $qrcodeData] = $this->buildQr(
                'LSP', $souscripteur, $montantSaisi, $prochaineTranche
            );

            $ov = Ov::create([
                'souscripteur_id'   => $souscripteur->id,
                'montant_total'     => $prixLogement,
                'pourcentage'       => round(($montantSaisi / max(1, $prixLogement - $totalAides)) * 100, 2),
                'montant_paye'      => $montantSaisi,
                'montant_restant'   => $nouveauReste,
                'numero_tranche'    => $prochaineTranche,
                'vsp'               => false,
                'qr_content_plain'  => $qrDataPlain,
                'qr_content_hashed' => $qrDataHashed,
                'qrcode'            => $qrcodeData,
                'user_id'           => Auth::id(),
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

        $souscripteur = Souscripteur::with('logement.site')->findOrFail($request->souscripteur_id);

        $existe = Aide::where('souscripteur_id', $request->souscripteur_id)
                      ->where('type', $request->type)
                      ->exists();

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
    // STORE — CRÉDIT BANCAIRE (CORRIGÉ - VERSION COMPLÈTE)
    // =========================================================================
    public function storeCreditBancaire(Request $request)
    {
        $request->validate([
            'souscripteur_id'     => 'required|exists:souscripteurs,id',
            'montant_attestation' => 'required|numeric|min:1',
            'montant_reel'        => 'required|numeric|min:1',
            'date_attestation'    => 'required|date',
            'date_versement_reel' => 'nullable|date',
            'pieces_jointes'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $souscripteur = Souscripteur::with(['ovs', 'aides', 'creditBancaire', 'logement.site'])
            ->findOrFail($request->souscripteur_id);

        // ══════════════════════════════════════════════════════════════════════
        // ÉTAPE 1 : VÉRIFICATIONS PRÉALABLES
        // ══════════════════════════════════════════════════════════════════════
        
        // ── Aide BNH obligatoire ───────────────────────────────────────────────
        $aideBnh = $souscripteur->aides->firstWhere('type', 'bnh');
        if (!$aideBnh) {
            return back()->with('error',
                "L'aide BNH doit être enregistrée avant d'enregistrer un crédit bancaire."
            );
        }

        // ── Aide FNPOS obligatoire ─────────────────────────────────────────────
        $aideFnpos = $souscripteur->aides->firstWhere('type', 'fnpos');
        if (!$aideFnpos) {
            return back()->with('error',
                "L'aide FNPOS doit être enregistrée avant d'enregistrer un crédit bancaire."
            );
        }

        // ── Un seul crédit bancaire autorisé ───────────────────────────────────
        if ($souscripteur->creditBancaire) {
            return back()->with('error', 
                'Un crédit bancaire est déjà enregistré pour ce souscripteur.');
        }

        // ── T1 doit exister ET être payée ──────────────────────────────────────
        $ovT1 = $souscripteur->ovs
            ->where('type_ov', null)
            ->where('numero_tranche', 1)
            ->first();

        if (!$ovT1) {
            return back()->with('error', 
                'La Tranche 1 doit être générée avant d\'enregistrer un crédit bancaire.');
        }

        if (!$ovT1->paiement) {
            return back()->with('error', 
                '⚠️ La Tranche 1 doit être PAYÉE avant d\'enregistrer un crédit bancaire.');
        }

        // ── Bloquer si T2 normale déjà générée ─────────────────────────────────
        $ovT2Normal = $souscripteur->ovs
            ->where('type_ov', null)
            ->where('numero_tranche', 2)
            ->first();

        if ($ovT2Normal) {
            return back()->with('error',
                'Impossible d\'enregistrer un crédit bancaire : la Tranche 2 normale a déjà été générée. '
                . 'Le crédit bancaire n\'est autorisé qu\'après la Tranche 1 uniquement.'
            );
        }

        // ══════════════════════════════════════════════════════════════════════
        // ÉTAPE 2 : CALCUL DU MONTANT ATTENDU
        // Formule : (Prix − BNH) − T1 − FNPOS
        // ══════════════════════════════════════════════════════════════════════
        
        $prixLogement = (float) $souscripteur->logement->prix;
        $montantBnh   = (float) $aideBnh->montant;
        $montantFnpos = self::FNPOS_MONTANT_FIXE;
        $montantT1    = (float) $ovT1->montant_paye;

        // Calcul du reste à payer par crédit bancaire
        $montantAttendu = $prixLogement - $montantBnh - $montantT1 - $montantFnpos;

        if ($montantAttendu <= 0) {
            return back()->with('error',
                'Le montant restant à payer est nul ou négatif. Aucun crédit bancaire nécessaire. '
                . 'Calcul : (' . number_format($prixLogement, 2, ',', ' ') 
                . ' − ' . number_format($montantBnh, 2, ',', ' ') 
                . ') − ' . number_format($montantT1, 2, ',', ' ') 
                . ' − ' . number_format($montantFnpos, 2, ',', ' ') 
                . ' = ' . number_format($montantAttendu, 2, ',', ' ') . ' DA.'
            );
        }

        // ══════════════════════════════════════════════════════════════════════
        // ÉTAPE 3 : VALIDATION DES MONTANTS SAISIS
        // ══════════════════════════════════════════════════════════════════════
        
        $montantAttestation = (float) $request->montant_attestation;
        $montantReel        = (float) $request->montant_reel;

        // ── Vérification : montant_attestation = montant attendu ───────────────
        $tolerance = 0.01; // Tolérance d'arrondi (1 centime)
        
        if (abs($montantAttestation - $montantAttendu) > $tolerance) {
            return back()->with('error',
                '❌ Le montant de l\'attestation (' . number_format($montantAttestation, 2, ',', ' ') . ' DA) '
                . 'ne correspond pas au montant attendu (' . number_format($montantAttendu, 2, ',', ' ') . ' DA). '
                . '<br><br><strong>Formule de calcul :</strong><br>'
                . '(Prix − BNH) − T1 − FNPOS<br>'
                . '= (' . number_format($prixLogement, 2, ',', ' ')
                . ' − ' . number_format($montantBnh, 2, ',', ' ')
                . ') − ' . number_format($montantT1, 2, ',', ' ')
                . ' − ' . number_format($montantFnpos, 2, ',', ' ')
                . '<br>= <strong>' . number_format($montantAttendu, 2, ',', ' ') . ' DA</strong>'
            )->withInput();
        }

        // ── Vérification : montant_reel ≤ montant_attestation ──────────────────
        if ($montantReel > $montantAttestation) {
            return back()->with('error',
                '❌ Le montant réel (' . number_format($montantReel, 2, ',', ' ') . ' DA) '
                . 'ne peut pas dépasser le montant de l\'attestation (' 
                . number_format($montantAttestation, 2, ',', ' ') . ' DA).'
            )->withInput();
        }

        // Calcul de la différence (OV complémentaire si > 0)
        $difference = max(0.0, $montantAttestation - $montantReel);

        // ══════════════════════════════════════════════════════════════════════
        // ÉTAPE 4 : ENREGISTREMENT
        // ══════════════════════════════════════════════════════════════════════
        
        $filePath = null;
        if ($request->hasFile('pieces_jointes')) {
            $filePath = $request->file('pieces_jointes')->store('credits_pj', 'public');
        }

        DB::beginTransaction();
        try {
            // ── 1. Enregistrer le crédit bancaire ──────────────────────────────
            $credit = CreditBancaire::create([
                'souscripteur_id'     => $souscripteur->id,
                'montant_attestation' => $montantAttestation,
                'montant_reel'        => $montantReel,
                'date_attestation'    => $request->date_attestation,
                'date_versement_reel' => $request->date_versement_reel,
                'pieces_jointes'      => $filePath,
                'user_id'             => Auth::id(),
            ]);

            // ── 2. Générer T2 = montant_reel, statut PAYÉ automatiquement ──────
            [$qrPlainT2, $qrHashedT2, $qrDataT2] = $this->buildQr(
                'LPA-CREDIT', $souscripteur, $montantReel, 2
            );

            $ovT2 = Ov::create([
                'souscripteur_id'   => $souscripteur->id,
                'montant_total'     => $prixLogement,
                'pourcentage'       => round(($montantReel / max(1, $prixLogement)) * 100, 2),
                'montant_paye'      => $montantReel,
                'montant_restant'   => $difference,
                'numero_tranche'    => 2,
                'vsp'               => false,
                'type_ov'           => 'credit_reel',
                'qr_content_plain'  => $qrPlainT2,
                'qr_content_hashed' => $qrHashedT2,
                'qrcode'            => $qrDataT2,
                'user_id'           => Auth::id(),
            ]);

            // Paiement automatique T2
            Paiement::create([
                'ov_id'         => $ovT2->id,
                'num_recu'      => 'CREDIT-AUTO-' . $souscripteur->id . '-' . time(),
                'nom_agence'    => $souscripteur->logement->site->nom_agence ?? 'Banque',
                'num_agence'    => $souscripteur->logement->site->num_agence ?? '—',
                'date_paiement' => $request->date_versement_reel ?? $request->date_attestation,
                'recu_pdf'      => null,
                'user_id'       => Auth::id(),
            ]);

            // ── 3. Générer T3 = différence si > 0, statut EN ATTENTE ───────────
            $ovT3 = null;
            if ($difference > 0) {
                [$qrPlainT3, $qrHashedT3, $qrDataT3] = $this->buildQr(
                    'LPA-DIFF', $souscripteur, $difference, 3
                );

                $ovT3 = Ov::create([
                    'souscripteur_id'   => $souscripteur->id,
                    'montant_total'     => $prixLogement,
                    'pourcentage'       => round(($difference / max(1, $prixLogement)) * 100, 2),
                    'montant_paye'      => $difference,
                    'montant_restant'   => 0,
                    'numero_tranche'    => 3,
                    'vsp'               => false,
                    'type_ov'           => 'credit_diff',
                    'qr_content_plain'  => $qrPlainT3,
                    'qr_content_hashed' => $qrHashedT3,
                    'qrcode'            => $qrDataT3,
                    'user_id'           => Auth::id(),
                ]);
            }

            Logement::where('code_loge_lpl', $souscripteur->code_loge_lpl)->update(['flag' => 2]);
            DB::commit();

            // ══════════════════════════════════════════════════════════════════
            // ÉTAPE 5 : RETOUR AVEC MESSAGES
            // ══════════════════════════════════════════════════════════════════
            
            // PDF T2 auto-ouvert
            $pdfUrl = route('ov.pdf', Hashids::encode($ovT2->id));

            // Message de confirmation selon le cas
            $recapCalcul = '<br><br><strong>📊 Récapitulatif :</strong><br>'
                . '• Prix logement : ' . number_format($prixLogement, 2, ',', ' ') . ' DA<br>'
                . '• Aide BNH : −' . number_format($montantBnh, 2, ',', ' ') . ' DA<br>'
                . '• T1 payée : −' . number_format($montantT1, 2, ',', ' ') . ' DA<br>'
                . '• Aide FNPOS : −' . number_format($montantFnpos, 2, ',', ' ') . ' DA<br>'
                . '• <strong>Reste (crédit) : ' . number_format($montantAttendu, 2, ',', ' ') . ' DA</strong><br>'
                . '• T2 (crédit réel) : ' . number_format($montantReel, 2, ',', ' ') . ' DA ✅ PAYÉE';

            if ($difference > 0) {
                $recapCalcul .= '<br>• T3 (différence) : ' 
                    . number_format($difference, 2, ',', ' ') . ' DA ⏳ EN ATTENTE';
                
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
                ->with('success',
                    '✅ Crédit bancaire enregistré avec succès !'
                    . $recapCalcul
                );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    // =========================================================================
    // STORE — OV COMPLÉMENTAIRE (différence crédit bancaire)
    // =========================================================================
    public function storeOvCredit(Request $request)
    {
        return redirect()->route('ov.index')
            ->with('error', 'L\'OV complémentaire est généré automatiquement lors de l\'enregistrement du crédit bancaire.');
    }

    // =========================================================================
    // PDF
    // =========================================================================
    public function generatePDF($id)
    {
        $ov = Ov::with('souscripteur.logement')->findOrFail($id);

        $Arabic      = new Arabic();
        $republique  = $Arabic->utf8Glyphs("الجمهورية الجزائرية الديمقراطية الشعبية");
        $ministere   = $Arabic->utf8Glyphs("وزارة السكن والعمران والمدينة و التهيئة العمرانية");
        $agence      = $Arabic->utf8Glyphs("الوكالة الوطنية لتحسين السكن وتطويره");
        $logoAADL    = base64_encode(file_get_contents(public_path('images/AADL_logo.svg')));
        $algeria     = base64_encode(file_get_contents(public_path('images/algeria(1).svg')));
        $ordre       = $Arabic->utf8Glyphs("أمر بالدفع");
        $sous        = $Arabic->utf8Glyphs(" عزيزي المكتتب،");
        $document    = $Arabic->utf8Glyphs("هذه الوثيقة تشكل أمر دفع يمثل");
        $montant_total  = $Arabic->utf8Glyphs("من المبلغ الإجمالي للسكن");
        $da          = $Arabic->utf8Glyphs("دج");
        $num_ordre   = $Arabic->utf8Glyphs("رقم الأمر :");
        $programme   = $Arabic->utf8Glyphs("في إطار برنامج السكن الترقوي المدعم.");
        $apayer      = $Arabic->utf8Glyphs("المبلغ الواجب دفعه :");
        $lettres     = $Arabic->utf8Glyphs("بالحروف :");
        $nom_ar      = $Arabic->utf8Glyphs("اللقب :");
        $prenom_ar   = $Arabic->utf8Glyphs("الاسم :");
        $date_n_ar   = $Arabic->utf8Glyphs("تاريخ الميلاد :");
        $code_loge_ar = $Arabic->utf8Glyphs("رمز السكن :");

        $textePrincipal = "تُشكّل هذه الوثيقة أمراً بالدفع يُمثّل {$ov->pourcentage}% من المبلغ الإجمالي للسكن " .
                          number_format($ov->montant_total, 2, ',', '.') .
                          " دج وذلك في إطار برنامج السكن الترقوي المدعم.";

        $texte_principal    = $textePrincipal;
        $montantEnLettres   = $this->montantEnLettres($ov->montant_paye);
        $montantEnLettresAr = $this->montantEnLettresArabe($ov->montant_paye);
        $lettres_ar         = $Arabic->utf8Glyphs($montantEnLettresAr);
        $nomAr              = $Arabic->utf8Glyphs($ov->souscripteur->nom_arabe);
        $prenomAr           = $Arabic->utf8Glyphs($ov->souscripteur->prenom_arabe);

        $customPaper = [0, 0, 595.28, 283.46];

        $pdf = Pdf::loadView(
            'ordre_versement_pdf',
            compact(
                'ov', 'montantEnLettres', 'montantEnLettresAr', 'republique', 'ministere',
                'agence', 'logoAADL', 'algeria', 'nomAr', 'prenomAr', 'ordre', 'sous',
                'document', 'montant_total', 'da', 'programme', 'num_ordre', 'apayer',
                'lettres', 'nom_ar', 'prenom_ar', 'date_n_ar', 'code_loge_ar', 'lettres_ar',
                'texte_principal', 'Arabic'
            )
        )->setPaper($customPaper);

        return $pdf->stream('OV_' . $ov->id . '.pdf');
    }

    // =========================================================================
    // PAIEMENT
    // =========================================================================
    public function createPaiement($ovId)
    {
        $ov = Ov::with('souscripteur.logement.site')->findOrFail($ovId);

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
            'nom_agence'    => 'required|string',
            'num_agence'    => 'required|string',
            'pj.*'          => 'required|file|mimes:pdf',
        ]);

        $filePath = null;
        if ($request->hasFile('pj')) {
            $ov       = Ov::findOrFail($request->ov_id);
            $file     = $request->file('pj')[0];
            $fileName = 'recu_' . $ov->id . '_' . time() . '.pdf';
            $filePath = $file->storeAs('recus_paiements', $fileName, 'public');
        }

        try {
            DB::beginTransaction();
            Paiement::create([
                'ov_id'         => $request->ov_id,
                'num_recu'      => $request->num_recu,
                'nom_agence'    => $request->nom_agence,
                'num_agence'    => $request->num_agence,
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
    // HELPERS PRIVÉS
    // =========================================================================
    private function getProgrammeType(Souscripteur $souscripteur): string
    {
        $prog = $souscripteur->logement->programme->libelle ?? 'LPL';
        return strtoupper(trim($prog));
    }

    private function buildQr(
        string $programmeCode,
        Souscripteur $souscripteur,
        float $montant,
        ?int $tranche = null
    ): array {
        $plain = sprintf(
            'AADL %s | Nom: %s | Prénom: %s | Code: %s%s | Montant: %.2f',
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

    // ── Conversion montant en lettres (FR) ────────────────────────────────────
    private function montantEnLettres($montant)
    {
        $montant  = floor($montant);
        if ($montant == 0) return "zéro dinar algérien";
        $unites   = ["","un","deux","trois","quatre","cinq","six","sept","huit","neuf","dix","onze","douze","treize","quatorze","quinze","seize","dix-sept","dix-huit","dix-neuf"];
        $dizaines = ["","","vingt","trente","quarante","cinquante","soixante","soixante","quatre-vingt","quatre-vingt"];
        return ucfirst($this->convertirNombreEnLettres($montant, $unites, $dizaines) . " Dinar Algérien");
    }

    private function convertirNombreEnLettres($nombre, $unites, $dizaines)
    {
        if ($nombre < 20) return $unites[$nombre];
        if ($nombre < 100) {
            $d = floor($nombre / 10); $u = $nombre % 10;
            $r = $dizaines[$d];
            if ($d == 7 || $d == 9)               $r  = $dizaines[$d] . "-" . $unites[10 + $u];
            elseif ($d == 8 && $u == 0)            $r .= "s";
            elseif ($u == 1 && $d > 1 && $d != 8) $r .= " et un";
            elseif ($u > 0)                         $r .= "-" . $unites[$u];
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

    // ── Conversion montant en lettres (AR) ────────────────────────────────────
    private function montantEnLettresArabe($montant)
    {
        $montant  = floor($montant);
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
            if      ($m == 1)  $res = "ألف";
            elseif  ($m == 2)  $res = "ألفان";
            elseif  ($m <= 10) $res = $unites[$m] . " آلاف";
            else               $res = $this->convertirNombreEnLettresArabe($m, $unites, $dizaines) . " ألف";
            if ($r > 0) $res .= " و" . $this->convertirNombreEnLettresArabe($r, $unites, $dizaines);
            return $res;
        }
        if ($nombre < 1000000000) {
            $m = floor($nombre / 1000000); $r = $nombre % 1000000;
            if      ($m == 1)  $res = "مليون";
            elseif  ($m == 2)  $res = "مليونان";
            elseif  ($m <= 10) $res = $unites[$m] . " ملايين";
            else               $res = $this->convertirNombreEnLettresArabe($m, $unites, $dizaines) . " مليون";
            if ($r > 0) $res .= " و" . $this->convertirNombreEnLettresArabe($r, $unites, $dizaines);
            return $res;
        }
        return "مبلغ كبير جدا";
    }
}