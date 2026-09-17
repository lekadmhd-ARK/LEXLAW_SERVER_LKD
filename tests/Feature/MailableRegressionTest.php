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
use App\Notifications\VerifyEmailNotification;
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

        $html = $mail->render();
        $this->assertStringContainsString('INV-REG-001', $html);
        // Reggresi: pastikan nama perusahaan tampil, BUKAN dump objek Company
        $this->assertStringContainsString($company->name, $html);
        $this->assertStringNotContainsString('tenant_id', $html);
        $this->assertStringNotContainsString('subscription_status', $html);
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $mail);
    }

    public function test_welcome_mail_compiles_and_is_queued(): void
    {
        ['company' => $company, 'user' => $user] = $this->makeCompanyAndUser();

        $mail = new WelcomeMail($user);

        $html = $mail->render();
        $this->assertStringContainsString('Selamat Datang', $html);
        // HTML murni, bukan markdown mentah
        $this->assertStringContainsString($company->name, $html);
        $this->assertStringNotContainsString('**', $html);
        $this->assertStringNotContainsString('---', $html);
        $this->assertStringNotContainsString('# ', $html);
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

    public function test_verify_email_notification_uses_html_view(): void
    {
        ['user' => $user] = $this->makeCompanyAndUser();

        $notification = new VerifyEmailNotification();
        $message = $notification->toMail($user);

        $this->assertStringContainsString('Verifikasi Email', $message->subject);
        $this->assertSame('mail.verify-email', $message->view);

        // view() → render mailer; HTML murni, bukan markdown mentah
        $html = $message->render();
        $this->assertStringContainsString('Verifikasi Email', $html);
        $this->assertStringNotContainsString('**', $html);
        $this->assertStringNotContainsString('# ', $html);
    }
}
