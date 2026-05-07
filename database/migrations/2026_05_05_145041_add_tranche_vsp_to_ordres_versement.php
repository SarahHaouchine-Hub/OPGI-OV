<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordres_versement', function (Blueprint $table) {
            // Numéro de tranche (1-5 pour LPA, 1 pour LSP, null pour LPL)
            $table->unsignedTinyInteger('numero_tranche')->nullable()->after('montant_restant');
            // VSP (Vérification de la Situation du Programme) - OV2 LPA uniquement
            $table->boolean('vsp')->default(false)->after('numero_tranche');
        });
    }

    public function down(): void
    {
        Schema::table('ordres_versement', function (Blueprint $table) {
            $table->dropColumn(['numero_tranche', 'vsp']);
        });
    }
};