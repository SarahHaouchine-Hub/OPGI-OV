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
    Schema::create('logements', function (Blueprint $table) {
        $table->id(); // PK standard (BigInt Unsigned)
        $table->integer('num_batiment');
        $table->integer('num_etage');
        $table->integer('num_porte');
        $table->decimal('prix', 15, 2)->default(0);
        
        // CORRECTION ICI : Ajout de unsigned()
$table->string('code_loge_lpl')->unique()->nullable(); // Changé en string        
        $table->integer('flag')->default(0);
        $table->timestamps();
        
        $table->engine = 'InnoDB'; // On force InnoDB
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logements');
    }
};
