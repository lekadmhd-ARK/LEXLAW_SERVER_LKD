<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auth_activities', function (Blueprint $table) {
            $table->string('device_fingerprint', 64)->nullable()->after('local_ip');
            $table->string('geo_country', 64)->nullable()->after('device_fingerprint');
            $table->string('geo_region', 96)->nullable()->after('geo_country');
            $table->string('geo_city', 96)->nullable()->after('geo_region');
            $table->decimal('geo_lat', 10, 6)->nullable()->after('geo_city');
            $table->decimal('geo_lon', 10, 6)->nullable()->after('geo_lat');
            $table->string('geo_isp', 128)->nullable()->after('geo_lon');
        });
    }

    public function down(): void
    {
        Schema::table('auth_activities', function (Blueprint $table) {
            $table->dropColumn([
                'device_fingerprint',
                'geo_country', 'geo_region', 'geo_city', 'geo_lat', 'geo_lon', 'geo_isp',
            ]);
        });
    }
};