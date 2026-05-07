<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // On crée l'admin avec l'ID 1
        User::create([
            'id' => 1,
            'name' => 'Administrateur',
            'email' => 'admin@aadl.dz',
            'password' => Hash::make(123456789), // Changez-le !
            'role' => 'admin', // Assurez-vous que la colonne role existe
        ]);
    }
}