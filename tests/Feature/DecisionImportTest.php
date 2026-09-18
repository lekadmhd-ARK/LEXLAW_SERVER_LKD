<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Services\PutusanDirectoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class DecisionImportTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        $company = Company::create([
            'tenant_id' => Str::uuid()->toString(),
            'name' => 'C',
            'slug' => 'c-' . Str::random(4),
            'subscription_status' => 'trialing',
            'trial_ends_at' => now()->addDays(3),
        ]);

        $user = User::create([
            'name' => 'U',
            'email' => 'u-' . Str::uuid()->toString() . '@example.com',
            'password' => bcrypt('x'),
            'tenant_id' => $company->tenant_id,
            'company_id' => $company->id,
            'role' => 'owner',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    public function test_import_route_invokes_service_and_returns_summary(): void
    {
        $summary = [
            'attempted' => 1,
            'created' => 1,
            'updated' => 0,
            'with_text' => 0,
            'no_text' => 1,
            'pdf_fetched' => 0,
            'errors' => 0,
            'tahun_used' => false,
            'entries' => [
                ['nomor' => '163/Pdt.G/2024/PN Jkt.Utr', 'action' => 'created', 'text' => 'no', 'error' => null],
            ],
        ];

        $mock = Mockery::mock(PutusanDirectoryService::class);
        $mock->shouldReceive('import')
            ->once()
            ->withArgs(function (string $pn, string $kategoris, string $tahun, int $limit, int $pages, int $maxPdf, bool $withPdf) {
                return $pn === 'pn-jakarta-utara'
                    && $kategoris === 'perdata-17441'
                    && $tahun === '2024'
                    && $limit === 10
                    && $withPdf === false;
            })
            ->andReturn($summary);

        $this->app->instance(PutusanDirectoryService::class, $mock);

        $this->actingAs($this->makeUser())
            ->postJson(route('decisions.import'), [
                'pn' => 'pn-jakarta-utara',
                'kategori' => 'perdata-17441',
                'tahun' => '2024',
                'limit' => 10,
                'with_pdf' => false,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('summary.created', 1)
            ->assertJsonPath('summary.entries.0.nomor', '163/Pdt.G/2024/PN Jkt.Utr');
    }

    public function test_import_rejects_invalid_pn(): void
    {
        $this->actingAs($this->makeUser())
            ->postJson(route('decisions.import'), ['pn' => 'bukan-slug', 'limit' => 10])
            ->assertStatus(422)
            ->assertJsonPath('error', 'PN tidak valid');
    }

    public function test_import_rejects_unknown_kategori(): void
    {
        $this->actingAs($this->makeUser())
            ->postJson(route('decisions.import'), ['pn' => 'pn-jakarta-utara', 'kategori' => 'ngawur', 'limit' => 10])
            ->assertStatus(422);
    }

    public function test_import_requires_auth(): void
    {
        $this->postJson(route('decisions.import'), ['pn' => 'pn-jakarta-utara'])
            ->assertUnauthorized();
    }
}