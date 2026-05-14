<?php

namespace App\Http\Controllers;

use App\Models\Desistement;
use App\Models\Logement;
use App\Models\Souscripteur;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DesistementController extends Controller
{
    /**
     * Affiche la liste des logements (flag 1 ou 2) avec les filtres,
     * ainsi que la liste des désistements existants.
     */
    public function listLogements(Request $request)
    {
        $logementsQuery = Logement::query()
            ->with('souscripteur')
            ->whereIn('flag', [1, 2]);

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

        $listSites = Site::orderBy('libelle')->get(['id', 'libelle']);

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

        $desistements = Desistement::with('logement', 'souscripteur', 'nouveauSouscripteur')
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
     * Enregistre le désistement simple.
     */
   

    /**
     * API — Recherche un souscripteur par NIN.
     * GET /api/souscripteur/search-nin/{nin}
     */
    public function searchByNin($nin)
    {
        $souscripteur = Souscripteur::where('nin', $nin)->first();

        if (!$souscripteur) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'                   => true,
            'id'                      => $souscripteur->id,
            'nom'                     => $souscripteur->nom,
            'prenom'                  => $souscripteur->prenom,
            'date_naissance'          => $souscripteur->date_naissance,
            'lieu_naissance'          => $souscripteur->lieu_naissance,
            'situation_familiale'     => $souscripteur->situation_familiale,
            'nom_pere'                => $souscripteur->nom_pere,
            'prenom_pere'             => $souscripteur->prenom_pere,
            'nom_mere'                => $souscripteur->nom_mere,
            'prenom_mere'             => $souscripteur->prenom_mere,
            'conjoint_nom'            => $souscripteur->conjoint_nom,
            'conjoint_prenom'         => $souscripteur->conjoint_prenom,
            'conjoint_nin'            => $souscripteur->conjoint_nin,
            'conjoint_date_naissance' => $souscripteur->conjoint_date_naissance,
            'conjoint_lieu_naissance' => $souscripteur->conjoint_lieu_naissance,
            'conjoint_nom_pere'       => $souscripteur->conjoint_nom_pere,
            'conjoint_prenom_pere'    => $souscripteur->conjoint_prenom_pere,
            'conjoint_nom_mere'       => $souscripteur->conjoint_nom_mere,
            'conjoint_prenom_mere'    => $souscripteur->conjoint_prenom_mere,
        ]);
    }

    /**
     * Remplace l'ancien souscripteur par un nouveau sur un logement donné.
     * POST /desistement/{idLogement}/remplacer
     */
    public function remplacer(Request $request, $idLogement)
{
    $request->validate([
        'nin'                     => 'required|string|max:18',
        'nom'                     => 'required|string|max:255',
        'prenom'                  => 'required|string|max:255',
        'date_naissance'          => 'required|date',
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
    ]);

    DB::beginTransaction();

    try {
        // ── Charger le logement avec site uniquement ──────────────────
       // ✅ Après — charge aussi programme via site
$logement = Logement::with(['site.programme'])
    ->findOrFail($idLogement);

        // ── Ancien souscripteur via code_loge_lpl ─────────────────────
        $ancienSouscripteur = Souscripteur::where('code_loge_lpl', $logement->code_loge_lpl)
            ->first();

        if (!$ancienSouscripteur) {
            DB::rollBack();
            return redirect()->route('desistement')
                ->withErrors(['error' => 'Aucun souscripteur associé à ce logement.']);
        }

        // ── Vérifier que le nouveau NIN ≠ ancien NIN ──────────────────
        if ($ancienSouscripteur->nin === $request->nin) {
            DB::rollBack();
            return redirect()->route('desistement')
                ->withErrors(['error' => 'Le nouveau souscripteur est identique à l\'ancien.']);
        }

        // ── Génération du nouveau code logement ───────────────────────
        do {
            $random  = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $bat     = str_pad((string) $logement->num_batiment, 2, '0', STR_PAD_LEFT);
            $porte   = str_pad((string) $logement->num_porte,    2, '0', STR_PAD_LEFT);
            $codeLPL = "B{$bat}N{$porte}{$random}";
        } while (Logement::where('code_loge_lpl', $codeLPL)->exists());

        // ── Programme via site ────────────────────────────────────────
        $programmeLibelle = $logement->site->programme->libelle
                            ?? $logement->site->libelle
                            ?? '';
        $siteLibelle      = $logement->site->libelle ?? '';

        // ── QR Code ───────────────────────────────────────────────────
        $qrDataPlain = implode(' | ', [
            'OPGI',
            'Nom: '       . strtoupper($request->nom),
            'Prénom: '    . $request->prenom,
            'Programme: ' . $programmeLibelle,
            'Site: '      . $siteLibelle,
            'Code: '      . $codeLPL,
        ]);
        $qrDataHashed = hash('sha256', $qrDataPlain);
        $qrcodeData   = base64_encode(
            QrCode::size(200)->margin(1)->generate($qrDataHashed)
        );

        // ── Nouveau souscripteur : existant ou création ───────────────
        $nouveauSouscripteur = Souscripteur::where('nin', $request->nin)->first();

        if ($nouveauSouscripteur) {
            $nouveauSouscripteur->update([
                'code_loge_lpl'     => $codeLPL,
                'qr_content_plain'  => $qrDataPlain,
                'qr_content_hashed' => $qrDataHashed,
                'qrcode'            => $qrcodeData,
                'desiste'           => 0,
            ]);
        } else {
            $nouveauSouscripteur = Souscripteur::create([
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
                'desiste'                 => 0,
            ]);
        }

        // ── Tracer dans desistements ──────────────────────────────────
        Desistement::create([
            'souscripteur_id'         => $ancienSouscripteur->id,
            'logement_id'             => $logement->id,
            'code_loge_lpl'           => $logement->code_loge_lpl,
            'date_desistement'        => now(),
            'user_id'                 => Auth::id(),
            'type'                    => 'remplacement',
            'nouveau_souscripteur_id' => $nouveauSouscripteur->id,
        ]);

        // ── Marquer l'ancien comme désisté ────────────────────────────
        $ancienSouscripteur->update([
            'desiste'       => 1,
            'code_loge_lpl' => null,
        ]);

        // ── Mettre à jour le logement ─────────────────────────────────
        $logement->update([
            'flag'          => 1,
            'code_loge_lpl' => $codeLPL,
        ]);

        DB::commit();

        return redirect()->route('desistement')
            ->with('success', 'Remplacement effectué avec succès.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('desistement')
            ->withErrors(['error' => 'Erreur : ' . $e->getMessage()]);
    }
}
}