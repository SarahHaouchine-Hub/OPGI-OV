<?php

namespace App\Http\Controllers;

use ArPHP\I18N\Arabic;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Logement;
use App\Models\Aide;
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
    // ── Tranches fixes LPA ─────────────────────────────────────────────────────
    private const LPA_TRANCHES = [
        1 => 20,
        2 => 15,
        3 => 35,
        4 => 25,
        5 => 5,
    ];

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index()
    {
        $souscripteurs = Souscripteur::with([
                'logement.programme',   // adapter selon votre structure
                'ovs.paiement',
                'aides',
            ])
            ->whereHas('logement', fn($q) => $q->whereIn('flag', [1, 2]))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('listeOv', compact('souscripteurs'));
    }

    // =========================================================================
    // CREATE — routage par programme
    // =========================================================================

    public function create($id)
    {
        $souscripteur = Souscripteur::with([
            'logement.programme',
            'ovs',
            'aides',
        ])->findOrFail($id);

        $programme = $this->getProgrammeType($souscripteur);

        return match ($programme) {
            'LPA'   => $this->createLpa($souscripteur),
            'LSP'   => $this->createLsp($souscripteur),
            default => $this->createLpl($souscripteur),   // LPL
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
    $prixLogement     = $souscripteur->logement->prix ?? 0;
    $aideCnl          = $souscripteur->aides->firstWhere('type', 'cnl');
    $aideFnpos        = $souscripteur->aides->firstWhere('type', 'fnpos');
    $ovsDone          = $souscripteur->ovs->sortBy('numero_tranche');
    $prochaineTranche = $ovsDone->count() + 1;
    $code_loge        = $souscripteur->code_loge_lpl;

    if ($prochaineTranche > 5) {
        return redirect()->route('ov.index')
            ->with('error', 'Toutes les tranches LPA ont été générées.');
    }

    $totalPaye  = $ovsDone->sum('montant_paye');
    $totalAides = ($aideCnl->montant ?? 0) + ($aideFnpos->montant ?? 0);
    $tranches   = self::LPA_TRANCHES;
    $pourcentage = $tranches[$prochaineTranche];

    // ✅ Base = ce qui reste réellement à payer maintenant
    $baseCalcul = max(0, $prixLogement - $totalPaye - $totalAides);

    $montantTranche  = ($baseCalcul * $pourcentage) / 100;
    $montantRestant  = max(0, $baseCalcul - $montantTranche);

    return view('createOvLpa', compact(
        'souscripteur', 'prixLogement', 'aideCnl', 'aideFnpos',
        'ovsDone', 'prochaineTranche', 'tranches', 'pourcentage',
        'montantTranche', 'montantRestant', 'totalPaye', 'totalAides',
        'baseCalcul', 'code_loge'
    ));
}
    // ── LSP ───────────────────────────────────────────────────────────────────

    private function createLsp(Souscripteur $souscripteur)
    {
        $prixLogement = $souscripteur->logement->prix ?? 0;
        $aideCnl      = $souscripteur->aides->firstWhere('type', 'cnl');
        $aideFnpos    = $souscripteur->aides->firstWhere('type', 'fnpos');
        $totalAides   = ($aideCnl->montant ?? 0) + ($aideFnpos->montant ?? 0);
        $montantOv    = max(0, $prixLogement - $totalAides);
        $ovExistant   = $souscripteur->ovs->first();
        $code_loge    = $souscripteur->code_loge_lpl;

        return view('createOvLsp', compact(
            'souscripteur', 'prixLogement', 'aideCnl', 'aideFnpos',
            'totalAides', 'montantOv', 'ovExistant', 'code_loge'
        ));
    }

    // =========================================================================
    // STORE — LPL (inchangé)
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
            $baseDeCalcul   = (float) $request->solde_reste;
            $pourcentage    = (int) $request->pourcentage;
            $total          = (float) $request->montant_total;
            $calculTheorique = ($total * $pourcentage) / 100;
            $montantAPayer  = min($calculTheorique, $baseDeCalcul);
            $nouveauReste   = max(0, $baseDeCalcul - $montantAPayer);

            $souscripteur = Souscripteur::with('logement')->findOrFail($request->souscripteur_id);
            [$qrDataPlain, $qrDataHashed, $qrcodeData] = $this->buildQr(
                'LPL', $souscripteur, $montantAPayer
            );

            $ov = Ov::create([
                'souscripteur_id' => $souscripteur->id,
                'montant_total'   => $total,
                'pourcentage'     => $pourcentage,
                'montant_paye'    => $montantAPayer,
                'montant_restant' => $nouveauReste,
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
    $prochaineTranche = $souscripteur->ovs()->count() + 1;
    $numeroRecu       = (int) $request->numero_tranche;

    // Cohérence tranche
    if ($numeroRecu !== $prochaineTranche) {
        return back()->with('error', 'Numéro de tranche invalide. Attendu : ' . $prochaineTranche);
    }

    if ($prochaineTranche > 5) {
        return back()->with('error', 'Toutes les tranches ont déjà été générées.');
    }

    // CNL obligatoire
    if (! $souscripteur->aides->firstWhere('type', 'cnl')) {
        return back()->with('error', "L'aide CNL doit être enregistrée avant de générer un OV LPA.");
    }

    // VSP obligatoire pour tranche 2
    if ($prochaineTranche === 2 && ! $request->boolean('vsp')) {
        return back()->with('error', 'Vous devez cocher le VSP pour générer la 2ème tranche.');
    }

    DB::beginTransaction();
    try {
        $prixLogement = (float) $souscripteur->logement->prix;
        $pourcentage  = self::LPA_TRANCHES[$prochaineTranche];

        // Récupération des aides connues au moment de la génération
        $aideCnl    = $souscripteur->aides->firstWhere('type', 'cnl');
        $aideFnpos  = $souscripteur->aides->firstWhere('type', 'fnpos');
        $totalAides = ($aideCnl->montant ?? 0) + ($aideFnpos->montant ?? 0);

        // Total déjà versé via les OVs précédents
        $totalPaye = (float) $souscripteur->ovs()->sum('montant_paye');

        // Base = ce qui reste réellement à payer (prix - versé - toutes aides connues)
        $baseCalcul = max(0, $prixLogement - $totalPaye - $totalAides);

        // Montant de cette tranche
        $montantAPayer = ($baseCalcul * $pourcentage) / 100;

        // Reste après cette tranche
        $montantRestant = max(0, $baseCalcul - $montantAPayer);

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
            'vsp'               => ($prochaineTranche === 2) ? $request->boolean('vsp') : false,
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
    ]);

    $souscripteur = Souscripteur::with(['logement', 'ovs', 'aides'])->findOrFail($request->souscripteur_id);

    if ($souscripteur->ovs()->count() > 0) {
        return back()->with('error', "L'ordre de versement LSP a déjà été généré.");
    }

    $prixLogement  = (float) $souscripteur->logement->prix;
    $totalAides    = (float) $souscripteur->aides()->sum('montant');
    $montantAPayer = max(0, $prixLogement - $totalAides);

    // On génère même si montant = 0 (cas rare mais possible)
    // Retirez le blocage ci-dessous si vous voulez autoriser montant = 0
    if ($montantAPayer <= 0) {
        return back()->with('error', 'Le montant est entièrement couvert par les aides. Aucun OV à générer.');
    }

    DB::beginTransaction();
    try {
        [$qrDataPlain, $qrDataHashed, $qrcodeData] = $this->buildQr(
            'LSP', $souscripteur, $montantAPayer
        );

        $ov = Ov::create([
            'souscripteur_id'   => $souscripteur->id,
            'montant_total'     => $prixLogement,
            'pourcentage'       => 100,
            'montant_paye'      => $montantAPayer,
            'montant_restant'   => 0,
            'numero_tranche'    => 1,
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
            ->with('success', 'Ordre de versement LSP généré avec succès.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Erreur : ' . $e->getMessage());
    }
}

    // =========================================================================
    // STORE — AIDE (CNL / FNPOS) — commun LPA & LSP
    // =========================================================================

    public function storeAide(Request $request)
    {
        $rules = [
            'souscripteur_id' => 'required|exists:souscripteurs,id',
            'type'            => 'required|in:cnl,fnpos',
            'montant'         => 'required|numeric|min:1',
            'num_decision'    => 'required|string|max:100',
            'date'            => 'required|date',
            'pieces_jointes'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        if ($request->type === 'cnl') {
            $rules['num_convention'] = 'required|string|max:100';
        }

        $request->validate($rules);

        // Vérifier qu'une aide de ce type n'existe pas déjà
        $existe = Aide::where('souscripteur_id', $request->souscripteur_id)
                      ->where('type', $request->type)
                      ->exists();

        if ($existe) {
            return back()->with('error', 'Une aide ' . strtoupper($request->type) . ' est déjà enregistrée pour ce souscripteur.');
        }

        $filePath = null;
        if ($request->hasFile('pieces_jointes')) {
            $filePath = $request->file('pieces_jointes')
                                ->store('aides_pj', 'public');
        }

        Aide::create([
            'souscripteur_id' => $request->souscripteur_id,
            'type'            => $request->type,
            'montant'         => $request->montant,
            'num_convention'  => $request->type === 'cnl' ? $request->num_convention : null,
            'num_decision'    => $request->num_decision,
            'date'            => $request->date,
            'pieces_jointes'  => $filePath,
            'user_id'         => Auth::id(),
        ]);

        return back()->with('success', 'Aide ' . strtoupper($request->type) . ' enregistrée avec succès.');
    }

    // =========================================================================
    // PDF (inchangé + compatible LPA/LSP)
    // =========================================================================

    public function generatePDF($id)
    {
        $ov = Ov::with('souscripteur.logement')->findOrFail($id);

        $Arabic   = new Arabic();
        $republique = $Arabic->utf8Glyphs("الجمهورية الجزائرية الديمقراطية الشعبية");
        $ministere  = $Arabic->utf8Glyphs("وزارة السكن والعمران والمدينة و التهيئة العمرانية");
        $agence     = $Arabic->utf8Glyphs("الوكالة الوطنية لتحسين السكن وتطويره");
        $logoAADL   = base64_encode(file_get_contents(public_path('images/AADL_logo.svg')));
        $algeria    = base64_encode(file_get_contents(public_path('images/algeria(1).svg')));
        $ordre      = $Arabic->utf8Glyphs("أمر بالدفع");
        $sous       = $Arabic->utf8Glyphs(" عزيزي المكتتب،");
        $document   = $Arabic->utf8Glyphs("هذه الوثيقة تشكل أمر دفع يمثل");
        $montant_total = $Arabic->utf8Glyphs("من المبلغ الإجمالي للسكن");
        $da         = $Arabic->utf8Glyphs("دج");
        $num_ordre  = $Arabic->utf8Glyphs("رقم الأمر :");
        $programme  = $Arabic->utf8Glyphs("في إطار برنامج السكن الترقوي المدعم.");
        $apayer     = $Arabic->utf8Glyphs("المبلغ الواجب دفعه :");
        $lettres    = $Arabic->utf8Glyphs("بالحروف :");
        $nom_ar     = $Arabic->utf8Glyphs("اللقب :");
        $prenom_ar  = $Arabic->utf8Glyphs("الاسم :");
        $date_n_ar  = $Arabic->utf8Glyphs("تاريخ الميلاد :");
        $code_loge_ar = $Arabic->utf8Glyphs("رمز السكن :");

        $montant_total_logement = $ov->souscripteur->logement->prix ?? 0;
        $textePrincipal = "تُشكّل هذه الوثيقة أمراً بالدفع يُمثّل {$ov->pourcentage}% من المبلغ الإجمالي للسكن " .
                          number_format($ov->montant_total, 2, ',', '.') .
                          " دج وذلك في إطار برنامج السكن الترقوي المدعم.";

        $texte_principal      = $textePrincipal;
        $montantEnLettres     = $this->montantEnLettres($ov->montant_paye);
        $montantEnLettresAr   = $this->montantEnLettresArabe($ov->montant_paye);
        $lettres_ar           = $Arabic->utf8Glyphs($montantEnLettresAr);
        $nomAr                = $Arabic->utf8Glyphs($ov->souscripteur->nom_arabe);
        $prenomAr             = $Arabic->utf8Glyphs($ov->souscripteur->prenom_arabe);

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
    // PAIEMENT (inchangé)
    // =========================================================================

    public function createPaiement($ovId)
    {
        $ov = Ov::with('souscripteur')->findOrFail($ovId);
        return view('paiementCreate', compact('ov'));
    }

    public function storePaiement(Request $request)
    {
        $request->validate([
            'ov_id'          => 'required|exists:ordres_versement,id',
            'num_recu'       => 'required|string|unique:paiements,num_recu',
            'date_paiement'  => 'required|date',
            'nom_agence'     => 'required|string',
            'num_agence'     => 'required|string',
            'pj.*'           => 'required|file|mimes:pdf',
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
                'ov_id'          => $request->ov_id,
                'num_recu'       => $request->num_recu,
                'nom_agence'     => $request->nom_agence,
                'num_agence'     => $request->num_agence,
                'date_paiement'  => $request->date_paiement,
                'recu_pdf'       => $filePath,
                'user_id'        => Auth::id(),
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

    /**
     * Déterminer le type de programme du souscripteur.
     * ⚠️  Adaptez selon votre structure réelle (logement->programme->code, etc.)
     */
 private function getProgrammeType(Souscripteur $souscripteur): string
{
    $prog = $souscripteur->logement->programme->libelle  // ← libelle pas code
         ?? 'LPL';

    return strtoupper(trim($prog));
}
    /**
     * Génère les données QR + SVG base64.
     * Retourne [plain, hashed, base64_svg]
     */
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
        $montant = floor($montant);
        if ($montant == 0) return "zéro dinar algérien";

        $unites  = ["","un","deux","trois","quatre","cinq","six","sept","huit","neuf","dix","onze","douze","treize","quatorze","quinze","seize","dix-sept","dix-huit","dix-neuf"];
        $dizaines = ["","","vingt","trente","quarante","cinquante","soixante","soixante","quatre-vingt","quatre-vingt"];

        return ucfirst($this->convertirNombreEnLettres($montant, $unites, $dizaines) . " Dinar Algérien");
    }

    private function convertirNombreEnLettres($nombre, $unites, $dizaines)
    {
        if ($nombre < 20) return $unites[$nombre];

        if ($nombre < 100) {
            $d = floor($nombre / 10); $u = $nombre % 10;
            $r = $dizaines[$d];
            if ($d == 7 || $d == 9)          $r  = $dizaines[$d] . "-" . $unites[10 + $u];
            elseif ($d == 8 && $u == 0)      $r .= "s";
            elseif ($u == 1 && $d > 1 && $d != 8) $r .= " et un";
            elseif ($u > 0)                  $r .= "-" . $unites[$u];
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
        $montant = floor($montant);
        if ($montant == 0) return "صفر دينار جزائري";

        $unites  = ["","واحد","اثنان","ثلاثة","أربعة","خمسة","ستة","سبعة","ثمانية","تسعة","عشرة","أحد عشر","اثنا عشر","ثلاثة عشر","أربعة عشر","خمسة عشر","ستة عشر","سبعة عشر","ثمانية عشر","تسعة عشر"];
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
            $c   = floor($nombre / 100); $r = $nombre % 100;
            $centaines = ["","مائة","مئتان","ثلاثمائة","أربعمائة","خمسمائة","ستمائة","سبعمائة","ثمانمائة","تسعمائة"];
            $res = $centaines[$c];
            if ($r > 0) $res .= " و" . $this->convertirNombreEnLettresArabe($r, $unites, $dizaines);
            return $res;
        }

        if ($nombre < 1000000) {
            $m  = floor($nombre / 1000); $r = $nombre % 1000;
            if      ($m == 1)              $res = "ألف";
            elseif  ($m == 2)              $res = "ألفان";
            elseif  ($m <= 10)             $res = $unites[$m] . " آلاف";
            else                           $res = $this->convertirNombreEnLettresArabe($m, $unites, $dizaines) . " ألف";
            if ($r > 0) $res .= " و" . $this->convertirNombreEnLettresArabe($r, $unites, $dizaines);
            return $res;
        }

        if ($nombre < 1000000000) {
            $m  = floor($nombre / 1000000); $r = $nombre % 1000000;
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