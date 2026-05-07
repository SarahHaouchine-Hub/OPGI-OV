<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('ordres_versement', function (Blueprint $table) {
        $table->id();
        $table->foreignId('souscripteur_id')->constrained()->onDelete('cascade');
        $table->decimal('montant_total', 15, 2);
        $table->integer('pourcentage'); // 25, 50, 100...
        $table->decimal('montant_paye', 15, 2);
        $table->decimal('montant_restant', 15, 2);
        $table->text('qr_content_plain')->nullable();  // Données en clair
        $table->string('qr_content_hashed')->nullable(); // Empreinte (Hash) 
        $table->longText('qrcode')->nullable();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->timestamps();
                $table->engine = 'InnoDB'; 

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordres_versement');
    }
};
