<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use App\Models\User;

class WebhookController extends CashierWebhookController
{
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        parent::handleCustomerSubscriptionUpdated($payload);
        $this->syncUserPlan($payload);
    }

    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        parent::handleCustomerSubscriptionDeleted($payload);

        $stripeId = $payload['data']['object']['customer'];
        User::where('stripe_id', $stripeId)->update(['plan' => 'free']);
    }

    private function syncUserPlan(array $payload): void
    {
        $stripeId = $payload['data']['object']['customer'];
        $priceId  = $payload['data']['object']['items']['data'][0]['price']['id'] ?? null;
        $status   = $payload['data']['object']['status'];

        if (!$stripeId || $status !== 'active') return;

        $plan = match($priceId) {
            config('services.stripe.standard_price_id') => 'standard',
            config('services.stripe.pro_price_id')      => 'pro',
            default => 'free',
        };

        User::where('stripe_id', $stripeId)->update(['plan' => $plan]);
    }
}
