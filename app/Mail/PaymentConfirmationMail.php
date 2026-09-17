<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Company $company,
        public string $invoice,
        public int $amount,
        public string $method,
        public ?string $paidAt = null,
        public ?string $subscribedUntil = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembayaran Berhasil — LEXLAW',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.payment-confirmation',
            with: [
                'company_name'     => $this->company->name,
                'plan'             => $this->company->plan?->name ?? 'LEXLAW Pro',
                'invoice'          => $this->invoice,
                'amount'           => number_format($this->amount, 0, ',', '.'),
                'method'           => $this->method,
                'paidAt'           => $this->paidAt ?? now()->setTimezone('Asia/Jakarta')->format('d M Y H:i'),
                'subscribedUntil'  => $this->subscribedUntil,
            ],
        );
    }
}
