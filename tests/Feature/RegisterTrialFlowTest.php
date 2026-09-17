<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckQuota;
use App\Models\Company;
use App\Models\Plan;
use App\Models\TeamWorkspace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regression: akun baru langsung menjalani masa trial 3 hari tanpa perlu
 * admin approve, dan semua modul bisa diakses selama trial (kuota/billing
 * di-skip). Setelah trial habis, gate kuota berlaku normal.
 */
class RegisterTrialFlowTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->payload = [
            'name'                  => 'Trial Runner',
            'email'                 => 'trial-' . Str::uuid()->toString() . '@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'company_name'          => 'Trial Co ' . Str::random(6),
            'consent'               => '1',
        ];
    }

    public function test_registration_creates_3_day_trial(): void
    {
        $this->post('/register', $this->payload)->assertRedirect('/dashboard');

        $company = Company::where('name', $this->payload['company_name'])->firstOrFail();
        $this->assertSame('trialing', $company->subscription_status);
        $this->assertTrue($company->trial_ends_at->greaterThan(now()->addDays(2)));
        $this->assertTrue($company->trial_ends_at->lessThanOrEqualTo(now()->addDays(3)));
        $this->assertNull($company->plan_id);
    }

    private function verifyUser(User $user): User
    {
        $user->forceFill(['email_verified_at' => now()])->save();
        return $user;
    }

    public function test_trial_user_is_not_gated_by_plan_in_check_quota(): void
    {
        $company = Company::create([
            'tenant_id'           => Str::uuid()->toString(),
            'name'                => 'Trial Gate Co',
            'slug'                => 'trial-gate-' . Str::random(4),
            'subscription_status' => 'trialing',
            'trial_ends_at'       => now()->addDays(3),
        ]);
        $user = $this->verifyUser(User::create([
            'name'              => 'Trial Gate',
            'email'             => 'gate-' . Str::uuid()->toString() . '@example.com',
            'password'          => bcrypt('password123'),
            'tenant_id'         => $company->tenant_id,
            'company_id'        => $company->id,
            'role'              => 'owner',
        ]));

        $this->actingAs($user);
        $request = \Illuminate\Http\Request::create('/ai/lex-qna', 'POST');

        $response = app(CheckQuota::class)->handle($request, fn ($req) => 'NEXT', 'qna');

        $this->assertSame('NEXT', $response, 'Trial aktif tidak boleh dialihkan ke billing.');
    }

    public function test_expired_trial_is_gated_in_check_quota(): void
    {
        $company = Company::create([
            'tenant_id'           => Str::uuid()->toString(),
            'name'                => 'Trial Expired Co',
            'slug'                => 'trial-ex-' . Str::random(4),
            'subscription_status' => 'trialing',
            'trial_ends_at'       => now()->subDay(),
        ]);
        $user = $this->verifyUser(User::create([
            'name'              => 'Trial Expired',
            'email'             => 'exp-' . Str::uuid()->toString() . '@example.com',
            'password'          => bcrypt('password123'),
            'tenant_id'         => $company->tenant_id,
            'company_id'        => $company->id,
            'role'              => 'owner',
        ]));

        $this->actingAs($user);
        $request = \Illuminate\Http\Request::create('/ai/lex-qna', 'POST');

        $response = app(CheckQuota::class)->handle($request, fn ($req) => 'NEXT', 'qna');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('/billing', $response->getTargetUrl());
    }

    public function test_landing_pricing_shows_plan_from_db(): void
    {
        Plan::create([
            'name'            => 'Starter',
            'slug'            => 'basic',
            'price_monthly'   => '99000.00',
            'price_yearly'    => '990000.00',
            'max_users'       => 3,
            'max_regulations' => 500,
            'max_ai_queries'  => 100,
            'is_active'       => true,
        ]);

        $this->get('/')
             ->assertOk()
             ->assertSee('99.000', false)
             ->assertSee('990.000', false);
    }

    public function test_branding_form_saves_address_and_phone(): void
    {
        $company = Company::create([
            'tenant_id'           => Str::uuid()->toString(),
            'name'                => 'Branding Co',
            'slug'                => 'brand-' . Str::random(4),
            'subscription_status' => 'trialing',
            'trial_ends_at'       => now()->addDays(3),
        ]);
        $user = $this->verifyUser(User::create([
            'name'              => 'Branding Owner',
            'email'             => 'brand-' . Str::uuid()->toString() . '@example.com',
            'password'          => bcrypt('password123'),
            'tenant_id'         => $company->tenant_id,
            'company_id'        => $company->id,
            'role'              => 'owner',
        ]));

        $this->actingAs($user)
             ->put('/settings/branding', [
                 'address' => 'Jl. Merdeka No. 1',
                 'phone'   => '+62 812-0000-0000',
             ])
             ->assertSessionHasNoErrors();

        $company->refresh();
        $this->assertSame('Jl. Merdeka No. 1', $company->address);
        $this->assertSame('+62 812-0000-0000', $company->phone);
    }

    public function test_branding_logo_upload_saves_to_company_column(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $company = Company::create([
            'tenant_id'           => Str::uuid()->toString(),
            'name'                => 'Logo Co',
            'slug'                => 'logo-' . Str::random(4),
            'subscription_status' => 'trialing',
            'trial_ends_at'       => now()->addDays(3),
        ]);
        $user = $this->verifyUser(User::create([
            'name'              => 'Logo Owner',
            'email'             => 'logo-' . Str::uuid()->toString() . '@example.com',
            'password'          => bcrypt('password123'),
            'tenant_id'         => $company->tenant_id,
            'company_id'        => $company->id,
            'role'              => 'owner',
        ]));

        $this->actingAs($user)
             ->put('/settings/branding', [
                 'logo' => \Illuminate\Http\UploadedFile::fake()->image('logo.png', 200, 200),
             ])
             ->assertSessionHasNoErrors()
             ->assertStatus(302);

        $company->refresh();
        $this->assertNotEmpty($company->logo_url, 'Logo harus tersimpan di kolom companies.logo_url.');
        $this->assertStringContainsString('branding/', $company->logo_url);

        $this->actingAs($user)
             ->get('/settings/branding')
             ->assertOk()
             ->assertSee($company->logo_url, false);
    }

    public function test_regulation_create_and_contents_pages_render(): void
    {
        $company = Company::create([
            'tenant_id'           => Str::uuid()->toString(),
            'name'                => 'Reg Co',
            'slug'                => 'reg-' . Str::random(4),
            'subscription_status' => 'trialing',
            'trial_ends_at'       => now()->addDays(3),
        ]);
        $user = $this->verifyUser(User::create([
            'name'              => 'Reg Owner',
            'email'             => 'reg-' . Str::uuid()->toString() . '@example.com',
            'password'          => bcrypt('password123'),
            'tenant_id'         => $company->tenant_id,
            'company_id'        => $company->id,
            'role'              => 'owner',
        ]));

        $this->assertTrue(\Illuminate\Support\Facades\Route::has('regulations.fetch-jdih'));

        $this->actingAs($user)->get('/regulations/create')->assertOk();
        $this->actingAs($user)->get('/regulation-contents')->assertOk();
    }

    public function test_password_change_does_not_500(): void
    {
        $company = Company::create([
            'tenant_id'           => Str::uuid()->toString(),
            'name'                => 'Pwd Co',
            'slug'                => 'pwd-' . Str::random(4),
            'subscription_status' => 'trialing',
            'trial_ends_at'       => now()->addDays(3),
        ]);
        $user = $this->verifyUser(User::create([
            'name'              => 'Pwd Owner',
            'email'             => 'pwd-' . Str::uuid()->toString() . '@example.com',
            'password'          => bcrypt('password123'),
            'tenant_id'         => $company->tenant_id,
            'company_id'        => $company->id,
            'role'              => 'owner',
        ]));

        $this->actingAs($user)
             ->post('/password-change', [
                 'current_password'            => 'password123',
                 'new_password'                => 'newpassword456',
                 'new_password_confirmation'   => 'newpassword456',
             ])
             ->assertStatus(302)
             ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword456', $user->password));
    }

    public function test_workspace_nested_actions_do_not_500(): void
    {
        $company = Company::create([
            'tenant_id'           => Str::uuid()->toString(),
            'name'                => 'WS Co',
            'slug'                => 'ws-' . Str::random(4),
            'subscription_status' => 'trialing',
            'trial_ends_at'       => now()->addDays(3),
        ]);
        $user = $this->verifyUser(User::create([
            'name'              => 'WS Owner',
            'email'             => 'ws-' . Str::uuid()->toString() . '@example.com',
            'password'          => bcrypt('password123'),
            'tenant_id'         => $company->tenant_id,
            'company_id'        => $company->id,
            'role'              => 'owner',
        ]));
        $workspace = TeamWorkspace::create([
            'tenant_id'  => $company->tenant_id,
            'company_id' => $company->id,
            'name'       => 'CASE A',
            'created_by' => $user->id,
            'is_active'  => true,
        ]);
        $workspace->members()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);

        $this->actingAs($user)
             ->post("/team-workspaces/{$workspace->id}/members", [
                 'email' => $user->email,
                 'role'  => 'member',
             ])
             ->assertSessionHasNoErrors('email')
             ->assertStatus(302);

        $this->actingAs($user)
             ->post("/team-workspaces/{$workspace->id}/notes", [
                 'title'   => 'Catatan regresi',
                 'content' => 'Isi catatan',
             ])
             ->assertStatus(302)
             ->assertSessionHasNoErrors();

        $this->actingAs($user)
             ->post("/team-workspaces/{$workspace->id}/tasks", [
                 'title' => 'Tugas regresi',
             ])
             ->assertStatus(302)
             ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('workspace_notes', ['title' => 'Catatan regresi']);
        $this->assertDatabaseHas('workspace_tasks', ['title' => 'Tugas regresi']);
        $this->assertDatabaseHas('team_workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id'      => $user->id,
        ]);
    }
}