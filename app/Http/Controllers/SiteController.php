<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Wilaya;
use App\Models\Commune;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
   public function store(Request $request)
{
    $request->validate([
        'libelle'            => 'required|string|max:255',
        'wilaya_id'          => 'required|exists:wilayas,id',
        'commune_id'         => 'required|exists:communes,id',
        'programme_id'       => 'required|exists:programmes,id',
        'num_convention_bnh' => 'nullable|string|max:255',
        'nom_agence'         => 'nullable|string|max:255',
        'num_agence'         => 'nullable|string|max:255',
    ]);

    Site::create([
        'libelle'            => $request->libelle,
        'wilaya_id'          => $request->wilaya_id,
        'commune_id'         => $request->commune_id,
        'programme_id'       => $request->programme_id,
        'num_convention_bnh' => $request->num_convention_bnh,
        'nom_agence'         => $request->nom_agence,
        'num_agence'         => $request->num_agence,
        'user_id'            => Auth::id(),
    ]);

    return redirect()->route('dashboard')->with('success', 'Site ajouté avec succès.');
}
    public function sitesByWilaya($wilayaId)
{
    $sites = Site::where('wilaya_id', $wilayaId)
        ->orderBy('libelle')
        ->get(['id', 'libelle']);

    return response()->json($sites);
}
}