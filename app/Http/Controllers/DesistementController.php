<?php

namespace App\Http\Controllers;

use App\Models\Desistement;
use App\Models\Logement;
use App\Models\Souscripteur;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesistementController extends Controller
{
    /**
     * Affiche la liste des logements (flag 1 ou 2) avec les filtres,
     * ainsi que la liste des désistements existants.
     */
    public function listLogements(Request $request)
    {
        // Requête de base pour les logements éligibles (flag 1 ou 2)
        $logementsQuery = Logement::query()
            ->with('souscripteur')
            ->whereIn('flag', [1, 2]);

        // Application des filtres sur les logements
        if ($request->filled('num_batiment')) {
            $logementsQuery->where('num_batiment', $request->num_batiment);
        }
        if ($request->filled('num_etage')) {
            $logementsQuery->where('num_etage', $request->num_etage);
        }
        if ($request->filled('num_porte')) {
            $logementsQuery->where('num_porte', $request->num_porte);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $logementsQuery->where('flag', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $logementsQuery->where('code_loge_lpl', 'like', "%{$search}%");
        }

        $logements = $logementsQuery->orderBy('created_at')->paginate(10);

        // Liste des sites pour le filtre principal
        $listSites = Site::orderBy('libelle')->get(['id', 'libelle']);

        // Construction des listes déroulantes (bâtiment, étage, porte) en fonction du site sélectionné
        // On commence par une requête de base sur Logement, éventuellement filtrée par site
        $filterQuery = Logement::query();
        if ($request->filled('site_id')) {
            $filterQuery->where('site_id', $request->site_id);
        }

        $listBatiments = (clone $filterQuery)->distinct()->pluck('num_batiment');

        $listEtages = [];
        if ($request->filled('num_batiment')) {
            $listEtages = (clone $filterQuery)
                ->where('num_batiment', $request->num_batiment)
                ->distinct()
                ->pluck('num_etage');
        }

        $listPortes = [];
        if ($request->filled('num_batiment') && $request->filled('num_etage')) {
            $listPortes = (clone $filterQuery)
                ->where('num_batiment', $request->num_batiment)
                ->where('num_etage', $request->num_etage)
                ->distinct()
                ->pluck('num_porte');
        }

        // Liste des désistements déjà enregistrés (affichée dans un tableau séparé)
        $desistements = Desistement::with('logement', 'souscripteur')
            ->orderBy('date_desistement', 'desc')
            ->paginate(10);

        return view('desistement', compact(
            'logements',
            'desistements',
            'listSites',
            'listBatiments',
            'listEtages',
            'listPortes'
        ));
    }

    /**
     * Enregistre le désistement d'un souscripteur pour un logement donné.
     */
    public function desistement($idLogement)
    {
        $logement = Logement::findOrFail($idLogement);

        // Récupérer le souscripteur lié à ce logement (via le code_loge_lpl)
        $souscripteur = Souscripteur::where('code_loge_lpl', $logement->code_loge_lpl)->first();

        if (!$souscripteur) {
            return redirect()->route('desistement')->withErrors([
                'error' => 'Aucun souscripteur associé à ce logement. Impossible d\'enregistrer le désistement.'
            ]);
        }

        $user = Auth::user();

        // Création de l'enregistrement de désistement
        Desistement::create([
            'souscripteur_id'   => $souscripteur->id,
            'logement_id'       => $logement->id,
            'code_loge_lpl'     => $logement->code_loge_lpl,
            'date_desistement'  => now(), // Helper Laravel (Carbon)
            'user_id'           => $user->id,
        ]);

        // Mise à jour du logement : flag à 3 (désisté) et dissociation du code logement
        $logement->update([
            'flag'           => '3',
            'code_loge_lpl'  => null,
        ]);

        // Mise à jour du souscripteur : marqué comme désisté
        $souscripteur->update([
            'desiste' => 1,
        ]);

        return redirect()->route('desistement')->with('success', 'Désistement effectué avec succès.');
    }
}