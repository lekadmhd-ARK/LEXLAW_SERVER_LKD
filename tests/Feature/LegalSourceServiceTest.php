<?php

namespace Tests\Feature;

use App\Services\LegalSourceService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LegalSourceServiceTest extends TestCase
{
    public function test_parse_bpk_search_links()
    {
        Http::fake([
            'peraturan.bpk.go.id/Search*' => Http::response('
                <html><body>
                <a href="/Details/350055/permen-pu-no-6-tahun-2026">Permen PU No 6 Tahun 2026</a>
                <a href="/Details/999/bp-0002-2026">sampah</a>
                </body></html>', 200),
            '*' => Http::response('', 200),
        ]);

        Cache::flush();
        $srv = new LegalSourceService();
        $ref = new \ReflectionMethod($srv, 'searchBpk');
        $ref->setAccessible(true);
        $result = $ref->invoke($srv, 'Permen PU No. 6 Tahun 2026');

        $this->assertNotEmpty($result);
        $this->assertSame(
            'https://peraturan.bpk.go.id/Details/350055/permen-pu-no-6-tahun-2026',
            $result[0]['url'] ?? null
        );
    }

    public function test_bpk_search_dedupe_url()
    {
        Http::fake([
            'peraturan.bpk.go.id/Search*' => Http::response('
                <html><body>
                <a href="/Details/1/uu-a">UU A</a>
                <a href="/Details/1/uu-a">UU A dupe</a>
                <a href="/Details/2/pp-b">PP B</a>
                </body></html>', 200),
            '*' => Http::response('<html></html>', 200),
        ]);

        Cache::flush();
        $srv = new LegalSourceService();
        $result = $srv->search('UU A');

        $titles = array_column($result, 'title');
        $this->assertCount(1, array_filter($titles, fn($t) => str_contains(strtolower($t), 'uu a')));
    }

    public function test_get_context_returns_sources_and_context()
    {
        Http::fake([
            'peraturan.bpk.go.id/Search*' => Http::response('
                <html><body>
                <a href="/Details/350055/permen-pu-no-6-tahun-2026">Permen Pu No 6 Tahun 2026</a>
                </body></html>', 200),
            'peraturan.bpk.go.id/Details/350055/*' => Http::response('
                <html><head><title>Permen PU No. 6 Tahun 2026</title></head>
                <body>
                <section class="detail-hukum">
                Status Peraturan: Berlaku<br>
                Peraturan Menteri Pekerjaan Umum Nomor 6 Tahun 2026
                tentang Kebijakan dan Strategi Nasional Penyelenggaraan Sistem Penyediaan Air Minum Tahun 2026-2030
                </section>
                </body></html>', 200),
            '*' => Http::response('', 200),
        ]);

        Cache::flush();
        $srv = new LegalSourceService();
        $ctx = $srv->getContext('apa isi Permen PU No. 6 Tahun 2026 tentang air minum', 1);

        $this->assertNotEmpty($ctx['sources']);
        $this->assertStringContainsString('https://peraturan.bpk.go.id/Details/350055/', $ctx['sources'][0]['url']);
        $this->assertStringContainsString('permen-pu-no-6-tahun-2026', $ctx['sources'][0]['url']);
        $this->assertStringContainsString('SUMBER RESMI', $ctx['context']);
    }

    public function test_duckduckgo_filters_non_go_id()
    {
        Http::fake([
            'html.duckduckgo.com/html/*' => Http::response('
                <html><body>
                <a class="result__a" href="//duckduckgo.com/l/?uddg=https%3A%2F%2Fjdih.kemenkoinfra.go.id%2Fpermen-pu-no-6-tahun-2026">jdih.kemenkoinfra.go.id</a>
                <a class="result__a" href="//duckduckgo.com/l/?uddg=https%3A%2F%2Fwww.regulasiair.com%2Fregulasi%2Fpermen-pu-6-2026%2F">regulasiair.com (tidak resmi)</a>
                </body></html>', 200),
            '*' => Http::response('', 200),
        ]);

        Cache::flush();
        $srv = new LegalSourceService();
        $ref = new \ReflectionMethod($srv, 'searchDuckDuckGo');
        $ref->setAccessible(true);
        $result = $ref->invoke($srv, 'Permen PU No. 6 Tahun 2026');

        $urls = array_column($result, 'url');
        $this->assertContains('https://jdih.kemenkoinfra.go.id/permen-pu-no-6-tahun-2026', $urls);
        $this->assertNotContains('https://www.regulasiair.com/regulasi/permen-pu-6-2026/', $urls);
    }

    public function test_live_result_is_cached()
    {
        Http::fake(['*' => Http::response('', 200)]);
        Cache::flush();

        $srv = new LegalSourceService();
        $key = 'legalsrc_search_' . md5('permendagri no. 1 tahun 2026');

        $this->assertNull(Cache::get($key));
        $this->assertIsArray($srv->search('Permendagri No. 1 Tahun 2026'));
        $this->assertNotNull(Cache::get($key));
    }
}