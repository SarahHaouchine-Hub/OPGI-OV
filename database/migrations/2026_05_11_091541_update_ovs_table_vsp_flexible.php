<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ordres_versement', function (Blueprint $table) {
            // VSP est déjà boolean/tinyint, mais on s'assure qu'elle est nullable
            $table->boolean('vsp')->nullable()->default(false)->change();
        });
    }

    public function down()
    {
        // Rollback si nécessaire
    }
};