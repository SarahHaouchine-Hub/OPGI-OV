<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    const ROLES = [
        'admin'                => 'Administrateur',
        'dg'                   => 'DG',
        'dga'                  => 'DGA',
        'chef_service_com'     => 'Chef de service Commercial',
        'charge_etude_lsp_lpa' => 'Chargé d\'étude LSP/LPA',
        'charge_etude_prom'    => 'Chargé d\'étude Promotionnel',
        'agent'                => 'Agent',
    ];

    public function index()
    {
        $users = User::get()->whereNotIn('id', [1]);
        $roles = self::ROLES;
        return view('users', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'in:' . implode(',', array_keys(self::ROLES))],
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'Utilisateur ajouté avec succès');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'in:' . implode(',', array_keys(self::ROLES))],
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Utilisateur modifié avec succès');
    }

    public function toggleStatus(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activé' : 'désactivé';

        return back()->with('status', "Le compte de l'utilisateur a été $status avec succès.");
    }
}