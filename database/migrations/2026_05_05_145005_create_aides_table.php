<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('souscripteur_id')->constrained('souscripteurs')->onDelete('cascade');
            $table->enum('type', ['cnl', 'fnpos']);
            $table->decimal('montant', 15, 2);
            $table->string('num_convention', 100)->nullable(); // CNL uniquement
            $table->string('num_decision', 100);
            $table->date('date');
            $table->string('pieces_jointes')->nullable(); // chemin fichier
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            // Un souscripteur ne peut avoir qu'une seule aide de chaque type
            $table->unique(['souscripteur_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aides');
    }
};