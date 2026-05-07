<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('souscripteurs', function (Blueprint $table) {
        $table->string('nin', 18)->unique()->nullable()->after('date_naissance');
    });
}

public function down(): void
{
    Schema::table('souscripteurs', function (Blueprint $table) {
        $table->dropColumn('nin');
    });
}
};