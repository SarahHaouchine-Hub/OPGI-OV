<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\Logement;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Wilaya;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $wilayas    = Wilaya::orderBy('nom')->get();
        $programmes = Programme::where('is_active', 1)->get();
        $sites      = Site::with('wilaya', 'programme')->orderBy('libelle')->get();

        // ── Statistiques globales logements ────────────────────────────────
        $totalLogements = Logement::count();
        $soldes         = Logement::where('flag', 2)->count();
        $inscrits       = Logement::where('flag', 1)->count();
        $libres         = Logement::where('flag', 0)->count();
        $remplaces      = Logement::where('flag', 3)->count();

        // ── Nouvelles statistiques BNH/OV/VSP ──────────────────────────────

        // 1. Souscripteurs ayant une décision BNH (type = 'bnh' dans table aides)
        $decisionBnh = \DB::table('souscripteurs')
            ->join('aides', 'souscripteurs.id', '=', 'aides.souscripteur_id')
            ->where('aides.type', 'bnh')
            ->distinct('souscripteurs.id')
            ->count('souscripteurs.id');

        // 2. OV Payées : ordres_versement ayant au moins 1 paiement
        $ovPayees = \DB::table('ordres_versement')
            ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('paiements')
                    ->whereColumn('paiements.ov_id', 'ordres_versement.id');
            })
            ->count();

        // 3. OV Non Payées : ordres_versement sans paiement
        $ovNonPayees = \DB::table('ordres_versement')
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('paiements')
                    ->whereColumn('paiements.ov_id', 'ordres_versement.id');
            })
            ->count();

        // 4. VSP par projet (en supposant qu'il existe une colonne is_vsp dans logements)
       // 4. VSP par projet — via ordres_versement.vsp → souscripteurs → logements → sites
$vspParProjet = \DB::table('sites')
    ->join('logements', 'logements.site_id', '=', 'sites.id')
    ->join('souscripteurs', 'souscripteurs.code_loge_lpl', '=', 'logements.code_loge_lpl')
    ->join('ordres_versement', function ($join) {
        $join->on('ordres_versement.souscripteur_id', '=', 'souscripteurs.id')
             ->whereNotNull('ordres_versement.vsp')
             ->where('ordres_versement.vsp', '!=', '');
    })
    ->groupBy('sites.id', 'sites.libelle')
    ->orderByDesc('vsp_count')
    ->get(\DB::raw('sites.id, sites.libelle, COUNT(DISTINCT ordres_versement.id) as vsp_count'))
    ->filter(fn($s) => $s->vsp_count > 0);

$totalVsp = \DB::table('ordres_versement')
    ->whereNotNull('vsp')
    ->where('vsp', '!=', '')
    ->count();

        // ── Filtres et pagination des sites ────────────────────────────────
        $sitesQuery = Site::with('wilaya', 'programme', 'logements')
            ->orderBy('libelle');

        if ($request->filled('wilaya_id')) {
            $sitesQuery->where('wilaya_id', $request->wilaya_id);
        }

        if ($request->filled('programme_id')) {
            $sitesQuery->where('programme_id', $request->programme_id);
        }

        if ($request->filled('search')) {
            $sitesQuery->where('libelle', 'like', '%' . $request->search . '%');
        }

        $sitesPaginated = $sitesQuery->paginate(15)->withQueryString();

        return view('dashboard', compact(
            'sitesPaginated',
            'totalLogements', 'soldes', 'inscrits', 'libres', 'remplaces',
            'decisionBnh', 'ovPayees', 'ovNonPayees', 'totalVsp', 'vspParProjet',
            'programmes', 'wilayas', 'sites'
        ));
    }

    // ── Endpoints API (inchangés) ───────────────────────────────────────

    public function programmesByWilaya($wilayaId)
    {
        $programmes = Programme::whereHas('sites', fn($q) => $q->where('wilaya_id', $wilayaId))
            ->distinct()->orderBy('libelle')->get(['id', 'libelle']);
        return response()->json($programmes);
    }

    public function sitesByWilayaProgramme($wilayaId, $programmeId)
    {
        $sites = Site::where('wilaya_id', $wilayaId)
            ->where('programme_id', $programmeId)
            ->orderBy('libelle')->get(['id', 'libelle']);
        return response()->json($sites);
    }

    public function batimentsBySite($siteId)
    {
        $batiments = Logement::where('site_id', $siteId)
            ->distinct()->orderBy('num_batiment')->pluck('num_batiment');
        return response()->json($batiments);
    }

    public function etagesBySiteBatiment($siteId, $batiment)
    {
        $etages = Logement::where('site_id', $siteId)
            ->where('num_batiment', $batiment)
            ->distinct()->orderBy('num_etage')->pluck('num_etage');
        return response()->json($etages);
    }

    public function sitesByWilaya($wilayaId)
    {
        $sites = Site::where('wilaya_id', $wilayaId)
            ->orderBy('libelle')->get(['id', 'libelle']);
        return response()->json($sites);
    }

    public function communes($wilayaId)
    {
        $communes = Commune::where('wilaya_id', $wilayaId)
            ->orderBy('nom')->get(['id', 'nom']);
        return response()->json($communes);
    }

    public function logementsBySite($siteId)
    {
        $site = Site::with('wilaya', 'programme')->findOrFail($siteId);

        $logements = Logement::with('souscripteur')
            ->where('site_id', $siteId)
            ->orderBy('num_batiment')
            ->orderBy('num_etage')
            ->orderBy('num_porte')
            ->get()
            ->map(function ($l) {
                return [
                    'id'           => $l->id,
                    'num_batiment' => $l->num_batiment,
                    'num_etage'    => $l->num_etage,
                    'num_porte'    => $l->num_porte,
                    'num_lot'      => $l->num_lot,
                    'typologie'    => $l->typologie,
                    'surface'      => $l->surface,
                    'prix'         => $l->prix,
                    'flag'         => $l->flag,
                    'souscripteur' => $l->souscripteur ? [
                        'id'             => $l->souscripteur->id,
                        'nom'            => $l->souscripteur->nom,
                        'prenom'         => $l->souscripteur->prenom,
                        'nom_arabe'      => $l->souscripteur->nom_arabe ?? null,
                        'prenom_arabe'   => $l->souscripteur->prenom_arabe ?? null,
                        'telephone'      => $l->souscripteur->telephone ?? null,
                        'email'          => $l->souscripteur->email ?? null,
                        'nin'            => $l->souscripteur->nin,
                        'date_naissance' => $l->souscripteur->date_naissance,
                        'adresse'        => $l->souscripteur->adresse ?? null,
                        'wilaya_nais'    => $l->souscripteur->wilaya_nais ?? null,
                        'situation_fam'  => $l->souscripteur->situation_familiale ?? null,
                        'profession'     => $l->souscripteur->profession ?? null,
                    ] : null,
                ];
            });

        return response()->json([
            'site'      => [
                'id'        => $site->id,
                'libelle'   => $site->libelle,
                'programme' => $site->programme->libelle ?? 'N/A',
                'wilaya'    => $site->wilaya->nom ?? 'N/A',
            ],
            'logements' => $logements,
        ]);
    }
}