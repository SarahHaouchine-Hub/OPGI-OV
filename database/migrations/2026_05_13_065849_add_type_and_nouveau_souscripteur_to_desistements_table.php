<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('desistements', function (Blueprint $table) {
            // Type de l'opération : désistement simple ou remplacement
            $table->enum('type', ['desistement', 'remplacement'])
                  ->default('desistement')
                  ->after('user_id');

            // Nouveau souscripteur (uniquement pour type = remplacement)
            $table->foreignId('nouveau_souscripteur_id')
                  ->nullable()
                  ->after('type')
                  ->constrained('souscripteurs')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('desistements', function (Blueprint $table) {
            $table->dropForeign(['nouveau_souscripteur_id']);
            $table->dropColumn(['type', 'nouveau_souscripteur_id']);
        });
    }
};