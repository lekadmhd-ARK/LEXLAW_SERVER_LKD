<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regression: verifikasi checkbox persetujuan Kebijakan Privasi (UU PDP)
 * wajib diisi saat registrasi. Tanpa consent, registrasi ditolak.
 */
class RegisterConsentTest extends TestCase
{
    use RefreshDatabase;

    private array $validPayload = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->validPayload = [
            'name'                  => 'User Consent Test',
            'email'                 => 'consent-' . Str::uuid()->toString() . '@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'company_name'          => 'Consent Co ' . Str::random(6),
            'consent'               => '1',
        ];
    }

    public function test_register_without_consent_is_rejected(): void
    {
        $payload = $this->validPayload;
        unset($payload['consent']);

        $this->post('/register', $payload)
             ->assertSessionHasErrors('consent');
    }

    public function test_register_with_consent_creates_user_and_company(): void
    {
        $this->post('/register', $this->validPayload)
             ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', ['email' => $this->validPayload['email']]);
        $this->assertDatabaseHas('companies', ['name' => $this->validPayload['company_name']]);
    }
}
