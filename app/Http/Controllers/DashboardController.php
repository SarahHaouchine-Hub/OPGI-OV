<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\Logement;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Wilaya;
use App\Traits\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use RoleAccess;

    public function index(Request $request)
    {
        $wilayas    = Wilaya::orderBy('nom')->get();
        $programmes = $this->getProgrammesForUser();
        $sites      = $this->getSitesQueryForUser()->with('wilaya', 'programme')->orderBy('libelle')->get();

        // ── Statistiques globales logements ────────────────────────────────
        // Pour les rôles restreints : on filtre les logements par programme
        $logementsQuery = $this->getLogementsQueryForUser();

        $totalLogements = (clone $logementsQuery)->count();
        $soldes         = (clone $logementsQuery)->where('flag', 2)->count();
        $inscrits       = (clone $logementsQuery)->where('flag', 1)->count();
        $libres         = (clone $logementsQuery)->where('flag', 0)->count();
// APRÈS
$desistes = (clone $logementsQuery)->where('flag', 3)->count();

        // ── Nouvelles statistiques BNH/OV/VSP ──────────────────────────────

        // 1. Souscripteurs ayant une décision BNH (type = 'bnh' dans table aides)
        $decisionBnh = \DB::table('souscripteurs')
            ->join('aides', 'souscripteurs.id', '=', 'aides.souscripteur_id')
            ->join('logements', 'logements.code_loge_lpl', '=', 'souscripteurs.code_loge_lpl')
            ->join('sites', 'sites.id', '=', 'logements.site_id')
            ->join('programmes', 'programmes.id', '=', 'sites.programme_id')
            ->where('aides.type', 'bnh')
            ->when($this->getAllowedProgrammes(), function ($q) {
                $allowed = $this->getAllowedProgrammes();
                $q->where(function ($inner) use ($allowed) {
                    foreach ($allowed as $key) {
                        $inner->orWhereRaw('UPPER(programmes.libelle) LIKE ?', ['%' . $key . '%']);
                    }
                });
            })
            ->distinct('souscripteurs.id')
            ->count('souscripteurs.id');

        // 2. OV Payées : ordres_versement ayant au moins 1 paiement
        $ovPayees = \DB::table('ordres_versement')
            ->join('souscripteurs', 'souscripteurs.id', '=', 'ordres_versement.souscripteur_id')
            ->join('logements', 'logements.code_loge_lpl', '=', 'souscripteurs.code_loge_lpl')
            ->join('sites', 'sites.id', '=', 'logements.site_id')
            ->join('programmes', 'programmes.id', '=', 'sites.programme_id')
            ->whereExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('paiements')
                    ->whereColumn('paiements.ov_id', 'ordres_versement.id');
            })
            ->when($this->getAllowedProgrammes(), function ($q) {
                $allowed = $this->getAllowedProgrammes();
                $q->where(function ($inner) use ($allowed) {
                    foreach ($allowed as $key) {
                        $inner->orWhereRaw('UPPER(programmes.libelle) LIKE ?', ['%' . $key . '%']);
                    }
                });
            })
            ->count();

        // 3. OV Non Payées : ordres_versement sans paiement
        $ovNonPayees = \DB::table('ordres_versement')
            ->join('souscripteurs', 'souscripteurs.id', '=', 'ordres_versement.souscripteur_id')
            ->join('logements', 'logements.code_loge_lpl', '=', 'souscripteurs.code_loge_lpl')
            ->join('sites', 'sites.id', '=', 'logements.site_id')
            ->join('programmes', 'programmes.id', '=', 'sites.programme_id')
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('paiements')
                    ->whereColumn('paiements.ov_id', 'ordres_versement.id');
            })
            ->when($this->getAllowedProgrammes(), function ($q) {
                $allowed = $this->getAllowedProgrammes();
                $q->where(function ($inner) use ($allowed) {
                    foreach ($allowed as $key) {
                        $inner->orWhereRaw('UPPER(programmes.libelle) LIKE ?', ['%' . $key . '%']);
                    }
                });
            })
            ->count();

        // 4. VSP par projet
        $vspQuery = \DB::table('sites')
            ->join('logements', 'logements.site_id', '=', 'sites.id')
            ->join('souscripteurs', 'souscripteurs.code_loge_lpl', '=', 'logements.code_loge_lpl')
            ->join('ordres_versement', function ($join) {
                $join->on('ordres_versement.souscripteur_id', '=', 'souscripteurs.id')
                     ->whereNotNull('ordres_versement.vsp')
                     ->where('ordres_versement.vsp', '!=', '');
            })
            ->join('programmes', 'programmes.id', '=', 'sites.programme_id');

        if ($allowed = $this->getAllowedProgrammes()) {
            $vspQuery->where(function ($q) use ($allowed) {
                foreach ($allowed as $key) {
                    $q->orWhereRaw('UPPER(programmes.libelle) LIKE ?', ['%' . $key . '%']);
                }
            });
        }

        $vspParProjet = $vspQuery
            ->groupBy('sites.id', 'sites.libelle')
            ->orderByDesc('vsp_count')
            ->get(\DB::raw('sites.id, sites.libelle, COUNT(DISTINCT ordres_versement.id) as vsp_count'))
            ->filter(fn($s) => $s->vsp_count > 0);

        $totalVspQuery = \DB::table('ordres_versement')
            ->join('souscripteurs', 'souscripteurs.id', '=', 'ordres_versement.souscripteur_id')
            ->join('logements', 'logements.code_loge_lpl', '=', 'souscripteurs.code_loge_lpl')
            ->join('sites', 'sites.id', '=', 'logements.site_id')
            ->join('programmes', 'programmes.id', '=', 'sites.programme_id')
            ->whereNotNull('ordres_versement.vsp')
            ->where('ordres_versement.vsp', '!=', '');

        if ($allowed = $this->getAllowedProgrammes()) {
            $totalVspQuery->where(function ($q) use ($allowed) {
                foreach ($allowed as $key) {
                    $q->orWhereRaw('UPPER(programmes.libelle) LIKE ?', ['%' . $key . '%']);
                }
            });
        }

        $totalVsp = $totalVspQuery->count();

        // ── Filtres et pagination des sites ────────────────────────────────
        $sitesQuery = $this->getSitesQueryForUser()
            ->with('wilaya', 'programme', 'logements')
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
// Nombre de remplacements effectués
$totalRemplacements = \DB::table('desistements')
    ->where('type', 'remplacement')
    ->when($this->getAllowedProgrammes(), function ($q) {
        $allowed = $this->getAllowedProgrammes();
        $q->whereExists(function ($inner) use ($allowed) {
            $inner->select(\DB::raw(1))
                ->from('logements')
                ->join('sites', 'sites.id', '=', 'logements.site_id')
                ->join('programmes', 'programmes.id', '=', 'sites.programme_id')
                ->whereColumn('logements.id', 'desistements.logement_id')
                ->where(function ($p) use ($allowed) {
                    foreach ($allowed as $key) {
                        $p->orWhereRaw('UPPER(programmes.libelle) LIKE ?', ['%' . $key . '%']);
                    }
                });
        });
    })
    ->count();

        $sitesPaginated = $sitesQuery->paginate(15)->withQueryString();

      return view('dashboard', compact(
    'sitesPaginated',
    'totalLogements', 'soldes', 'inscrits', 'libres', 'desistes', 'totalRemplacements',
    'decisionBnh', 'ovPayees', 'ovNonPayees', 'totalVsp', 'vspParProjet',
    'programmes', 'wilayas', 'sites'
));
    }

    // =========================================================================
    //  HELPERS PRIVÉS — Filtrage par rôle
    // =========================================================================

    /**
     * Retourne les programmes visibles par l'utilisateur connecté.
     */
    private function getProgrammesForUser()
    {
        $query = Programme::where('is_active', 1);
        $allowed = $this->getAllowedProgrammes();

        if ($allowed) {
            $query->where(function ($q) use ($allowed) {
                foreach ($allowed as $key) {
                    $q->orWhereRaw('UPPER(libelle) LIKE ?', ['%' . $key . '%']);
                }
            });
        }

        return $query->get();
    }

    /**
     * Retourne un builder Site filtré selon le rôle de l'utilisateur.
     */
    private function getSitesQueryForUser()
    {
        $query = Site::query();
        $allowed = $this->getAllowedProgrammes();

        if ($allowed) {
            $query->whereHas('programme', function ($q) use ($allowed) {
                $q->where(function ($inner) use ($allowed) {
                    foreach ($allowed as $key) {
                        $inner->orWhereRaw('UPPER(libelle) LIKE ?', ['%' . $key . '%']);
                    }
                });
            });
        }

        return $query;
    }

    /**
     * Retourne un builder Logement filtré selon le rôle de l'utilisateur.
     */
    private function getLogementsQueryForUser()
    {
        $query = Logement::query();
        $allowed = $this->getAllowedProgrammes();

        if ($allowed) {
            $query->whereHas('site.programme', function ($q) use ($allowed) {
                $q->where(function ($inner) use ($allowed) {
                    foreach ($allowed as $key) {
                        $inner->orWhereRaw('UPPER(libelle) LIKE ?', ['%' . $key . '%']);
                    }
                });
            });
        }

        return $query;
    }

    // =========================================================================
    //  Endpoints API
    // =========================================================================

    public function programmesByWilaya($wilayaId)
    {
        $query = Programme::whereHas('sites', fn($q) => $q->where('wilaya_id', $wilayaId))
            ->distinct()
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

    public function sitesByWilayaProgramme($wilayaId, $programmeId)
    {
        $sites = Site::where('wilaya_id', $wilayaId)
            ->where('programme_id', $programmeId)
            ->orderBy('libelle')
            ->get(['id', 'libelle']);

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
        // Vérifier si le rôle a accès à ce site
        $site = Site::with('wilaya', 'programme')->findOrFail($siteId);
        if (!$this->canAccessProgramme($site->programme->libelle ?? '')) {
            return response()->json(['error' => $this->accessDeniedMessage()], 403);
        }

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
                    'box_num'        => $l->box_num,
'box_superficie' => $l->box_superficie,
'box_num_lot'    => $l->box_num_lot,
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