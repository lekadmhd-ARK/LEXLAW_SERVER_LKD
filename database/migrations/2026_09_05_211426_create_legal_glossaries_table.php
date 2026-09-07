<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_glossaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('term', 255);
            $table->string('singkatan', 100)->nullable();
            $table->string('kategori_hukum', 50)->nullable()->index();
            $table->text('definisi_singkat')->nullable();
            $table->text('penjelasan_lengkap')->nullable();
            $table->jsonb('dasar_hukum_terkait')->nullable();
            $table->text('contoh_implementasi')->nullable();
            // Legacy compat columns (keep old controller working)
            $table->text('definition')->nullable();
            $table->string('category', 50)->nullable();
            $table->jsonb('cross_references')->nullable();
            $table->timestamps();
        });
        // pg_trgm for fuzzy search (create extension first)
        DB::statement("CREATE EXTENSION IF NOT EXISTS pg_trgm");
        // Full-text GIN index
        DB::statement("CREATE INDEX idx_glossary_term_trgm ON legal_glossaries USING gin (term gin_trgm_ops)");
        DB::statement("CREATE INDEX idx_glossary_singkatan_trgm ON legal_glossaries USING gin (singkatan gin_trgm_ops)");
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_glossaries');
    }
};
