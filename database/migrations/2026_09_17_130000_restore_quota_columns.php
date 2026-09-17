<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi pemulihan: kolom kuota di tabel companies & plans hilang dari skema
 * produksi, padahal migrasi lama (add_plan_limits / add_company_quota) tercatat
 * sudah "Ran". Ditambah idempotent (guard hasColumn) agar aman di semua env.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'quota_qna')) {
                $table->integer('quota_qna')->default(0)->after('plan_id');
            }
            if (! Schema::hasColumn('companies', 'quota_draft')) {
                $table->integer('quota_draft')->default(0);
            }
            if (! Schema::hasColumn('companies', 'quota_contract_review')) {
                $table->integer('quota_contract_review')->default(0);
            }
            if (! Schema::hasColumn('companies', 'quota_validity')) {
                $table->integer('quota_validity')->default(0);
            }
            if (! Schema::hasColumn('companies', 'quota_reset_at')) {
                $table->timestamp('quota_reset_at')->nullable();
            }
        });

        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'limit_qna')) {
                $table->integer('limit_qna')->default(0)->after('max_ai_queries');
            }
            if (! Schema::hasColumn('plans', 'limit_draft')) {
                $table->integer('limit_draft')->default(0);
            }
            if (! Schema::hasColumn('plans', 'limit_contract_review')) {
                $table->integer('limit_contract_review')->default(0);
            }
            if (! Schema::hasColumn('plans', 'limit_validity')) {
                $table->integer('limit_validity')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['quota_qna', 'quota_draft', 'quota_contract_review', 'quota_validity', 'quota_reset_at']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['limit_qna', 'limit_draft', 'limit_contract_review', 'limit_validity']);
        });
    }
};