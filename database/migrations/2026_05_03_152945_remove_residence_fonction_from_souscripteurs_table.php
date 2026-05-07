<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('souscripteurs', function (Blueprint $table) {
        $table->dropColumn(['residence', 'fonction']);
    });
}

public function down(): void
{
    Schema::table('souscripteurs', function (Blueprint $table) {
        $table->string('residence')->nullable();
        $table->string('fonction')->nullable();
    });
}
};
