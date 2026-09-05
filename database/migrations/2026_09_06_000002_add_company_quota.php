<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('quota_qna')->default(0)->after('plan_id');
            $table->integer('quota_draft')->default(0)->after('quota_qna');
            $table->integer('quota_contract_review')->default(0)->after('quota_draft');
            $table->integer('quota_validity')->default(0)->after('quota_contract_review');
            $table->timestamp('quota_reset_at')->nullable()->after('quota_validity');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['quota_qna', 'quota_draft', 'quota_contract_review', 'quota_validity', 'quota_reset_at']);
        });
    }
};
