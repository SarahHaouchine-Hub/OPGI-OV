<?php

namespace App\Http\Controllers;

use App\Models\Logement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogementController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'site_id'      => 'required|exists:sites,id',
            'num_batiment' => 'required|string|max:10',
            'num_etage'    => 'required|integer|min:0',
            'num_porte'    => 'required|integer|min:1',
            'num_lot'      => 'nullable|string|max:50',
            'surface'      => 'nullable|numeric|min:0',
            'typologie'    => 'nullable|in:F2,F3,F4,F5,F6',
            'prix'         => 'required|numeric|min:0',
            'programme_id' => 'required|exists:programmes,id',
            'box_num'         => 'nullable|string|max:20',
'box_superficie'  => 'nullable|numeric|min:0',
'box_num_lot'     => 'nullable|string|max:50',
        ]);

        Logement::create([
            'site_id'      => $request->site_id,
            'num_batiment' => $request->num_batiment,
            'num_etage'    => $request->num_etage,
            'num_porte'    => $request->num_porte,
            'num_lot'      => $request->num_lot,
            'surface'      => $request->surface,
            'typologie'    => $request->typologie,
            'prix'         => $request->prix,
            'programme_id' => $request->programme_id,
            'flag'         => 0,
            'box_num'        => $request->box_num,
'box_superficie' => $request->box_superficie,
'box_num_lot'    => $request->box_num_lot,
            'user_id'      => Auth::id(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Logement ajouté avec succès.');
    }
}