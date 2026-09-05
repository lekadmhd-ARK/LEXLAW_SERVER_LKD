<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHierarchyToRegulationsTable extends Migration
{
    public function up()
    {
        Schema::table('regulations', function (Blueprint $table) {
            // Hierarchy level: 1=UU, 2=PP, 3=Perpres, 4=PerMen, 5=Perda
            $table->enum('hierarchy_level', ['1', '2', '3', '4', '5'])->after('year')->comment('Level kekuatan: 1=UU, 2=PP, 3=Perpres, 4=PerMen, 5=Perda');

            // Sector/Topik: Ketenagakerjaan, Perpajakan, Perusahaan, Agraria, TI
            $table->enum('category_sector', ['ketenagakerjaan', 'perpajakan', 'perusahaan', 'agraria', 'teknologi', 'lainnya'])->nullable()->after('hierarchy_level');

            // Status Berlaku
            $table->boolean('is_active')->default(true);

            // Referensi peraturan yang dicabut/diubah
            $table->unsignedBigInteger('derogat_legi_id')->nullable();
            $table->foreign('derogat_legi_id')->references('id')->on('regulations');

            // Metadata tambahan
            $table->text('short_description')->nullable();
            $table->string('penetapan_date')->nullable();
            $table->string('pengundangan_date')->nullable();

        });
    }

    public function down()
    {
        Schema::table('regulations', function (Blueprint $table) {
            $table->dropColumn(['hierarchy_level', 'category_sector', 'is_active', 'derogat_legi_id', 'short_description', 'penetapan_date', 'pengundangan_date']);
        });
    }
}
