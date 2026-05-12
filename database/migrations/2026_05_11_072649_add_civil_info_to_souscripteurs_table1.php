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

            // ── Lieu de naissance (FR uniquement) ───────────────────
            $table->string('lieu_naissance', 255)->nullable()->after('situation_familiale');

            // ── Parents du souscripteur (FR uniquement) ──────────────
            $table->string('nom_pere',    255)->nullable()->after('lieu_naissance');
            $table->string('prenom_pere', 255)->nullable()->after('nom_pere');
            $table->string('nom_mere',    255)->nullable()->after('prenom_pere');
            $table->string('prenom_mere', 255)->nullable()->after('nom_mere');

            // ── Conjoint — identité (FR uniquement) ──────────────────
            $table->string('conjoint_nom',    255)->nullable()->after('prenom_mere');
            $table->string('conjoint_prenom', 255)->nullable()->after('conjoint_nom');
            $table->string('conjoint_nin',     18)->nullable()->after('conjoint_prenom');
            $table->date('conjoint_date_naissance')->nullable()->after('conjoint_nin');
            $table->string('conjoint_lieu_naissance', 255)->nullable()->after('conjoint_date_naissance');

            // ── Parents du conjoint (FR uniquement) ──────────────────
            $table->string('conjoint_nom_pere',    255)->nullable()->after('conjoint_lieu_naissance');
            $table->string('conjoint_prenom_pere', 255)->nullable()->after('conjoint_nom_pere');
            $table->string('conjoint_nom_mere',    255)->nullable()->after('conjoint_prenom_pere');
            $table->string('conjoint_prenom_mere', 255)->nullable()->after('conjoint_nom_mere');
        });
    }

    public function down(): void
    {
        Schema::table('souscripteurs', function (Blueprint $table) {
            $table->dropColumn([
                'situation_familiale',
                'lieu_naissance',
                'nom_pere', 'prenom_pere',
                'nom_mere', 'prenom_mere',
                'conjoint_nom', 'conjoint_prenom',
                'conjoint_nin', 'conjoint_date_naissance',
                'conjoint_lieu_naissance',
                'conjoint_nom_pere', 'conjoint_prenom_pere',
                'conjoint_nom_mere', 'conjoint_prenom_mere',
            ]);
        });
    }
};