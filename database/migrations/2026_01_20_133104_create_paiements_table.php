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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->string('num_recu');
            $table->string('nom_agence');
            $table->string('num_agence');
            $table->date('date_paiement');
            $table->string('recu_pdf');
            $table->foreignId('ov_id')->constrained('ordres_versement')->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
                    $table->engine = 'InnoDB'; 

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
