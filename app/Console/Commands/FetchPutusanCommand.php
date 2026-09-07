<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('putusan:fetch')]
#[Description('Fetch putusan terbaru dari Mahkamah Agung (menggunakan aggregator BPK sebagai proxy)')]
class FetchPutusanCommand extends Command
{
    public function handle()
    {
        $this->info('Memulai fetch putusan...');
        
        // Menggunakan aggregator BPK sebagai sumber untuk mencari putusan MA
        // Karena putusan3.mahkamahagung.go.id sulit discrape langsung.
        $service = app(\App\Services\LegalSourceService::class);
        $query = "Putusan Mahkamah Agung";
        
        $results = $service->search($query, 10);
        
        $count = 0;
        foreach ($results as $r) {
            if (str_contains($r['url'], 'putusan') || str_contains($r['title'], 'Putusan')) {
                // Fetch detail
                try {
                    $detail = $service->fetch($r['url']);
                    
                    \App\Models\Putusan::updateOrCreate(
                        ['nomor_putusan' => $r['title']],
                        [
                            'nama_pengadilan' => 'Mahkamah Agung RI',
                            'jenis_pengadilan' => 'MA',
                            'golongan_perkara' => 'Umum',
                            'tingkat_pengadilan' => 'Kasasi',
                            'tanggal_putusan' => now(),
                            'isi_putusan' => $detail['text'],
                            'sumber_url' => $r['url'],
                        ]
                    );
                    $count++;
                    $this->info("Imported: " . $r['title']);
                } catch (\Exception $e) {
                    $this->error("Gagal fetch " . $r['url'] . ": " . $e->getMessage());
                }
            }
        }
        
        $this->info("Selesai! Total putusan diimport: $count");
    }
}
