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
    Schema::table('logements', function (Blueprint $table) {
        // Modifier num_batiment en string
        $table->string('num_batiment')->change();
        
        // Ajouter les nouvelles colonnes
        $table->string('num_lot')->nullable()->after('num_porte');
        $table->decimal('surface', 8, 2)->nullable()->after('num_lot');
        $table->string('typologie')->nullable()->after('surface'); // F3, F4, F5
        $table->foreignId('site_id')->nullable()->constrained('sites')->onDelete('cascade')->after('typologie');
    });
}

public function down(): void
{
    Schema::table('logements', function (Blueprint $table) {
        $table->integer('num_batiment')->change();
        $table->dropColumn(['num_lot', 'surface', 'typologie']);
        $table->dropForeign(['site_id']);
        $table->dropColumn('site_id');
    });
}
};
