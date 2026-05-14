<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('adresse_agence')->nullable()->after('nom_agence');
            $table->string('num_compte_agence')->nullable()->after('adresse_agence');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['adresse_agence', 'num_compte_agence']);
        });
    }
};