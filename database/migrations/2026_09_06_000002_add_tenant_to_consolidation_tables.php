<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regulation_passages', function (Blueprint $table) {
            if (!Schema::hasColumn('regulation_passages', 'tenant_id')) {
                $table->string('tenant_id', 64)->default('shared')->after('id');
            }
        });

        Schema::table('consolidation_chunks', function (Blueprint $table) {
            if (!Schema::hasColumn('consolidation_chunks', 'tenant_id')) {
                $table->string('tenant_id', 64)->default('shared')->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('regulation_passages', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
        Schema::table('consolidation_chunks', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }
};
