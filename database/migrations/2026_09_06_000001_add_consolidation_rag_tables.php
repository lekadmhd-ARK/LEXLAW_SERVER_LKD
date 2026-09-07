<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enrich consolidations
        Schema::table('consolidations', function (Blueprint $table) {
            if (!Schema::hasColumn('consolidations', 'source_regulations')) {
                $table->jsonb('source_regulations')->nullable();
            }
            if (!Schema::hasColumn('consolidations', 'status')) {
                $table->string('status', 20)->default('draft');
            }
            if (!Schema::hasColumn('consolidations', 'ai_metadata')) {
                $table->jsonb('ai_metadata')->nullable();
            }
        });

        // 2. Tabel audit trail
        if (!Schema::hasTable('consolidation_chunks')) {
            Schema::create('consolidation_chunks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('consolidation_id')->constrained('consolidations')->cascadeOnDelete();
                $table->integer('chunk_index');
                $table->bigInteger('source_regulation_id')->nullable();
                $table->text('source_passage');
                $table->text('ai_processed_text')->nullable();
                $table->jsonb('change_flags')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tabel passage (RAG atomik per pasal/ayat)
        if (!Schema::hasTable('regulation_passages')) {
            Schema::create('regulation_passages', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('regulation_id');
                $table->string('passage_type', 20)->default('pasal'); // bab, pasal, ayat, dll
                $table->string('passage_number', 50);
                $table->string('passage_title', 500)->nullable();
                $table->text('content');
                $table->bigInteger('parent_id')->nullable();
                $table->string('hierarchy_path', 500)->nullable();
                $table->timestamps();

                $table->index(['regulation_id', 'passage_type']);
                $table->index('hierarchy_path');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('regulation_passages');
        Schema::dropIfExists('consolidation_chunks');
        Schema::table('consolidations', function (Blueprint $table) {
            $table->dropColumn(['source_regulations', 'status', 'ai_metadata']);
        });
    }
};
