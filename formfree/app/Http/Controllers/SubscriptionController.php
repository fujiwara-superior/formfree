<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index()
    {
        return view('subscription.index', [
            'user' => Auth::user(),
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:standard,pro',
        ]);

        $user    = Auth::user();
        $priceId = $request->plan === 'pro'
            ? config('services.stripe.pro_price_id')
            : config('services.stripe.standard_price_id');

        $checkout = $user->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('subscription.index'),
                'locale'      => 'ja',
            ]);

        return redirect($checkout->url);
    }

    public function success(Request $request)
    {
        return redirect()->route('dashboard')
            ->with('success', 'プランのアップグレードが完了しました！');
    }

    public function cancel()
    {
        $user = Auth::user();

        if ($user->subscribed('default')) {
            $user->subscription('default')->cancel();
        }

        return redirect()->route('dashboard')
            ->with('success', 'サブスクリプションを解約しました。期末まで引き続きご利用いただけます。');
    }
}
