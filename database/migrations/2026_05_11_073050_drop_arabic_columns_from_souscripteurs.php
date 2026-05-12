<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('souscripteurs', function (Blueprint $table) {
            $table->dropColumn([
                'lieu_naissance_ar',
                'nom_pere_ar',
                'prenom_pere_ar',
                'nom_mere_ar',
                'prenom_mere_ar',
                'conjoint_nom_ar',
                'conjoint_prenom_ar',
                'conjoint_lieu_naissance_ar',
                'conjoint_nom_pere_ar',
                'conjoint_prenom_pere_ar',
                'conjoint_nom_mere_ar',
                'conjoint_prenom_mere_ar',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('souscripteurs', function (Blueprint $table) {
            $table->string('lieu_naissance_ar',          255)->nullable()->after('lieu_naissance');
            $table->string('nom_pere_ar',                255)->nullable()->after('nom_mere');
            $table->string('prenom_pere_ar',             255)->nullable()->after('nom_pere_ar');
            $table->string('nom_mere_ar',                255)->nullable()->after('prenom_pere_ar');
            $table->string('prenom_mere_ar',             255)->nullable()->after('nom_mere_ar');
            $table->string('conjoint_nom_ar',            255)->nullable()->after('conjoint_prenom');
            $table->string('conjoint_prenom_ar',         255)->nullable()->after('conjoint_nom_ar');
            $table->string('conjoint_lieu_naissance_ar', 255)->nullable()->after('conjoint_lieu_naissance');
            $table->string('conjoint_nom_pere_ar',       255)->nullable()->after('conjoint_prenom_mere');
            $table->string('conjoint_prenom_pere_ar',    255)->nullable()->after('conjoint_nom_pere_ar');
            $table->string('conjoint_nom_mere_ar',       255)->nullable()->after('conjoint_prenom_pere_ar');
            $table->string('conjoint_prenom_mere_ar',    255)->nullable()->after('conjoint_nom_mere_ar');
        });
    }
};