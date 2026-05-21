<?php

namespace App\Http\Controllers;

use App\Imports\LplImport;
use App\Imports\LspImport;
use App\Imports\LpaImport;
use App\Models\Logement;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Souscripteur;
use App\Models\Wilaya;
use App\Traits\RoleAccess;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Vinkla\Hashids\Facades\Hashids;

class SouscripteurController extends Controller
{
    use RoleAccess;

    // ─────────────────────────────────────────────────────────────────────────
    //  Formulaire d'inscription
    // ─────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $wilayas = Wilaya::orderBy('nom')->get();

        // Liste des programmes autorisés pour ce rôle (null = accès total)
        // On normalise en MAJUSCULE pour la comparaison côté blade
        $allowedProgrammes = $this->getAllowedProgrammes(); // null | string[]

        return view('ajouterSouscripteur', compact('wilayas', 'allowedProgrammes'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  API 1 — Programmes actifs ayant des sites dans une wilaya donnée
    //  Filtrés selon le rôle de l'utilisateur connecté
    //  GET /api/souscripteur/programmes-by-wilaya/{wilayaId}
    // ─────────────────────────────────────────────────────────────────────────
    public function programmesByWilaya($wilayaId)
    {
        $query = Programme::where('is_active', 1)
            ->whereHas('sites', fn($q) => $q->where('wilaya_id', $wilayaId))
            ->orderBy('libelle');

        $allowed = $this->getAllowedProgrammes();
        if ($allowed) {
            $query->where(function ($q) use ($allowed) {
                foreach ($allowed as $key) {
                    $q->orWhereRaw('UPPER(libelle) LIKE ?', ['%' . $key . '%']);
                }
            });
        }

        return response()->json($query->get(['id', 'libelle']));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  API 2 — Sites pour une wilaya + programme donnés
    //  GET /api/souscripteur/sites/{wilayaId}/{programmeId}
    // ─────────────────────────────────────────────────────────────────────────
    public function sitesByWilayaProgramme($wilayaId, $programmeId)
    {
        $programme = Programme::find($programmeId);
        if ($programme && !$this->canAccessProgramme($programme->libelle ?? '')) {
            return response()->json([]);
        }

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
        if (!$this->siteIsAccessible($siteId)) {
            return response()->json([]);
        }

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
        if (!$this->siteIsAccessible($siteId)) {
            return response()->json([]);
        }

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
        if (!$this->siteIsAccessible($siteId)) {
            return response()->json([]);
        }

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
            'nom'                     => 'required|string|max:255',
            'prenom'                  => 'required|string|max:255',
            'date_naissance'          => 'required|date',
            'nin'                     => 'required|string|max:18|unique:souscripteurs,nin',
            'situation_familiale'     => 'required|in:celibataire,marie,divorce,veuf',
            'lieu_naissance'          => 'nullable|string|max:255',
            'nom_pere'                => 'nullable|string|max:255',
            'prenom_pere'             => 'nullable|string|max:255',
            'nom_mere'                => 'nullable|string|max:255',
            'prenom_mere'             => 'nullable|string|max:255',
            'conjoint_nom'            => 'required_if:situation_familiale,marie|nullable|string|max:255',
            'conjoint_prenom'         => 'required_if:situation_familiale,marie|nullable|string|max:255',
            'conjoint_nin'            => 'required_if:situation_familiale,marie|nullable|string|max:18',
            'conjoint_date_naissance' => 'required_if:situation_familiale,marie|nullable|date',
            'conjoint_lieu_naissance' => 'nullable|string|max:255',
            'conjoint_nom_pere'       => 'nullable|string|max:255',
            'conjoint_prenom_pere'    => 'nullable|string|max:255',
            'conjoint_nom_mere'       => 'nullable|string|max:255',
            'conjoint_prenom_mere'    => 'nullable|string|max:255',
            'wilaya_id'               => 'required|exists:wilayas,id',
            'programme_id'            => 'required|exists:programmes,id',
            'site_id'                 => 'required|exists:sites,id',
            'logement_id'             => 'required|exists:logements,id',
        ]);

        $programme = Programme::find($request->programme_id);
        if ($programme && !$this->canAccessProgramme($programme->libelle ?? '')) {
            return back()->with('error', $this->accessDeniedMessage())->withInput();
        }

        DB::beginTransaction();
        try {
            $logement = Logement::with(['site.programme', 'site.wilaya', 'programme'])
                ->findOrFail($request->logement_id);

            if (!in_array($logement->flag, [0, 3])) {
                return back()->withErrors('Ce logement n\'est plus disponible.')->withInput();
            }

            do {
                $random  = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $bat     = str_pad((string) $logement->num_batiment, 2, '0', STR_PAD_LEFT);
                $porte   = str_pad((string) $logement->num_porte,    2, '0', STR_PAD_LEFT);
                $codeLPL = "B{$bat}N{$porte}{$random}";
            } while (Logement::where('code_loge_lpl', $codeLPL)->exists());

            $qrDataPlain = implode(' | ', [
                'OPGI',
                'Nom: '       . strtoupper($request->nom),
                'Prénom: '    . $request->prenom,
                'Programme: ' . ($logement->programme->libelle ?? ''),
                'Site: '      . ($logement->site->libelle ?? ''),
                'Code: '      . $codeLPL,
            ]);
            $qrDataHashed = hash('sha256', $qrDataPlain);
            $qrcodeData   = base64_encode(QrCode::size(200)->margin(1)->generate($qrDataHashed));

            $logement->update(['code_loge_lpl' => $codeLPL, 'flag' => 1]);

            $souscripteur = Souscripteur::create([
                'nom'                     => $request->nom,
                'prenom'                  => $request->prenom,
                'date_naissance'          => $request->date_naissance,
                'nin'                     => $request->nin,
                'situation_familiale'     => $request->situation_familiale,
                'lieu_naissance'          => $request->lieu_naissance,
                'nom_pere'                => $request->nom_pere,
                'prenom_pere'             => $request->prenom_pere,
                'nom_mere'                => $request->nom_mere,
                'prenom_mere'             => $request->prenom_mere,
                'conjoint_nom'            => $request->conjoint_nom,
                'conjoint_prenom'         => $request->conjoint_prenom,
                'conjoint_nin'            => $request->conjoint_nin,
                'conjoint_date_naissance' => $request->conjoint_date_naissance,
                'conjoint_lieu_naissance' => $request->conjoint_lieu_naissance,
                'conjoint_nom_pere'       => $request->conjoint_nom_pere,
                'conjoint_prenom_pere'    => $request->conjoint_prenom_pere,
                'conjoint_nom_mere'       => $request->conjoint_nom_mere,
                'conjoint_prenom_mere'    => $request->conjoint_prenom_mere,
                'code_loge_lpl'           => $codeLPL,
                'qr_content_plain'        => $qrDataPlain,
                'qr_content_hashed'       => $qrDataHashed,
                'qrcode'                  => $qrcodeData,
                'user_id'                 => Auth::id(),
            ]);

            DB::commit();

            $hashedId = Hashids::encode($souscripteur->id);
            return redirect()->route('souscripteur.create')
                ->with('fiche_url', route('souscripteur.fiche', ['id' => $hashedId]))
                ->with('success', 'Souscripteur affecté avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Erreur : ' . $e->getMessage())->withInput();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Génération de la fiche PDF
    // ─────────────────────────────────────────────────────────────────────────
    public function generateFiche($id)
    {
        $souscripteur = Souscripteur::findOrFail($id);

        $logement = Logement::with([
            'site.programme',
            'site.wilaya',
            'programme',
        ])->where('code_loge_lpl', $souscripteur->code_loge_lpl)->firstOrFail();

        $Arabic     = new Arabic();
        $republique = $Arabic->utf8Glyphs('الجمهورية الجزائرية الديمقراطية الشعبية');
        $ministere  = $Arabic->utf8Glyphs('وزارة السكن والعمران والمدينة و التهيئة العمرانية');
        $agence     = $Arabic->utf8Glyphs('ديوان الترقية والتسيير العقاري');
        $comm       = $Arabic->utf8Glyphs('الدار البيضاء');

        $logoOPGI = base64_encode(file_get_contents(public_path('images/OPGI.jpg')));
        $algeria  = base64_encode(file_get_contents(public_path('images/algeria.jpg')));

        $pdf = Pdf::loadView('pdf.fiche_inscription', compact(
            'souscripteur', 'logement', 'ministere', 'republique',
            'agence', 'logoOPGI', 'comm', 'algeria'
        ));

        return $pdf->stream('Fiche_Inscription.pdf');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Import LPL Promotionnel
    // ─────────────────────────────────────────────────────────────────────────
    public function importLpl(Request $request)
    {
        if (!$this->canAccessProgramme('LPL')) {
            return redirect()->route('souscripteur.create')
                ->with('error', $this->accessDeniedMessage());
        }

        $request->validate([
            'excel_file_lpl' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new LplImport();
        Excel::import($import, $request->file('excel_file_lpl'));

        return $this->buildImportRedirect($import, 'LPL Promotionnel');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Import LSP
    // ─────────────────────────────────────────────────────────────────────────
    public function importLsp(Request $request)
    {
        if (!$this->canAccessProgramme('LSP')) {
            return redirect()->route('souscripteur.create')
                ->with('error', $this->accessDeniedMessage());
        }

        $request->validate([
            'excel_file_lsp' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new LspImport();
        Excel::import($import, $request->file('excel_file_lsp'));

        return $this->buildImportRedirect($import, 'LSP');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Import LPA
    // ─────────────────────────────────────────────────────────────────────────
    public function importLpa(Request $request)
    {
        if (!$this->canAccessProgramme('LPA')) {
            return redirect()->route('souscripteur.create')
                ->with('error', $this->accessDeniedMessage());
        }

        $request->validate([
            'excel_file_lpa' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new LpaImport();
        Excel::import($import, $request->file('excel_file_lpa'));

        return $this->buildImportRedirect($import, 'LPA');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helper commun — Import
    // ─────────────────────────────────────────────────────────────────────────
    private function buildImportRedirect($import, string $programmeName)
    {
        $successCount = $import->imported;
        $errors       = $import->errors;
        $msg          = "{$successCount} souscripteur(s) {$programmeName} importé(s) avec succès.";

        if (!empty($errors)) {
            $msg .= " — " . count($errors) . " erreur(s) détectée(s).";
            return redirect()->route('souscripteur.create')
                ->with('success', $msg)
                ->with('import_errors', $errors);
        }

        return redirect()->route('souscripteur.create')->with('success', $msg);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helper — Vérifie si le site est accessible par le rôle courant
    // ─────────────────────────────────────────────────────────────────────────
    private function siteIsAccessible(int $siteId): bool
    {
        $allowed = $this->getAllowedProgrammes();
        if ($allowed === null) {
            return true;
        }

        return Site::where('id', $siteId)
            ->whereHas('programme', function ($q) use ($allowed) {
                $q->where(function ($inner) use ($allowed) {
                    foreach ($allowed as $key) {
                        $inner->orWhereRaw('UPPER(libelle) LIKE ?', ['%' . $key . '%']);
                    }
                });
            })
            ->exists();
    }
}