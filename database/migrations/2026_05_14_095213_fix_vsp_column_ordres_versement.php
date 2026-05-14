<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE ordres_versement MODIFY COLUMN vsp TINYINT UNSIGNED NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE ordres_versement MODIFY COLUMN vsp TINYINT(1) NULL DEFAULT 0');
    }
};