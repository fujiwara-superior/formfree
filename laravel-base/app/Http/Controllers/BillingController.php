<?php

namespace App\Http\Controllers;

use App\Mail\PaymentFailedMail;
use App\Mail\PlanUpgradedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// ─── BillingController ──────────────────────────────────────────
class BillingController extends Controller
{
    public function index()
    {
        $company = auth()->user()->company;
        return view('billing.index', compact('company'));
    }

    public function createCheckoutSession(Request $request)
    {
        $request->validate(['plan' => 'required|in:standard,pro']);

        $company = auth()->user()->company;

        $priceId = match ($request->plan) {
            'standard' => config('stripe.prices.standard'),
            'pro'      => config('stripe.prices.pro'),
        };

        $stripe  = new \Stripe\StripeClient(config('stripe.secret'));
        $session = $stripe->checkout->sessions->create([
            'mode'                       => 'subscription',
            'customer_email'             => $company->email,
            'line_items'                 => [['price' => $priceId, 'quantity' => 1]],
            'success_url'                => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'                 => route('billing.index'),
            'metadata'                   => ['company_id' => $company->id, 'plan' => $request->plan],
            'payment_method_types'       => ['card'],
            'locale'                     => 'ja',
            'billing_address_collection' => 'required',
        ]);

        return response()->json(['checkout_url' => $session->url]);
    }

    public function success(Request $request)
    {
        return view('billing.success');
    }

    public function cancel()
    {
        return redirect()->route('billing.index')
            ->with('info', '決済をキャンセルしました。');
    }

    public function portal(Request $request)
    {
        $company = auth()->user()->company;
        $stripe  = new \Stripe\StripeClient(config('stripe.secret'));

        $session = $stripe->billingPortal->sessions->create([
            'customer'   => $company->stripe_customer_id,
            'return_url' => route('billing.index'),
        ]);

        return redirect($session->url);
    }
}

// ─── WebhookController ──────────────────────────────────────────
class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            Log::warning('Stripe Webhook signature invalid', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // 冪等性チェック
        if ($this->isAlreadyProcessed($event->id)) {
            return response()->json(['status' => 'already_processed']);
        }

        match ($event->type) {
            'checkout.session.completed'    => $this->handleCheckoutCompleted($event->data->object),
            'invoice.payment_succeeded'     => $this->handlePaymentSucceeded($event->data->object),
            'invoice.payment_failed'        => $this->handlePaymentFailed($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionCancelled($event->data->object),
            default                         => null,
        };

        $this->markAsProcessed($event->id, $event->type);

        return response()->json(['status' => 'ok']);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        $companyId = $session->metadata->company_id;
        $plan      = $session->metadata->plan;
        $limits    = ['standard' => 100, 'pro' => 500];

        DB::table('companies')
            ->where('id', $companyId)
            ->update([
                'plan'               => $plan,
                'monthly_job_limit'  => $limits[$plan],
                'stripe_customer_id' => $session->customer,
                'stripe_sub_id'      => $session->subscription,
                'updated_at'         => now(),
            ]);

        $company = DB::table('companies')->where('id', $companyId)->first();
        Mail::to($company->email)->queue(new PlanUpgradedMail($company, $plan));
    }

    private function handlePaymentSucceeded(object $invoice): void
    {
        // 支払い成功：特に処理なし（checkoutで対応済み）
        Log::info('Payment succeeded', ['customer' => $invoice->customer]);
    }

    private function handlePaymentFailed(object $invoice): void
    {
        $company = DB::table('companies')
            ->where('stripe_customer_id', $invoice->customer)
            ->first();

        if (!$company) return;

        $nextRetry   = $invoice->next_payment_attempt
            ? now()->createFromTimestamp($invoice->next_payment_attempt)->format('Y年m月d日')
            : '未定';
        $failReason  = $this->translateFailReason($invoice->last_payment_error?->code ?? '');

        Mail::to($company->email)
            ->queue(new PaymentFailedMail($company, $nextRetry, $failReason));
    }

    private function handleSubscriptionCancelled(object $subscription): void
    {
        DB::table('companies')
            ->where('stripe_sub_id', $subscription->id)
            ->update([
                'plan'               => 'free',
                'monthly_job_limit'  => 10,
                'stripe_sub_id'      => null,
                'updated_at'         => now(),
            ]);
    }

    private function isAlreadyProcessed(string $eventId): bool
    {
        return DB::table('webhook_events')
            ->where('stripe_event_id', $eventId)
            ->exists();
    }

    private function markAsProcessed(string $eventId, string $type): void
    {
        DB::table('webhook_events')->insertOrIgnore([
            'stripe_event_id' => $eventId,
            'type'            => $type,
            'processed_at'    => now(),
        ]);
    }

    private function translateFailReason(?string $code): string
    {
        return match ($code) {
            'insufficient_funds'       => 'カード残高不足',
            'card_declined'            => 'カードが拒否されました',
            'expired_card'             => 'カードの有効期限切れ',
            'incorrect_cvc'            => 'セキュリティコードが不正',
            'processing_error'         => '処理エラー（しばらく後に再試行されます）',
            default                    => '決済処理エラー',
        };
    }
}
