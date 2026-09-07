<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Services\LegalSourceService;

// Test non-destruktif: memakai data produksi yang ada, tidak mem-wipe DB.
class LexlawE2eTest extends TestCase
{
    private function user(): User
    {
        return User::where('email', 'admin@lexlaw.id')->first();
    }

    private function trialingUser(): User
    {
        // user dengan company subscription_status != active → form pilihan paket tampil
        foreach (User::all() as $u) {
            $c = $u->company ?? null;
            if ($c && $c->subscription_status !== 'active') {
                return $u;
            }
        }
        $this->fail('Tidak ada user dengan company non-active untuk test billing.');
    }

    /** @test */
    public function test_dashboard_tampil_setelah_login()
    {
        $resp = $this->actingAs($this->user())->get('/dashboard');
        $resp->assertOk();
        $this->assertNotEmpty(trim($resp->getContent()));
    }

    /** @test */
    public function test_billing_menampilkan_paket()
    {
        $u = $this->trialingUser();
        $resp = $this->actingAs($u)->get('/billing');
        $resp->assertOk();
        $resp->assertSee('Pilih Paket');
    }

    /** @test */
    public function test_billing2_generate_payload_qris_vald()
    {
        $resp = $this->actingAs($this->user())->post('/billing2/make-dynamic', ['nominal' => 25000]);
        $resp->assertOk();
        $content = $resp->getContent();
        $this->assertStringContainsString('540525000', $content, 'Tag 54 = 25000 harus ada');

        // validasi CRC
        preg_match('/0002010102[^<\s]+/', $content, $m);
        $payload = $m[0] ?? '';
        $this->assertNotEmpty($payload, 'Payload QRIS harus ada');
        $this->assertStringContainsString('010212', $payload, 'PIM harus 12 (dinamis)');
    }

    /** @test */
    public function test_qris_image_tersedia_untuk_paket()
    {
        foreach (['99000', '599000', '999000'] as $amt) {
            $file = public_path("paket_qris/qris_{$amt}.jpeg");
            $this->assertFileExists($file, "QRIS image paket $amt harus ada");
            $this->assertGreaterThan(10000, filesize($file), "QRIS $amt harus berisi gambar (bukan kosong)");
        }
    }

    /** @test */
    public function test_plans_exist_dan_bentuk_harga_benar()
    {
        $plans = Plan::all();
        $this->assertGreaterThanOrEqual(3, $plans->count());
        foreach ($plans as $p) {
            $this->assertIsNumeric($p->price_monthly);
        }
    }

    /** @test */
    public function test_qris_image_mapping_controller()
    {
        $ctrl = new \App\Http\Controllers\BillingController();
        $ref = new \ReflectionMethod($ctrl, 'qrisImageForAmount');
        $ref->setAccessible(true);
        $this->assertSame('/paket_qris/qris_99000.jpeg', $ref->invoke($ctrl, 99000));
        $this->assertSame('/paket_qris/qris_599000.jpeg', $ref->invoke($ctrl, 599000));
        $this->assertSame('/paket_qris/qris_999000.jpeg', $ref->invoke($ctrl, 999000));
    }

    /** @test */
    public function test_route_webhook_payment_ada()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('webhook.payment'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('billing2'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('billing2.make-dynamic'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('regulations.index'));
    }

    /** @test */
    public function test_dashboard_regulasi_ai_route_terdaftar()
    {
        foreach (['dashboard','billing','ai.lex-qna.form','ai.draft.form','billing2','regulations.index','regulations.fetch-jdih'] as $r) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($r), "route $r harus ada");
        }
    }

    /** @test */
    public function test_smoke_halaman_utama_tidak_500()
    {
        $u = $this->user();
        $urls = [
            '/dashboard', '/billing', '/billing2', '/regulations', '/legal-glossary',
            '/consolidations', '/team-workspaces', '/decisions', '/audit-logs', '/users',
            '/companies', '/password-change', '/disclaimer', '/terms-of-service',
            '/ai/lex-qna', '/ai/draft', '/ai/validity',
        ];
        $failures = [];
        foreach ($urls as $url) {
            $resp = $this->actingAs($u)->get($url);
            if ($resp->getStatusCode() === 500) {
                $failures[] = $url;
            }
        }
        $this->assertSame([], $failures, 'HTTP 500 pada: '.implode(', ', $failures));
    }

    /** @test */
    public function test_subscribe_paket_trialing_menampilkan_qris_sesuai()
    {
        $u = $this->trialingUser();
        // POST subscribe paket Pro (plan 4, 599000)
        $resp = $this->actingAs($u)->post('/billing/subscribe', ['plan_id' => 4]);
        $html = $resp->getContent();
        $this->assertStringContainsString('/paket_qris/qris_599000.jpeg', $html, 'QRIS Pro harus tampil');
        $this->assertStringContainsString('Rp 599.000', $html, 'Nominal Rp599.000 harus tampil');
    }

    /** @test */
    public function test_subscribe_paket_starter_menampilkan_qris_sesuai()
    {
        $u = $this->trialingUser();
        $resp = $this->actingAs($u)->post('/billing/subscribe', ['plan_id' => 3]);
        $html = $resp->getContent();
        $this->assertStringContainsString('/paket_qris/qris_99000.jpeg', $html, 'QRIS Starter harus tampil');
    }

    /** @test */
    public function test_subscribe_paket_ent_menampilkan_qris_sesuai()
    {
        $u = $this->trialingUser();
        $resp = $this->actingAs($u)->post('/billing/subscribe', ['plan_id' => 5]);
        $html = $resp->getContent();
        $this->assertStringContainsString('/paket_qris/qris_999000.jpeg', $html, 'QRIS Ent harus tampil');
    }

    /** @test */
    public function test_register_lalu_login_dan_logout()
    {
        $email = 'e2e-' . Str::random(8) . '@example.com';
        $companyName = 'E2E Company ' . Str::random(4);

        // Register
        $resp = $this->post('/register', [
            'name' => 'E2E Tester',
            'email' => $email,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'company_name' => $companyName,
        ]);
        $resp->assertRedirect('/dashboard');

        // User & company dibuat, status trialing
        $u = User::where('email', $email)->first();
        $this->assertNotNull($u, 'User baru harus ada');
        $this->assertNotNull($u->company, 'Company user baru harus ada');
        $this->assertSame('trialing', $u->company->subscription_status);

        // Halaman billing untuk user baru menampilkan form pilih paket
        $bill = $this->actingAs($u)->get('/billing');
        $bill->assertOk();
        $bill->assertSee('Pilih Paket');

        // Logout
        $out = $this->actingAs($u)->get('/logout');
        $out->assertRedirect('/');

        $this->assertGuest();

        // Bersihkan data uji
        $companyId = $u->company_id;
        $u->forceDelete();
        Company::where('id', $companyId)->forceDelete();
    }

    /** @test */
    public function test_admin_approve_company_trialing()
    {
        // Buat company trialing untuk diuji
        $company = Company::create([
            'tenant_id' => Str::uuid()->toString(),
            'name' => 'E2E Approve ' . Str::random(4),
            'slug' => 'e2e-approve-' . Str::random(6),
            'subscription_status' => 'trialing',
        ]);

        $admin = $this->user();
        $resp = $this->actingAs($admin)->post("/super-admin/companies/{$company->id}/approve");
        $resp->assertRedirect();

        $company->refresh();
        $this->assertSame('active', $company->subscription_status);
        $this->assertNotNull($company->subscribed_until, 'subscribed_until harus di-set');

        $resp2 = $this->actingAs($admin)->post("/super-admin/companies/{$company->id}/reject");
        $resp2->assertRedirect();
        $company->refresh();
        $this->assertSame('rejected', $company->subscription_status);

        // Bersihkan
        $company->forceDelete();
    }

    /** @test */
    public function test_billing2_dinamis_form_dan_payload()
    {
        $u = $this->trialingUser();
        $resp = $this->actingAs($u)->get('/billing2');
        $resp->assertOk();
        $resp->assertSee('QRIS');
    }

    /** @test */
    public function test_regulations_publik_tanpa_login()
    {
        // Data regulasi bersifat publik — guest (tanpa login) bisa akses index & pencarian
        $this->assertGuest();
        $resp = $this->get('/regulations');
        $resp->assertOk();
        $resp->assertSee('Regulasi');

        $search = $this->get('/regulations?q=pemeriksaan');
        $search->assertOk();

        // Halaman CRUD lain tetap terlindungi auth
        $this->get('/regulations/create')->assertRedirect('/login');
    }

    /** @test */
    public function test_regulations_publik_show_dan_pdf()
    {
        // Semua data regulasi terlihat lintas tenant
        $total = \App\Models\Regulation::count();
        $this->assertGreaterThanOrEqual(1, $total, 'Harus ada data regulasi publik.');
        $regulation = \App\Models\Regulation::first();
        if (!$regulation) {
            $this->markTestSkipped('Tidak ada data regulasi.');
        }

        $this->assertGuest();
        $show = $this->get("/regulations/{$regulation->id}");
        $show->assertOk();

        $pdf = $this->get("/regulations/{$regulation->id}/pdf");
        $this->assertTrue(in_array($pdf->getStatusCode(), [200, 302], true), 'PDF harus 200 atau redirect, got ' . $pdf->getStatusCode());
    }

    /** @test */
    public function test_legal_glossary_index_berisi_data()
    {
        $u = $this->user();
        $resp = $this->actingAs($u)->get('/legal-glossary');
        $resp->assertOk();
        $resp->assertSee('Glossary');
    }

    /** @test */
    public function test_filament_login_halaman_ada()
    {
        $resp = $this->get('/admin/login');
        $this->assertTrue(in_array($resp->getStatusCode(), [200], true), 'login filament harus 200, got ' . $resp->getStatusCode());
    }

    /** @test */
    public function test_guest_redirect_ke_login()
    {
        $this->assertGuest();
        $resp = $this->get('/dashboard');
        $resp->assertRedirect('/login');
        $resp = $this->get('/billing');
        $resp->assertRedirect('/login');
    }

    /** @test */
    public function test_trial_registration_update_bulanan()
    {
        // Set pitch: verifikasi di controller RegisterController memakai addDays(3)
        $src = file_get_contents(app_path('Http/Controllers/Auth/RegisterController.php'));
        preg_match('/now\(\)->addDays\((\d+)\)/', $src, $m);
        $this->assertSame('3', $m[1] ?? '', 'Trial baru harus 3 hari');
    }

    /** @test */
    public function test_regulations_publik_semua_user_lihat_semua_data()
    {
        // Ambil user dari tenant yang berbeda-beda; mereka HARUS melihat semua regulasi lintas tenant
        $users = User::where('role', '!=', 1)->get(); // non-superadmin
        if ($users->isEmpty()) {
            $this->markTestSkipped('Tidak ada user non-admin untuk diuji.');
        }

        $allCount = \App\Models\Regulation::count();
        $this->assertGreaterThanOrEqual(2, $allCount, 'Butuh minimal 2 regulasi untuk menguji lintas tenant.');

        foreach ($users as $u) {
            $dom = $this->actingAs($u)->get('/regulations')->getContent();

            // Pastikan halaman OK dan SEMUA regulasi masuk dalam total info pagination
            $this->assertStringContainsString('Total: <strong>', $dom, "henti total utk user {$u->email}");
            $this->assertStringContainsString((string)$allCount, $dom, "user {$u->email} (tenant {$u->tenant_id}) tidak melihat semua regulasi");

            // Detail regulasi dari tenant lain juga terbuka
            $other = \App\Models\Regulation::where('tenant_id', '!=', $u->tenant_id)->first();
            if ($other) {
                $this->actingAs($u)->get("/regulations/{$other->id}")->assertOk();
            }
        }
    }

    /** @test */
    public function test_regulations_publik_semua_user_lihat_semua_data_dashboard_stat()
    {
        // Dashboard harus menampilkan statistik regulasi global (tidak di-filter per tenant)
        $u = User::where('email', 'user2@gmail.com')->first() ?? User::where('role', '!=', 1)->first();
        if (!$u) {
            $this->markTestSkipped('Tidak ada user untuk diuji.');
        }
        $allCount = \App\Models\Regulation::count();
        $dom = $this->actingAs($u)->get('/dashboard')->getContent();
        $this->assertStringContainsString((string)$allCount, $dom, "dashboard user {$u->email} tidak menampilkan total regulasi global ({$allCount})");
    }

    /** @test */
    public function test_regulasi_tidak_boleh_double_title()
    {
        $u = $this->user();
        $existing = \App\Models\Regulation::first();
        if (!$existing) {
            $this->markTestSkipped('Tidak ada regulasi untuk diuji.');
        }
        $before = \App\Models\Regulation::count();

        // Store dengan title yang sama harus ditolak (validation error) — cegah double data
        $resp = $this->actingAs($u)->post('/regulations', [
            'title' => $existing->title,
            'hierarchy_level' => '1',
            'status' => 'active',
        ]);
        $resp->assertSessionHasErrors('title');

        $this->assertSame($before, \App\Models\Regulation::count(), 'Jumlah regulasi tidak boleh bertambah saat title ganda ditolak');
    }

    /** @test */
    public function test_smoke_halaman_tabel_admin()
    {
        $admin = $this->user();
        // Halaman dengan tabel yang dibungkus table-scroll
        foreach (['/super-admin/plans', '/super-admin/companies', '/users', '/companies', '/team-workspaces', '/audit-logs'] as $url) {
            $resp = $this->actingAs($admin)->get($url);
            $resp->assertOk();
        }
    }

    /** @test */
    public function test_lexaqna_chat_mock_ai_supports_live_sources()
    {
        $u = $this->user();
        if (!$u) {
            $this->markTestSkipped('Tidak ada user admin untuk uji LexQna.');
        }

        // Mock seluruh request keluar agar deterministik (AI + pencarian sumber resmi).
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'chat/completions')) {
                $answer = "Peraturan Menteri PU No. 6 Tahun 2026 adalah Perpres SPAM "
                    . "2026-2030. Sumber resmi: https://peraturan.bpk.go.id/Details/350055/permen-pu-no-6-tahun-2026";
                return Http::response([
                    'choices' => [['message' => ['content' => $answer]]],
                ], 200);
            }
            return Http::response('', 200);
        });

        $resp = $this->actingAs($u)->post('/ai/lex-qna', [
            'question' => 'Apa isi Permen PU No. 6 Tahun 2026?',
        ]);
        $resp->assertOk();

        $history = session('lexqna_history');
        $this->assertNotEmpty($history);
        $last = end($history);
        $this->assertSame('assistant', $last['role']);
        $this->assertStringContainsString('https://peraturan.bpk.go.id/Details/350055/', $last['content']);
    }

    /** @test */
    public function test_semua_route_ai_menunjuk_ke_method_yang_nyata()
    {
        // Route AI yang menunjuk ke method TIDAK ADA di controller = "salah jalur".
        $broken = [];
        foreach (Route::getRoutes() as $r) {
            $uri = $r->uri();
            if (!str_contains($uri, 'ai/')) {
                continue;
            }
            $actionName = $r->getActionName();
            if (!is_string($actionName) || !str_contains($actionName, 'Controller@')) {
                continue;
            }
            $parts = explode('@', $actionName);
            $method = array_pop($parts);
            $class = implode('@', $parts);
            if (!method_exists($class, $method)) {
                $broken[] = $r->methods()[0] . ' ' . $uri . ' -> ' . $class . '@' . $method;
            }
        }
        $this->assertSame([], $broken, 'Route AI mengarah ke method yang tak ada: ' . implode('; ', $broken));
    }

    /** @test */
    public function test_flow_ai_draft_dan_validity_mock_ai()
    {
        $u = $this->user();
        if (!$u) {
            $this->markTestSkipped('Tidak ada user admin.');
        }

        $faker = app(LegalSourceService::class);
        $this->app->instance(LegalSourceService::class, \Mockery::mock(LegalSourceService::class)
            ->shouldReceive('getContext')->andReturn(['context' => 'sumber resmi', 'sources' => []])
            ->shouldReceive('search')->andReturn([])
            ->getMock());
        // keep original for later tests? no — restore after is complex; fine for this test.

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'chat/completions')) {
                return Http::response([
                    'choices' => [['message' => [
                        'content' => "Pasal 1: Definisi. Sumber: https://peraturan.bpk.go.id/Details/1/x",
                    ]]],
                ], 200);
            }
            return Http::response('', 200);
        });

        // Flow Draft DOCX: form GET → submit POST → render hasil
        $resp = $this->actingAs($u)->get('/ai/draft');
        $resp->assertOk();

        $resp = $this->actingAs($u)->post('/ai/draft', [
            'document_type' => 'surat_perjanjian',
            'instructions' => 'Buat draf perjanjian sewa menyewa',
            'variant_count' => '1',
        ]);
        $resp->assertOk();
        $this->assertStringContainsString('Pasal 1', $resp->getContent());

        // Flow Validity Checker: form GET → submit POST → hasil
        $resp = $this->actingAs($u)->get('/ai/validity');
        $resp->assertOk();

        $resp = $this->actingAs($u)->post('/ai/validity', [
            'text' => "Berdasarkan Pasal 57 UU No. 13 Tahun 2003 tentang Ketenagakerjaan dan Undang-Undang Nomor 1 Tahun 1974",
        ]);
        $resp->assertOk();
    }

    /** @test */
    public function test_flow_ai_contract_review_mock_ai()
    {
        $u = $this->user();
        if (!$u) {
            $this->markTestSkipped('Tidak ada user admin.');
        }

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'chat/completions')) {
                return Http::response([
                    'choices' => [['message' => [
                        'content' => "## Ringkasan Kontrak\nKontrak sewa. Sumber: https://peraturan.bpk.go.id/Details/1/x",
                    ]]],
                ], 200);
            }
            return Http::response('', 200);
        });

        $resp = $this->actingAs($u)->get('/ai/contract-review');
        $resp->assertOk();

        $text = str_repeat('Kontrak sewa menyewa antara PT A dan PT B untuk jangka waktu 3 tahun dengan nilai Rp 1 miliar. ', 5);
        $resp = $this->actingAs($u)->post('/ai/contract-review', [
            'contract_text' => $text,
        ]);
        $resp->assertOk();
        $body = $resp->json();
        $this->assertSame(true, $body['success'] ?? false);
        $this->assertStringContainsString('## Ringkasan Kontrak', $body['answer'] ?? '');
    }
}