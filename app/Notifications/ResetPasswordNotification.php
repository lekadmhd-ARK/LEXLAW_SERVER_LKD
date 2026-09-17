<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(
        #[\SensitiveParameter] public string $token,
        public int $expireMinutes,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ["mail"];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = rtrim((string) config("app.frontend_url", config("app.url")), "/");
        $resetUrl = $frontendUrl . "/reset-password/" . $this->token . "?email=" . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->from("support@lexlaw.arktech.id", "LEXLAW Support")
            ->subject("Reset Password — LEXLAW")
            ->markdown("mail.reset-password", [
                "name" => $notifiable->name,
                "email" => $notifiable->getEmailForPasswordReset(),
                "resetUrl" => $resetUrl,
                "expireMinutes" => $this->expireMinutes,
            ]);
    }
}
