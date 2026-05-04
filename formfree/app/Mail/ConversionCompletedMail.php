<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConversionCompletedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly object $job,
        public readonly string $downloadUrl
    ) {
        $this->queue = 'emails';
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【FormFree】変換が完了しました');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.conversion-completed');
    }
}
