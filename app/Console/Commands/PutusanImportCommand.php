<?php

namespace App\Console\Commands;

use App\Services\PutusanDirectoryService;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature(
    'putusan:import {pn : slug pengadilan, contoh pn-jakarta-utara}
    {--kategori= : slug klasifikasi (lihat decisions/categories)}
    {--tahun= : tahun putusan, contoh 2024}
    {--limit=15 : batas jumlah putusan dalam 1 run}
    {--pages=3 : maksimal halaman listing (hal. 1 dst.)}
    {--pdf : aktifkan unduh teks dokumen PDF (lambat)}
    {--max-pdf=5 : batas PDF yang diunduh per run}'
)]
#[Description('Import putusan dari Direktori MA (via snapshot Wayback) per pengadilan')]
class PutusanImportCommand extends Command
{
    public function handle(PutusanDirectoryService $service): int
    {
        $pn = strtolower(trim($this->argument('pn')));
        if (!preg_match('/^[a-z0-9-]+$/', $pn)) {
            $this->error('PN tidak valid. Gunakan slug seperti pn-jakarta-utara.');

            return self::FAILURE;
        }

        $kategori = trim($this->option('kategori'));
        if ($kategori !== '' && !isset(PutusanDirectoryService::KATEGORI[$kategori])) {
            $this->error('Klasifikasi tidak dikenal: ' . $kategori);

            return self::FAILURE;
        }

        $tahun = trim($this->option('tahun'));
        if ($tahun !== '' && !preg_match('/^\d{4}$/', $tahun)) {
            $this->error('Tahun tidak valid.');

            return self::FAILURE;
        }

        $this->info('Import putusan ' . $pn
            . ($kategori ? ' | ' . $service->categoryLabel($kategori) : '')
            . ($tahun ? ' | Tahun ' . $tahun : '')
            . ' | limit ' . $this->option('limit')
            . ($this->option('pdf') ? ' | PDF aktif' : ''));

        $start = microtime(true);
        $stats = $service->import(
            $pn,
            $kategori,
            $tahun,
            (int) $this->option('limit'),
            (int) $this->option('pages'),
            (int) $this->option('max-pdf'),
            (bool) $this->option('pdf')
        );

        $this->newLine();
        if ($stats['errors'] > 0 && $stats['attempted'] === 0) {
            $this->error('Tidak ada snapshot arsip untuk pengadilan ini (atau akses gagal).');
        }

        if ($stats['entries']) {
            $this->table(
                ['#', 'Nomor Putusan', 'Aksi', 'Teks'],
                collect($stats['entries'])->map(fn ($e, $i) => [$i + 1, $e['nomor'], $e['action'], $e['text'] . ($e['error'] ? ' (err)' : '')])->all()
            );
        }

        $this->table(
            ['Statistik', 'Nilai'],
            [
                ['Dicoba', $stats['attempted']],
                ['Dibuat baru', $stats['created']],
                ['Diupdate', $stats['updated']],
                ['Dengan teks lengkap', $stats['with_text']],
                ['Tanpa teks', $stats['no_text']],
                ['PDF diunduh', $stats['pdf_fetched']],
                ['Filter tahun diterapkan', $stats['tahun_used'] ? 'ya' : 'tidak (snapshot tanpa tahun)'],
            ]
        );

        $this->newLine();
        $this->info(sprintf('Selesai dalam %.1f detik. Data disimpan sebagai DRAFT (is_published=false) — publish via admin sebelum tampil publik.',
            microtime(true) - $start));

        return self::SUCCESS;
    }
}