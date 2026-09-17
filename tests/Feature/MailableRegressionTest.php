<?php

namespace Tests\Feature;

use App\Mail\PaymentConfirmationMail;
use App\Mail\WelcomeMail;
use App\Models\Company;
use App\Models\User;
use App\Notifications\DeadlineReminderNotification;
use App\Notifications\DocumentUploadedNotification;
use App\Notifications\MemberAddedNotification;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regression: menangkap fatal error akibat missing import / interface
 * yang menimpa production (ShouldQueue 500 incident, 2026-09-17).
 */
class MailableRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function makeCompanyAndUser(): array
    {
        $company = Company::create([
            'tenant_id'          => Str::uuid()->toString(),
            'name'               => 'Regression Co ' . Str::random(8),
            'slug'               => 'regression-' . Str::uuid()->toString(),
            'subscription_status' => 'active',
        ]);

        $user = User::create([
            'name'           => 'Regression User',
            'email'          => 'reg-' . Str::uuid()->toString() . '@example.com',
            'password'       => bcrypt('password'),
            'tenant_id'      => $company->tenant_id,
            'company_id'     => $company->id,
            'role'           => 'owner',
        ]);

        return compact('company', 'user');
    }

    public function test_payment_confirmation_mail_compiles_and_is_queued(): void
    {
        ['company' => $company] = $this->makeCompanyAndUser();

        $mail = new PaymentConfirmationMail(
            company:       $company,
            invoice:       'INV-REG-001',
            amount:        150000,
            method:        'QRIS',
            paidAt:        now()->toDateTimeString(),
            subscribedUntil: now()->addMonth()->toDateTimeString(),
        );

        $this->assertStringContainsString('Pembayaran Berhasil', $mail->envelope()->subject);
        $this->assertStringContainsString('INV-REG-001', $mail->render());
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $mail);
    }

    public function test_welcome_mail_compiles_and_is_queued(): void
    {
        ['user' => $user] = $this->makeCompanyAndUser();

        $mail = new WelcomeMail($user);

        $this->assertStringContainsString('Selamat Datang', $mail->render());
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $mail);
    }

    public function test_all_notifications_load_and_are_queued(): void
    {
        $notifications = [
            DeadlineReminderNotification::class,
            DocumentUploadedNotification::class,
            MemberAddedNotification::class,
            TaskAssignedNotification::class,
        ];

        foreach ($notifications as $class) {
            $this->assertTrue(class_exists($class), $class . ' failed to load');
            $this->assertTrue(
                is_subclass_of($class, \Illuminate\Contracts\Queue\ShouldQueue::class),
                $class . ' must implement ShouldQueue'
            );
        }
    }
}
