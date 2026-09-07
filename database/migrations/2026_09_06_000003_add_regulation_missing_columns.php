<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            if (!Schema::hasColumn('regulations', 'category_sector')) {
                $table->string('category_sector', 30)->nullable()->after('hierarchy_level');
            }
            if (!Schema::hasColumn('regulations', 'short_description')) {
                $table->text('short_description')->nullable()->after('category_sector');
            }
            if (!Schema::hasColumn('regulations', 'penetapan_date')) {
                $table->date('penetapan_date')->nullable()->after('short_description');
            }
            if (!Schema::hasColumn('regulations', 'pengundangan_date')) {
                $table->date('pengundangan_date')->nullable()->after('penetapan_date');
            }
            if (!Schema::hasColumn('regulations', 'derogat_legi_id')) {
                $table->unsignedBigInteger('derogat_legi_id')->nullable()->after('pengundangan_date');
            }
            if (!Schema::hasColumn('regulations', 'pdf_url')) {
                $table->string('pdf_url', 500)->nullable()->after('source_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            $cols = ['category_sector', 'short_description', 'penetapan_date', 'pengundangan_date', 'derogat_legi_id', 'pdf_url'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('regulations', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
