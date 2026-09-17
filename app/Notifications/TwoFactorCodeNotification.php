<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode Verifikasi — LEXLAW')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Kode verifikasi dua langkah Anda adalah:')
            ->line('**' . $this->code . '**')
            ->line('Kode berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun.');
    }
}