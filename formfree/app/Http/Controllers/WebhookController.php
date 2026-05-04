<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('stripe.webhook_secret');

        // 署名検証
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error', ['error' => $e->getMessage()]);
            return response('Webhook error', 400);
        }

        Log::info('Stripe webhook received', ['type' => $event->type]);

        match ($event->type) {
            'checkout.session.completed'       => $this->handleCheckoutCompleted($event),
            'customer.subscription.created'    => $this->handleSubscriptionCreated($event),
            'customer.subscription.updated'    => $this->handleSubscriptionUpdated($event),
            'customer.subscription.deleted'    => $this->handleSubscriptionDeleted($event),
            default                            => null,
        };

        return response('OK', 200);
    }

    private function handleCheckoutCompleted(Event $event): void
    {
        $session   = $event->data->object;
        $companyId = $session->metadata->company_id ?? null;
        $plan      = $session->metadata->plan ?? null;
        $customerId = $session->customer ?? null;

        if (!$companyId || !$plan) return;

        Company::where('id', $companyId)->update([
            'plan'               => $plan,
            'monthly_job_limit'  => $this->jobLimit($plan),
            'stripe_customer_id' => $customerId,
        ]);

        Log::info('Plan updated via checkout', ['company' => $companyId, 'plan' => $plan]);
    }

    private function handleSubscriptionCreated(Event $event): void
    {
        $this->syncSubscription($event->data->object);
    }

    private function handleSubscriptionUpdated(Event $event): void
    {
        $this->syncSubscription($event->data->object);
    }

    private function handleSubscriptionDeleted(Event $event): void
    {
        $stripeId = $event->data->object->customer;

        Company::where('stripe_customer_id', $stripeId)->update([
            'plan'              => 'free',
            'monthly_job_limit' => 10,
        ]);

        Log::info('Plan downgraded to free', ['stripe_customer' => $stripeId]);
    }

    private function syncSubscription(object $subscription): void
    {
        $stripeId = $subscription->customer;
        $priceId  = $subscription->items->data[0]->price->id ?? null;
        $status   = $subscription->status;

        if (!$stripeId || $status !== 'active') return;

        $standardPriceId = config('stripe.prices.standard');
        $proPriceId      = config('stripe.prices.pro');

        $plan = match ($priceId) {
            $standardPriceId => 'standard',
            $proPriceId      => 'pro',
            default          => null,
        };

        if (!$plan) return;

        Company::where('stripe_customer_id', $stripeId)->update([
            'plan'              => $plan,
            'monthly_job_limit' => $this->jobLimit($plan),
        ]);

        Log::info('Subscription synced', ['stripe_customer' => $stripeId, 'plan' => $plan]);
    }

    private function jobLimit(string $plan): int
    {
        return match ($plan) {
            'standard' => 200,
            'pro'      => 999999,
            default    => 10,
        };
    }
}
