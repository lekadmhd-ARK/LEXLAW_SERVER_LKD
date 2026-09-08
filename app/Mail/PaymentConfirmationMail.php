<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Company $company,
        public string $invoice,
        public int $amount,
        public string $method,
        public ?string $paidAt = null,
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
                'company' => $this->company->name,
                'invoice' => $this->invoice,
                'amount' => number_format($this->amount, 0, ',', '.'),
                'method' => $this->method,
                'paidAt' => $this->paidAt,
            ],
        );
    }
}