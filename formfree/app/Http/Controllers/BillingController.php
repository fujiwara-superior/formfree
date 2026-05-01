<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
