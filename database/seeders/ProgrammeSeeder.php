<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Programme;  // 👈 ajouter cette ligne

class ProgrammeSeeder extends Seeder
{
    public function run(): void
    {
        Programme::insert([
            ['libelle' => 'LPL Promotionnel', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['libelle' => 'LSP',              'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['libelle' => 'LPA',              'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}