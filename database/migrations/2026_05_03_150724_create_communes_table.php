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
Schema::dropIfExists('communes');
Schema::create('communes', function (Blueprint $table) {
    $table->id();
    $table->string('nom');
    $table->foreignId('wilaya_id')->constrained()->onDelete('cascade');
    $table->timestamps();
    $table->engine = 'InnoDB';
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communes');
    }
};
