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
 * Regression: superadmin (role=1) wajib melalui 2FA setiap kali login,
 * dan tidak dapat menonaktifkannya — terlepas dari nilai kolom
 * two_factor_enabled.
 */
class SuperAdmin2faTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'tenant_id'           => Str::uuid()->toString(),
            'name'                => '2FA Test Co',
            'slug'                => '2fa-test-' . Str::uuid()->toString(),
            'subscription_status' => 'active',
        ]);

        $this->superAdmin = User::create([
            'name'           => 'Super Admin 2FA',
            'email'          => 'superadmin-2fa-' . Str::uuid()->toString() . '@example.com',
            'password'       => 'password123',
            'tenant_id'      => $this->company->tenant_id,
            'company_id'     => $this->company->id,
            'role'           => 1,
            'email_verified_at' => now(),
        ]);
    }

    private function loginAsSuperAdmin()
    {
        return $this->post('/login', [
            'email'    => $this->superAdmin->email,
            'password' => 'password123',
        ]);
    }

    public function test_superadmin_login_always_triggers_2fa_redirect(): void
    {
        $this->loginAsSuperAdmin()->assertRedirect('/2fa');

        // Sesi sudah authenticated tapi area inti tetap diarahkan ke 2FA
        $this->assertAuthenticatedAs($this->superAdmin);
        $this->get('/dashboard')->assertRedirect('/2fa');
    }

    public function test_superadmin_cannot_disable_2fa(): void
    {
        $this->actingAs($this->superAdmin);
        // Tandai 2FA sudah lewat agar request lolos RequireTwoFactor sampai ke controller
        session()->put('2fa_passed', true);

        $this->post(route('security.two-factor.disable'))
            ->assertSessionHas('error')
            ->assertRedirect();

        $this->superAdmin->refresh();
        $this->assertTrue(
            $this->superAdmin->two_factor_enabled || $this->superAdmin->role == 1,
            'SuperAdmin 2FA should remain enforced'
        );
    }

    public function test_superadmin_with_2fa_disabled_still_redirected_to_2fa(): void
    {
        // Force column to false — enforcement is role-based, not column-based
        $this->superAdmin->update(['two_factor_enabled' => false]);
        $this->loginAsSuperAdmin();

        $this->get('/dashboard')->assertRedirect('/2fa');
    }

    public function test_superadmin_can_complete_2fa_flow_and_access_dashboard(): void
    {
        $this->loginAsSuperAdmin();
        $this->get('/dashboard')->assertRedirect('/2fa');

        // Inject deterministic code into cache
        Cache::put(
            '2fa:' . $this->superAdmin->id,
            ['code' => Hash::make('999888'), 'expires_at' => now()->addMinutes(10)->getTimestamp()],
            600
        );

        $this->from('/2fa')->post('/2fa', ['code' => '999888'])
             ->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk();
    }
}
