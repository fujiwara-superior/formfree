<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Stripe\StripeClient;

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

        $stripe = new StripeClient(config('stripe.secret'));

        $params = [
            'mode'                       => 'subscription',
            'line_items'                 => [['price' => $priceId, 'quantity' => 1]],
            'success_url'                => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'                 => route('billing.index'),
            'metadata'                   => ['company_id' => $company->id, 'plan' => $request->plan],
            'payment_method_types'       => ['card'],
            'locale'                     => 'ja',
            'billing_address_collection' => 'required',
        ];

        // 既存の Stripe Customer があれば再利用（なければメールで新規作成）
        if ($company->stripe_customer_id) {
            $params['customer'] = $company->stripe_customer_id;
        } else {
            $params['customer_email'] = $company->email;
        }

        $session = $stripe->checkout->sessions->create($params);

        return response()->json(['checkout_url' => $session->url]);
    }

    public function success(Request $request)
    {
        // Stripe Checkout 完了後に session_id 経由で plan を即時更新
        if ($request->filled('session_id')) {
            try {
                $stripe  = new StripeClient(config('stripe.secret'));
                $session = $stripe->checkout->sessions->retrieve($request->session_id);

                $companyId  = $session->metadata->company_id ?? null;
                $plan       = $session->metadata->plan ?? null;
                $customerId = $session->customer ?? null;

                if ($companyId && $plan) {
                    $limits = ['standard' => 200, 'pro' => 999999];
                    Company::where('id', $companyId)->update([
                        'plan'               => $plan,
                        'monthly_job_limit'  => $limits[$plan] ?? 10,
                        'stripe_customer_id' => $customerId,
                    ]);
                }
            } catch (\Throwable $e) {
                // Webhook がバックアップとして plan を更新する
            }
        }

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
        $stripe  = new StripeClient(config('stripe.secret'));

        $session = $stripe->billingPortal->sessions->create([
            'customer'   => $company->stripe_customer_id,
            'return_url' => route('billing.index'),
        ]);

        return redirect($session->url);
    }
}
