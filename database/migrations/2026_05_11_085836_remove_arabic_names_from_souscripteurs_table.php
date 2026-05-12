<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('souscripteurs', function (Blueprint $table) {
            $table->dropColumn(['nom_arabe', 'prenom_arabe']);
        });
    }

    public function down()
    {
        Schema::table('souscripteurs', function (Blueprint $table) {
            $table->string('nom_arabe', 255)->nullable();
            $table->string('prenom_arabe', 255)->nullable();
        });
    }
};