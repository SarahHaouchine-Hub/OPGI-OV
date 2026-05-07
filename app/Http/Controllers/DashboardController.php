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
    /**
     * Affiche le tableau de bord.
     * - Par défaut : liste des sites avec Programme, Wilaya, Site.
     * - Avec ?view_site_id=X : liste des logements du site X.
     */
    public function index(Request $request)
    {
        $wilayas    = Wilaya::orderBy('nom')->get();
        $programmes = Programme::where('is_active', 1)->get();
        $sites      = Site::with('wilaya', 'programme')->orderBy('libelle')->get();

        // ── Statistiques globales ──────────────────────────────────────────
        $totalLogements = Logement::count();
        $vendus         = Logement::where('flag', 2)->count();
        $inscrits       = Logement::where('flag', 1)->count();
        $libres         = Logement::where('flag', 0)->count();
        $desistes       = Logement::where('flag', 3)->count();

        // ── Mode par défaut : liste des sites ──────────────────────────────
        $sitesQuery = Site::with('wilaya', 'programme', 'logements')
            ->orderBy('libelle');

        // Filtre wilaya
        if ($request->filled('wilaya_id')) {
            $sitesQuery->where('wilaya_id', $request->wilaya_id);
        }

        // Filtre programme
        if ($request->filled('programme_id')) {
            $sitesQuery->where('programme_id', $request->programme_id);
        }

        // Recherche libellé site
        if ($request->filled('search')) {
            $sitesQuery->where('libelle', 'like', '%' . $request->search . '%');
        }

        $sitesPaginated = $sitesQuery->paginate(15)->withQueryString();

        return view('dashboard', compact(
            'sitesPaginated',
            'totalLogements', 'vendus', 'inscrits', 'libres', 'desistes',
            'programmes', 'wilayas', 'sites'
        ));
    }

    // ── Endpoints API pour cascades ────────────────────────────────────────

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

    /**
     * API — Retourne tous les logements d'un site avec le souscripteur lié.
     */
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
                        'nom_arabe'      => $l->souscripteur->nom_arabe,
                        'prenom_arabe'   => $l->souscripteur->prenom_arabe,
                        'telephone'      => $l->souscripteur->telephone,
                        'email'          => $l->souscripteur->email,
                        'nin'            => $l->souscripteur->nin,
                        'date_naissance' => $l->souscripteur->date_naissance,
                        'adresse'        => $l->souscripteur->adresse,
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