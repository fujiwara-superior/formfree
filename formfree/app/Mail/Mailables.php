<?php
// ============================================================
// app/Mail/ConversionCompletedMail.php
// ============================================================
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


// ============================================================
// app/Mail/UsageLimitReachedMail.php
// ============================================================
class UsageLimitReachedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly object $company,
        public readonly int    $usedCount,
        public readonly int    $limit,
        public readonly bool   $isWarning = false  // trueなら80%警告
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


// ============================================================
// app/Mail/PaymentFailedMail.php
// ============================================================
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


// ============================================================
// app/Mail/PlanUpgradedMail.php
// ============================================================
class PlanUpgradedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly object $company,
        public readonly string $plan
    ) {
        $this->queue = 'emails';
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【FormFree】プランのアップグレードが完了しました');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.plan-upgraded');
    }
}
