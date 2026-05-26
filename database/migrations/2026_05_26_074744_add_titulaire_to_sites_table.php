<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('sites', function (Blueprint $table) {
        $table->string('titulaire')->nullable()->after('num_compte_agence');
    });
}

public function down(): void
{
    Schema::table('sites', function (Blueprint $table) {
        $table->dropColumn('titulaire');
    });
}
};
