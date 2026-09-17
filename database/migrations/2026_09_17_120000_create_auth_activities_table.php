<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->index();
            $table->string('event', 32); // register | login | login_failed | logout
            $table->string('email', 255)->nullable(); // diisi saat email tidak dikenal (failed)
            $table->string('ip_address', 45)->nullable();   // public/real IP (cf trust proxies)
            $table->string('mac_address', 45)->nullable();  // hanya terisi saat server di LAN sama
            $table->string('local_ip', 45)->nullable();     // best-effort dari WebRTC (js client)
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_activities');
    }
};