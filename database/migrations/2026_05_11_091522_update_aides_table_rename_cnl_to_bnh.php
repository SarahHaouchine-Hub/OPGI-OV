<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Mettre à jour les enregistrements existants
        DB::table('aides')
            ->where('type', 'cnl')
            ->update(['type' => 'bnh']);
    }

    public function down()
    {
        DB::table('aides')
            ->where('type', 'bnh')
            ->update(['type' => 'cnl']);
    }
};