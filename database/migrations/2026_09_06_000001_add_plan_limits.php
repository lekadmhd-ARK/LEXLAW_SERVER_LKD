<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('limit_qna')->default(0)->after('max_ai_queries');
            $table->integer('limit_draft')->default(0)->after('limit_qna');
            $table->integer('limit_contract_review')->default(0)->after('limit_draft');
            $table->integer('limit_validity')->default(0)->after('limit_contract_review');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['limit_qna', 'limit_draft', 'limit_contract_review', 'limit_validity']);
        });
    }
};
