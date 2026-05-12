<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('credits_bancaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('souscripteur_id')->constrained('souscripteurs')->onDelete('cascade');
            
            // Montants
            $table->decimal('montant_attestation', 15, 2); // Ce qui est écrit sur l'attestation
            $table->decimal('montant_reel', 15, 2);        // Ce que la banque verse réellement
            $table->decimal('difference', 15, 2);          // Attestation - Réel (calculé)
            
            // Dates
            $table->date('date_attestation')->nullable();
            $table->date('date_versement_reel')->nullable();
            
            // Pièces jointes
            $table->string('pieces_jointes')->nullable(); // Scan de l'attestation
            
            // Audit
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('credits_bancaires');
    }
};