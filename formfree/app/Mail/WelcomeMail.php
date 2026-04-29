<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly object $user,
        public readonly object $company
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【FormFree】ご登録ありがとうございます');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.welcome');
    }
}
