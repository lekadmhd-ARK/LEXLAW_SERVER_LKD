<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Data regulasi bersifat publik & harus unik per judul (case-insensitive).
        // Bersihkan duplikat existing yang tersisa sebelum menambah unique index.
        DB::statement("
            DELETE FROM regulations a
            USING regulations b
            WHERE a.id > b.id
              AND lower(trim(a.title)) = lower(trim(b.title))
        ");

        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS regulations_lower_title_unique ON regulations (lower(trim(title)))');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS regulations_lower_title_unique');
    }
};