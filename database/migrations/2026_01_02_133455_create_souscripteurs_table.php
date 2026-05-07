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
    Schema::create('souscripteurs', function (Blueprint $table) {
        $table->id();
        $table->string('nom');
        $table->string('prenom');
        $table->string('nom_arabe');
        $table->string('prenom_arabe');
        $table->date('date_naissance');
        $table->string('residence');
        $table->string('fonction');
        $table->text('qr_content_plain')->nullable();  // Données en clair
        $table->string('qr_content_hashed')->nullable(); // Empreinte (Hash) 
        $table->longText('qrcode')->nullable();
       
        // Relation vers l'utilisateur (Agent/Admin)
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        // Relation vers le logement (Assurez-vous que le type correspond à la table logements)
        // Si logements.code_loge_lpl est une PK standard, utilisez unsignedBigInteger
        $table->string('code_loge_lpl')->nullable(); 
        $table->integer('desiste')->default(0);

    // 2. Définir la contrainte
        $table->foreign('code_loge_lpl')
          ->references('code_loge_lpl')
          ->on('logements')
          ->onDelete('cascade') // Ou 'set null' si vous préférez
          ->onUpdate('cascade');
        $table->timestamps();
        
        // Force le moteur InnoDB pour activer le Cascade Delete
        $table->engine = 'InnoDB'; 
    });
}};
