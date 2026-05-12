<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            // N° de convention BNH unique par projet
            $table->string('num_convention_bnh', 100)->nullable()->after('programme_id');
            
            // Agence de paiement (commune à tous les souscripteurs du projet)
            $table->string('nom_agence', 255)->nullable()->after('num_convention_bnh');
            $table->string('num_agence', 100)->nullable()->after('nom_agence');
        });
    }

    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['num_convention_bnh', 'nom_agence', 'num_agence']);
        });
    }
};