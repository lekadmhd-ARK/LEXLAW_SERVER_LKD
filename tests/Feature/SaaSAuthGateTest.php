<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Gate SaaS: verifikasi email, 2FA (email OTP), status perusahaan,
 * dan penegakan kuota. Berjalan di SQLite (CI) tanpa data produksi.
 */
class SaaSAuthGateTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCompany(string $status = 'trialing', ?int $planId = null): Company
    {
        return Company::create([
            'tenant_id' => Str::uuid()->toString(),
            'name' => 'Test Co',
            'slug' => 'test-' . Str::uuid()->toString(),
            'subscription_status' => $status,
            'plan_id' => $planId,
        ]);
    }

    protected function makeUser(Company $company, array $overrides = []): User
    {
        $attrs = array_merge([
            'name' => 'Test User',
            'email' => 'test-' . Str::uuid()->toString() . '@example.com',
            'password' => 'password123',
            'company_id' => $company->id,
            'tenant_id' => $company->tenant_id,
            'role' => 'owner',
            'email_verified_at' => now(),
        ], $overrides);

        // email_verified_at tidak masuk $fillable → di-set manual.
        $verification = $attrs['email_verified_at'];
        $user = User::create(collect($attrs)->except('email_verified_at')->toArray());
        $user->email_verified_at = $verification;
        $user->save();

        return $user;
    }

    public function test_guest_diarahkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/billing')->assertRedirect('/login');
    }

    public function test_user_belum_verifikasi_email_di_blokir_fitur_ai(): void
    {
        $company = $this->makeCompany('active');
        $user = $this->makeUser($company, ['email_verified_at' => null]);

        $this->actingAs($user);
        $this->get('/ai/lex-qna')->assertRedirect('/email/verify');
        $this->get('/email/verify')->assertOk();
        $this->get('/dashboard')->assertOk();
    }

    public function test_user_terverifikasi_bisa_akses_feature_ai(): void
    {
        $company = $this->makeCompany('active');
        $user = $this->makeUser($company);

        $this->actingAs($user);
        $this->get('/ai/lex-qna')->assertOk();
    }

    public function test_company_suspended_di_arahkan_ke_billing(): void
    {
        $company = $this->makeCompany('suspended');
        $user = $this->makeUser($company);

        $this->actingAs($user);
        $this->get('/dashboard')->assertRedirect('/billing');
        $this->get('/billing')->assertOk();
    }

    public function test_company_rejected_di_arahkan_ke_billing(): void
    {
        $company = $this->makeCompany('rejected');
        $user = $this->makeUser($company);

        $this->actingAs($user);
        $this->get('/dashboard')->assertRedirect('/billing');
    }

    public function test_company_aktif_dashboard_terbuka(): void
    {
        $company = $this->makeCompany('active');
        $user = $this->makeUser($company);

        $this->actingAs($user);
        $this->get('/dashboard')->assertOk();
        $this->get('/ai/lex-qna')->assertOk();
    }

    public function test_super_admin_tidak_terkena_status_company(): void
    {
        $company = $this->makeCompany('suspended');
        $user = $this->makeUser($company, ['role' => '1']);

        $this->actingAs($user);
        $this->get('/dashboard')->assertOk();
    }

    public function test_user_tanpa_plan_diblokir_kuota_ai(): void
    {
        $company = $this->makeCompany('trialing'); // plan_id null → paket tidak aktif
        $user = $this->makeUser($company);

        $this->actingAs($user);
        $this->from('/ai/lex-qna')->post('/ai/lex-qna', ['question' => 'Halo'])->assertRedirect('/billing');
    }

    public function test_alur_two_factor_email_otp(): void
    {
        $company = $this->makeCompany('active');
        $user = $this->makeUser($company, ['two_factor_enabled' => true]);

        $this->actingAs($user);

        // Semua area inti diwajibkan OTP
        $this->get('/dashboard')->assertRedirect('/2fa');
        $this->get('/2fa')->assertOk();

        // Kode salah → kembali dengan error
        $this->from('/2fa')->post('/2fa', ['code' => '111111'])
            ->assertRedirect('/2fa')
            ->assertSessionHasErrors('code');

        // Kode benar (di-inject untuk deterministik) → lewat
        Cache::put(
            '2fa:' . $user->id,
            ['code' => Hash::make('654321'), 'expires_at' => now()->addMinutes(10)->getTimestamp()],
            600
        );

        $this->from('/2fa')->post('/2fa', ['code' => '654321'])->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk();
    }
}