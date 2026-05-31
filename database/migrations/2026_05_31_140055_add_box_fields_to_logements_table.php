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
    Schema::table('logements', function (Blueprint $table) {
        $table->string('box_num', 20)->nullable()->after('num_lot');
        $table->decimal('box_superficie', 8, 2)->nullable()->after('box_num');
        $table->string('box_num_lot', 50)->nullable()->after('box_superficie');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logements', function (Blueprint $table) {
            //
        });
    }
};
