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
            'libelle'      => 'required|string|max:255',
            'wilaya_id'    => 'required|exists:wilayas,id',
            'commune_id'   => 'required|exists:communes,id',
            'programme_id' => 'required|exists:programmes,id',
        ]);

        Site::create([
            'libelle'      => $request->libelle,
            'wilaya_id'    => $request->wilaya_id,
            'commune_id'   => $request->commune_id,
            'programme_id' => $request->programme_id,
            'user_id'      => Auth::id(),
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