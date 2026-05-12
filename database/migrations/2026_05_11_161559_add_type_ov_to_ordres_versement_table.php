<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordres_versement', function (Blueprint $table) {
            // Type de l'OV : null = tranche normale, 'credit_diff' = OV complémentaire crédit
            $table->string('type_ov')->nullable()->default(null)->after('vsp');
        });
    }

    public function down(): void
    {
        Schema::table('ordres_versement', function (Blueprint $table) {
            $table->dropColumn('type_ov');
        });
    }
};