<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            if (!Schema::hasColumn('regulations', 'pdf_url')) {
                $table->string('pdf_url', 500)->nullable()->after('source_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            if (Schema::hasColumn('regulations', 'pdf_url')) {
                $table->dropColumn('pdf_url');
            }
        });
    }
};
