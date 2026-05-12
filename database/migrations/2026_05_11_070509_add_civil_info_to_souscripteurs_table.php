<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('souscripteurs', function (Blueprint $table) {

            // ── Situation familiale ──────────────────────────────────
            $table->enum('situation_familiale', ['celibataire', 'marie', 'divorce', 'veuf'])
                  ->default('celibataire')
                  ->after('nin');

            // ── Lieu de naissance ────────────────────────────────────
            $table->string('lieu_naissance',    255)->nullable()->after('situation_familiale');
            $table->string('lieu_naissance_ar', 255)->nullable()->after('lieu_naissance');

            // ── Parents du souscripteur (FR) ─────────────────────────
            $table->string('nom_pere',    255)->nullable()->after('lieu_naissance_ar');
            $table->string('prenom_pere', 255)->nullable()->after('nom_pere');
            $table->string('nom_mere',    255)->nullable()->after('prenom_pere');
            $table->string('prenom_mere', 255)->nullable()->after('nom_mere');

            // ── Parents du souscripteur (AR) ─────────────────────────
            $table->string('nom_pere_ar',    255)->nullable()->after('prenom_mere');
            $table->string('prenom_pere_ar', 255)->nullable()->after('nom_pere_ar');
            $table->string('nom_mere_ar',    255)->nullable()->after('prenom_pere_ar');
            $table->string('prenom_mere_ar', 255)->nullable()->after('nom_mere_ar');

            // ── Conjoint — identité ──────────────────────────────────
            $table->string('conjoint_nom',    255)->nullable()->after('prenom_mere_ar');
            $table->string('conjoint_prenom', 255)->nullable()->after('conjoint_nom');
            $table->string('conjoint_nom_ar',    255)->nullable()->after('conjoint_prenom');
            $table->string('conjoint_prenom_ar', 255)->nullable()->after('conjoint_nom_ar');
            $table->string('conjoint_nin',  18)->nullable()->after('conjoint_prenom_ar');
            $table->date('conjoint_date_naissance')->nullable()->after('conjoint_nin');
            $table->string('conjoint_lieu_naissance',    255)->nullable()->after('conjoint_date_naissance');
            $table->string('conjoint_lieu_naissance_ar', 255)->nullable()->after('conjoint_lieu_naissance');

            // ── Parents du conjoint (FR) ─────────────────────────────
            $table->string('conjoint_nom_pere',    255)->nullable()->after('conjoint_lieu_naissance_ar');
            $table->string('conjoint_prenom_pere', 255)->nullable()->after('conjoint_nom_pere');
            $table->string('conjoint_nom_mere',    255)->nullable()->after('conjoint_prenom_pere');
            $table->string('conjoint_prenom_mere', 255)->nullable()->after('conjoint_nom_mere');

            // ── Parents du conjoint (AR) ─────────────────────────────
            $table->string('conjoint_nom_pere_ar',    255)->nullable()->after('conjoint_prenom_mere');
            $table->string('conjoint_prenom_pere_ar', 255)->nullable()->after('conjoint_nom_pere_ar');
            $table->string('conjoint_nom_mere_ar',    255)->nullable()->after('conjoint_prenom_pere_ar');
            $table->string('conjoint_prenom_mere_ar', 255)->nullable()->after('conjoint_nom_mere_ar');
        });
    }

    public function down(): void
    {
        Schema::table('souscripteurs', function (Blueprint $table) {
            $table->dropColumn([
                'situation_familiale',
                'lieu_naissance', 'lieu_naissance_ar',
                'nom_pere', 'prenom_pere', 'nom_mere', 'prenom_mere',
                'nom_pere_ar', 'prenom_pere_ar', 'nom_mere_ar', 'prenom_mere_ar',
                'conjoint_nom', 'conjoint_prenom',
                'conjoint_nom_ar', 'conjoint_prenom_ar',
                'conjoint_nin', 'conjoint_date_naissance',
                'conjoint_lieu_naissance', 'conjoint_lieu_naissance_ar',
                'conjoint_nom_pere', 'conjoint_prenom_pere',
                'conjoint_nom_mere', 'conjoint_prenom_mere',
                'conjoint_nom_pere_ar', 'conjoint_prenom_pere_ar',
                'conjoint_nom_mere_ar', 'conjoint_prenom_mere_ar',
            ]);
        });
    }
};