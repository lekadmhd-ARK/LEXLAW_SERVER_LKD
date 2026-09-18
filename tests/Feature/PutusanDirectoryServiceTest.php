<?php

namespace Tests\Feature;

use App\Models\Putusan;
use App\Services\PutusanDirectoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PutusanDirectoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['cache.default' => 'array']);
    }

    private function listingHtml(): string
    {
        $entry = function (string $hash, string $nomor) {
            return '<div class="spost clearfix"><div class="entry-c"><div class="small">'
                . '<a href="https://putusan3.mahkamahagung.go.id/pengadilan/profil/pengadilan/pn-jakarta-utara.html">PN JAKARTA UTARA</a>'
                . ' <a href="https://putusan3.mahkamahagung.go.id/direktori/index/pengadilan/pn-jakarta-utara/kategori/perdata-1.html">Perdata</a></div>'
                . '<div class="small"><strong>Register :</strong> 07-03-2024 &#8212; <strong>Putus :</strong> 20-11-2024 &#8212; <strong>Upload :</strong> 20-11-2024</div>'
                . '<strong><a href="https://putusan3.mahkamahagung.go.id/direktori/putusan/' . $hash . '.html">Putusan PN JAKARTA UTARA Nomor ' . $nomor . '</a></strong><br>'
                . '<div>Tanggal 20 Nopember 2024 &#8212; Penggugat melawan Tergugat<br>'
                . '</div></div></div>';
        };

        return '<html><div id="direktori">'
            . $entry('zaefa74d7f2fa0989375323134313135', '163/Pdt.G/2024/PN Jkt.Utr')
            . $entry('zaef37700000000000000000000000000', '377/Pdt.G/2024/PN Jkt.Utr')
            . '</div></html>';
    }

    private function detailHtml(): string
    {
        return '<html><body><strong><a href="https://putusan3.mahkamahagung.go.id/direktori/putusan/zaefa74d7f2fa0989375323134313135.html">Putusan PN JAKARTA UTARA Nomor 163/Pdt.G/2024/PN Jkt.Utr</a></strong>'
            . '<a href="https://putusan3.mahkamahagung.go.id/direktori/download_file/d1fd0d491f7d16f19bcc4dfbe2eb94db/pdf/000d9c579464b855744dac1089cb4618">PDF</a>'
            . '</body></html>';
    }

    private function fakeArchive(): void
    {
        Http::fake(function ($request) {
            $url = $request->url();
            $query = [];
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

            if (str_contains($url, '/cdx/search/cdx')) {
                $target = (string) ($query['url'] ?? '');
                $mt = (string) ($query['matchType'] ?? '');

                if ($mt === 'prefix' && str_contains($target, 'direktori/index/pengadilan/pn-jakarta-utara')) {
                    return Http::response(
                        "20241202193542 https://putusan3.mahkamahagung.go.id/direktori/index/pengadilan/pn-jakarta-utara/kategori/perdata-1.html\n"
                        . "20240810034726 https://putusan3.mahkamahagung.go.id/direktori/index/pengadilan/pn-jakarta-utara/kategori/pidana-umum-1.html\n"
                    );
                }
                if ($mt === 'exact' && str_contains($target, 'direktori/putusan/zaefa74d7f2fa0989375323134313135')) {
                    return Http::response(
                        "20241202193500 https://putusan3.mahkamahagung.go.id/direktori/putusan/zaefa74d7f2fa0989375323134313135.html\n"
                    );
                }
                if ($mt === 'exact' && str_contains($target, 'download_file')) {
                    return Http::response(
                        "20250316030201 https://putusan3.mahkamahagung.go.id/direktori/download_file/d1fd0d491f7d16f19bcc4dfbe2eb94db/pdf/000d9c579464b855744dac1089cb4618\n"
                    );
                }

                return Http::response('', 200);
            }

            if (str_contains($url, '/web/20240810034726id_/') || str_contains($url, '/web/20241202193542id_/')) {
                return Http::response($this->listingHtml(), 200, ['Content-Type' => 'text/html']);
            }
            if (str_contains($url, '/web/20241202193500id_/')) {
                return Http::response($this->detailHtml(), 200, ['Content-Type' => 'text/html']);
            }
            if (str_contains($url, '/web/20250316030201id_/')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml('<html><body><h1>Putusan PN JAKARTA UTARA Nomor 163/Pdt.G/2024/PN Jkt.Utr</h1><p>Menimbang, bahwa sebagai akibat dari pemutusan perjanjian tersebut penggugat berhak atas ganti rugi sebesar Rp. 100.000.000,- (seratus juta rupiah). Menimbang, bahwa tergugat telah mengingkari isi perjanjian sehingga penggugat mengalami kerugian materiil dan immateriil. Menimbang, bahwa berdasarkan pertimbangan di atas, majelis hakim menjatuhkan putusan dalam perkara ganti rugi atas wanprestasi yang diajukan oleh penggugat kepada tergugat.</p></body></html>');
                $dompdf->setPaper('A4');
                $dompdf->render();

                return Http::response($dompdf->output(), 200, ['Content-Type' => 'application/pdf']);
            }

            return Http::response('', 404);
        });
    }

    public function test_import_persists_metadata_and_dedupes(): void
    {
        $this->fakeArchive();
        $service = app(PutusanDirectoryService::class);

        $first = $service->import('pn-jakarta-utara', 'perdata-17441', '', 15, 1, 5, false);

        $this->assertSame(2, $first['attempted']);
        $this->assertSame(2, $first['created']);
        $this->assertSame(0, $first['updated']);
        $this->assertSame(2, Putusan::count());

        $row = Putusan::where('nomor_putusan', '163/Pdt.G/2024/PN Jkt.Utr')->firstOrFail();
        $this->assertSame('PN', $row->jenis_pengadilan);
        $this->assertSame('PN JAKARTA UTARA', $row->nama_pengadilan);
        $this->assertSame('Perdata', $row->golongan_perkara);
        $this->assertSame('Pertama', $row->tingkat_pengadilan);
        $this->assertSame('20-11-2024', $row->tanggal_putusan?->format('d-m-Y'));
        $this->assertNull($row->isi_putusan);
        $this->assertFalse($row->is_published);
        $this->assertStringContainsString('penggugat', strtolower($row->para_pihak));

        $second = $service->import('pn-jakarta-utara', 'perdata-17441', '', 15, 1, 5, false);

        $this->assertSame(0, $second['created']);
        $this->assertSame(2, $second['updated']);
    }

    public function test_import_with_pdf_extracts_full_text(): void
    {
        $this->fakeArchive();
        $service = app(PutusanDirectoryService::class);

        $stats = $service->import('pn-jakarta-utara', 'perdata-17441', '', 15, 1, 5, true);

        $this->assertSame(1, $stats['pdf_fetched']);
        $this->assertSame(1, $stats['with_text']);
        $this->assertSame(1, $stats['no_text']);

        $row = Putusan::where('nomor_putusan', '163/Pdt.G/2024/PN Jkt.Utr')->firstOrFail();
        $this->assertNotNull($row->isi_putusan);
        $this->assertSame(sha1($row->isi_putusan), $row->hash_content);
        $this->assertStringContainsString('Menimbang', $row->isi_putusan);

        // Entri kedua tidak punya snapshot detail -> metadata saja, tanpa teks.
        $row2 = Putusan::where('nomor_putusan', '377/Pdt.G/2024/PN Jkt.Utr')->firstOrFail();
        $this->assertNull($row2->isi_putusan);
    }

    public function test_import_empty_archive_returns_error_state(): void
    {
        Http::fake([
            '*' => Http::response('', 200),
        ]);
        Cache::flush();

        $stats = app(PutusanDirectoryService::class)->import('pn-tidak-ada', '', '', 15, 1, 5, false);

        $this->assertSame(0, $stats['attempted']);
        $this->assertSame(1, $stats['errors']);
    }
}