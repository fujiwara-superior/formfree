<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentFailedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly object $company,
        public readonly string $nextRetryDate,
        public readonly string $failureReason
    ) {
        $this->queue = 'emails';
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【緊急】お支払いが処理できませんでした - FormFree');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-failed');
    }
}
