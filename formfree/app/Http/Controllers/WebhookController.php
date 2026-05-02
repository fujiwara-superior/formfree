<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends CashierWebhookController
{
    // checkout.session.completed: metadata に company_id / plan を含む
    protected function handleCheckoutSessionCompleted(array $payload): Response
    {
        $session    = $payload['data']['object'];
        $companyId  = $session['metadata']['company_id'] ?? null;
        $plan       = $session['metadata']['plan'] ?? null;
        $customerId = $session['customer'] ?? null;

        if ($companyId && $plan) {
            Company::where('id', $companyId)->update([
                'plan'               => $plan,
                'monthly_job_limit'  => $this->jobLimit($plan),
                'stripe_customer_id' => $customerId,
            ]);
        }

        return $this->successMethod();
    }

    // 新規サブスクリプション作成時
    protected function handleCustomerSubscriptionCreated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionCreated($payload);
        $this->syncCompanyPlan($payload);
        return $response;
    }

    // プラン変更・更新時
    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionUpdated($payload);
        $this->syncCompanyPlan($payload);
        return $response;
    }

    // 解約時
    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $stripeId = $payload['data']['object']['customer'];
        Company::where('stripe_customer_id', $stripeId)->update([
            'plan'              => 'free',
            'monthly_job_limit' => 10,
        ]);

        return $response;
    }

    private function syncCompanyPlan(array $payload): void
    {
        $stripeId = $payload['data']['object']['customer'];
        $priceId  = $payload['data']['object']['items']['data'][0]['price']['id'] ?? null;
        $status   = $payload['data']['object']['status'];

        if (!$stripeId || $status !== 'active') return;

        $standardPriceId = config('stripe.prices.standard');
        $proPriceId      = config('stripe.prices.pro');

        if ($priceId === $standardPriceId) {
            $plan = 'standard';
        } elseif ($priceId === $proPriceId) {
            $plan = 'pro';
        } else {
            return;
        }

        Company::where('stripe_customer_id', $stripeId)->update([
            'plan'              => $plan,
            'monthly_job_limit' => $this->jobLimit($plan),
        ]);
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
