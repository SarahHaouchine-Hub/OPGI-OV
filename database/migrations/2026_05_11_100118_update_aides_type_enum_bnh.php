<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Étape 1 : modifier l'ENUM pour inclure 'bnh' (et garder 'cnl' pour rétrocompatibilité)
        DB::statement("ALTER TABLE `aides` MODIFY COLUMN `type` ENUM('bnh', 'fnpos', 'cnl') NOT NULL");

        // Étape 2 (optionnel) : migrer les anciens enregistrements 'cnl' → 'bnh'
        DB::statement("UPDATE `aides` SET `type` = 'bnh' WHERE `type` = 'cnl'");

        // Étape 3 (optionnel) : retirer 'cnl' de l'ENUM si vous n'en avez plus besoin
        DB::statement("ALTER TABLE `aides` MODIFY COLUMN `type` ENUM('bnh', 'fnpos') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE `aides` SET `type` = 'cnl' WHERE `type` = 'bnh'");
        DB::statement("ALTER TABLE `aides` MODIFY COLUMN `type` ENUM('cnl', 'fnpos') NOT NULL");
    }
};