<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Liste des tables à convertir
        $tables = ['users', 'logements', 'souscripteurs', 'ordres_versement', 'paiements', 'desistements']; 

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE $table ENGINE = InnoDB");
            }
        }
    }

    public function down(): void
    {
        // Optionnel : revenir en MyISAM si besoin
        $tables = ['users', 'logements', 'souscripteurs', 'ordres_versement', 'paiements', 'desistements'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE $table ENGINE = MyISAM");
            }
        }
    }
};