<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UsageLimitReachedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly object $company,
        public readonly int    $usedCount,
        public readonly int    $limit,
        public readonly bool   $isWarning = false
    ) {
        $this->queue = 'emails';
    }

    public function envelope(): Envelope
    {
        $subject = $this->isWarning
            ? '【FormFree】無料変換枠が残りわずかです（' . $this->usedCount . '/' . $this->limit . '件）'
            : '【重要】今月の無料変換枠を使い切りました';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.usage-limit');
    }
}
