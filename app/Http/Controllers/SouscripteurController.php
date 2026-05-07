<?php

namespace App\Http\Controllers;

use ArPHP\I18N\Arabic;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Logement;
use App\Models\Souscripteur;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Wilaya;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Imports\SouscripteursImport;
use Maatwebsite\Excel\Facades\Excel;

class SouscripteurController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    //  Formulaire d'inscription
    // ─────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $wilayas = Wilaya::orderBy('nom')->get();

        return view('ajouterSouscripteur', compact('wilayas'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  API 1 — Programmes actifs ayant des sites dans une wilaya donnée
    //  GET /api/souscripteur/programmes-by-wilaya/{wilayaId}
    // ─────────────────────────────────────────────────────────────────────────
    public function programmesByWilaya($wilayaId)
    {
        $programmes = Programme::where('is_active', 1)
            ->whereHas('sites', fn($q) => $q->where('wilaya_id', $wilayaId))
            ->orderBy('libelle')
            ->get(['id', 'libelle']);

        return response()->json($programmes);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  API 2 — Sites pour une wilaya + programme donnés
    //  GET /api/souscripteur/sites/{wilayaId}/{programmeId}
    // ─────────────────────────────────────────────────────────────────────────
    public function sitesByWilayaProgramme($wilayaId, $programmeId)
    {
        $sites = Site::where('wilaya_id', $wilayaId)
            ->where('programme_id', $programmeId)
            ->orderBy('libelle')
            ->get(['id', 'libelle']);

        return response()->json($sites);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  API 3 — Bâtiments disponibles (libres/désistés) pour un site
    //  GET /api/souscripteur/batiments/{siteId}
    // ─────────────────────────────────────────────────────────────────────────
    public function batimentsBySite($siteId)
    {
        $batiments = Logement::where('site_id', $siteId)
            ->whereIn('flag', [0, 3])
            ->select('num_batiment')
            ->distinct()
            ->orderBy('num_batiment')
            ->pluck('num_batiment');

        return response()->json($batiments);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  API 4 — Étages disponibles pour un site + bâtiment
    //  GET /api/souscripteur/etages/{siteId}/{batiment}
    // ─────────────────────────────────────────────────────────────────────────
    public function etagesBySiteBat($siteId, $batiment)
    {
        $etages = Logement::where('site_id', $siteId)
            ->where('num_batiment', $batiment)
            ->whereIn('flag', [0, 3])
            ->select('num_etage')
            ->distinct()
            ->orderBy('num_etage')
            ->pluck('num_etage');

        return response()->json($etages);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  API 5 — Portes disponibles pour un site + bâtiment + étage
    //  GET /api/souscripteur/portes/{siteId}/{batiment}/{etage}
    // ─────────────────────────────────────────────────────────────────────────
    public function portesBySiteBatEtage($siteId, $batiment, $etage)
    {
        $portes = Logement::where('site_id', $siteId)
            ->where('num_batiment', $batiment)
            ->where('num_etage', $etage)
            ->whereIn('flag', [0, 3])
            ->select('id', 'num_porte')
            ->orderBy('num_porte')
            ->get();

        return response()->json($portes);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Enregistrement du souscripteur
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
      $request->validate([
    'nom'            => 'required|string|max:255',
    'prenom'         => 'required|string|max:255',
    'nom_ar'         => 'required|string|max:255',
    'prenom_ar'      => 'required|string|max:255',
    'date_naissance' => 'required|date',
    'nin'            => 'required|string|max:18|unique:souscripteurs,nin', // ← ajout
    'wilaya_id'      => 'required|exists:wilayas,id',
    'programme_id'   => 'required|exists:programmes,id',
    'site_id'        => 'required|exists:sites,id',
    'logement_id'    => 'required|exists:logements,id',
]);

        DB::beginTransaction();

        try {
            // Charger le logement avec ses relations
            $logement = Logement::with(['site.programme', 'site.wilaya', 'programme'])
                ->findOrFail($request->logement_id);

            if (!in_array($logement->flag, [0, 3])) {
                return back()
                    ->withErrors('Ce logement n\'est plus disponible.')
                    ->withInput();
            }

            // ── Génération du code unique ──────────────────────────────
            do {
                $random  = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $bat     = str_pad((string) $logement->num_batiment, 2, '0', STR_PAD_LEFT);
                $porte   = str_pad((string) $logement->num_porte,    2, '0', STR_PAD_LEFT);
                $codeLPL = "B{$bat}N{$porte}{$random}";
            } while (Logement::where('code_loge_lpl', $codeLPL)->exists());

            // ── Données QR ────────────────────────────────────────────
            $qrDataPlain  = implode(' | ', [
                'AADL',
                'Nom: '      . strtoupper($request->nom),
                'Prénom: '   . $request->prenom,
                'Programme: '. ($logement->programme->libelle ?? ''),
                'Site: '     . ($logement->site->libelle ?? ''),
                'Code: '     . $codeLPL,
            ]);
            $qrDataHashed = hash('sha256', $qrDataPlain);

            // QrCode::generate() retourne du SVG — on le stocke en base64
            $qrcodeData = base64_encode(
                QrCode::size(200)->margin(1)->generate($qrDataHashed)
            );

            // ── Mise à jour du logement ───────────────────────────────
            $logement->update([
                'code_loge_lpl' => $codeLPL,
                'flag'          => 1,
            ]);

            // ── Création du souscripteur ──────────────────────────────
    $souscripteur = Souscripteur::create([
    'nom'               => $request->nom,
    'prenom'            => $request->prenom,
    'nom_arabe'         => $request->nom_ar,
    'prenom_arabe'      => $request->prenom_ar,
    'date_naissance'    => $request->date_naissance,
    'nin'               => $request->nin,              // ← ajout
    'code_loge_lpl'     => $codeLPL,
    'qr_content_plain'  => $qrDataPlain,
    'qr_content_hashed' => $qrDataHashed,
    'qrcode'            => $qrcodeData,
    'user_id'           => Auth::id(),
]);

            DB::commit();

            $hashedId = Hashids::encode($souscripteur->id);

            return redirect()->route('souscripteur.create')
                ->with('fiche_url', route('souscripteur.fiche', ['id' => $hashedId]))
                ->with('success', 'Souscripteur affecté avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors('Erreur : ' . $e->getMessage())
                ->withInput();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Génération de la fiche PDF
    // ─────────────────────────────────────────────────────────────────────────
    public function generateFiche($id)
    {
        $souscripteur = Souscripteur::findOrFail($id);

        // Charger le logement avec TOUTES les relations nécessaires à la fiche
        $logement = Logement::with([
                'site.programme',   // → $logement->site->programme->libelle
                'site.wilaya',      // → $logement->site->wilaya->nom
                'programme',        // → $logement->programme->libelle (relation directe)
            ])
            ->where('code_loge_lpl', $souscripteur->code_loge_lpl)
            ->firstOrFail();

        // ── Textes arabes via ArPHP ───────────────────────────────────
        $Arabic     = new Arabic();
        $republique = $Arabic->utf8Glyphs('الجمهورية الجزائرية الديمقراطية الشعبية');
        $ministere  = $Arabic->utf8Glyphs('وزارة السكن والعمران والمدينة و التهيئة العمرانية');
        $agence     = $Arabic->utf8Glyphs('الوكالة الوطنية لتحسين السكن وتطويره');

        // ── Images en base64 ─────────────────────────────────────────
        $logoAADL = base64_encode(file_get_contents(public_path('images/AADL_logo.jpg')));
        $algeria  = base64_encode(file_get_contents(public_path('images/algeria.jpg')));

        $pdf = Pdf::loadView('pdf.fiche_inscription', compact(
            'souscripteur',
            'logement',
            'ministere',
            'republique',
            'agence',
            'logoAADL',
            'algeria'
        ));

        return $pdf->stream('Fiche_Inscription.pdf');
    }
   public function import(Request $request)
{
    $request->validate([
        'excel_file' => 'required|file|mimes:xlsx,xls,csv'
    ]);

    $import = new SouscripteursImport();
    Excel::import($import, $request->file('excel_file'));

    $msg = "{$import->imported} souscripteur(s) importé(s) avec succès.";

    if (!empty($import->errors)) {
        $msg .= " Erreurs : " . implode(' | ', $import->errors);
        return redirect()->route('souscripteur.create')
            ->with('success', $msg)
            ->withErrors($import->errors);
    }

    return redirect()->route('souscripteur.create')->with('success', $msg);
}

}