<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('putusans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_putusan')->unique(); // Nomor putusan resmi
            $table->string('panitera')->nullable(); // Panitera pengadilan
            $table->string('jenis_pengadilan'); // MA, PT, PN, PTUN, dll
            $table->string('nama_pengadilan'); // Nama lengkap pengadilan
            $table->string('tingkat_pengadilan'); // Pertama, Banding, Kasasi, Peninjauan Kembali
            $table->string('golongan_perkara'); // Pidana, Perdata, Tata Usaha Negara, dll
            $table->string('klasifikasi_perkara')->nullable(); // Klasiifkasi khusus
            $table->date('tanggal_putusan'); // Tanggal putusan
            $table->date('tanggal_register')->nullable(); // Tanggal register
            $table->text('para_pihak')->nullable(); // Pihak-pihak dalam perkara
            $table->text('ringkasan_putusan')->nullable(); // Ringkasan/Headnote
            $table->longText('isi_putusan')->nullable(); // Isi lengkap putusan
            $table->json('pasal_dikutip')->nullable(); // Pasal/pasal yang dikutip dalam putusan
            $table->json('putusan_terkait')->nullable(); // Nomor putusan terkait
            $table->string('status_putusan')->default('Berlaku'); // Berlaku, Dicabut, Dibatalkan
            $table->string('sumber_url')->nullable(); // URL sumber resmi
            $table->string('hash_content')->nullable(); // Hash untuk dedup
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['jenis_pengadilan', 'golongan_perkara']);
            $table->index('tanggal_putusan');
            $table->index('status_putusan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('putusans');
    }
};
