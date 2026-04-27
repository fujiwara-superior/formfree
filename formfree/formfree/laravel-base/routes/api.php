<?php
// routes/api.php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Stripe Webhook（CSRF不要・認証不要）
Route::post('/stripe/webhook', [WebhookController::class, 'handle'])
    ->name('stripe.webhook');

// Python → Laravel 変換完了通知（内部API）
Route::post('/internal/job-completed', function (\Illuminate\Http\Request $request) {
    $secret = $request->header('X-Api-Secret');
    if ($secret !== config('services.python_converter.secret')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $request->validate([
        'job_id'    => 'required|uuid',
        'status'    => 'required|in:completed,failed',
        'row_count' => 'nullable|integer',
        'error'     => 'nullable|string',
    ]);

    $job = \Illuminate\Support\Facades\DB::table('conversion_jobs')
        ->where('id', $request->job_id)
        ->first();

    if (!$job) {
        return response()->json(['error' => 'Job not found'], 404);
    }

    // 変換完了メールを送信
    if ($request->status === 'completed') {
        $user        = \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $job->user_id)->first();
        $downloadUrl = route('conversion.download', $request->job_id);

        \Illuminate\Support\Facades\Mail::to($user->email)
            ->queue(new \App\Mail\ConversionCompletedMail($job, $downloadUrl));

        // 無料枠80%チェック
        $usedCount = \Illuminate\Support\Facades\DB::table('conversion_jobs')
            ->where('company_id', $job->company_id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('status', 'completed')
            ->count();

        $company = \Illuminate\Support\Facades\DB::table('companies')
            ->where('id', $job->company_id)->first();

        $warnThreshold = (int) ($company->monthly_job_limit * 0.8);
        if ($usedCount === $warnThreshold && $company->plan === 'free') {
            \Illuminate\Support\Facades\Mail::to($company->email)
                ->queue(new \App\Mail\UsageLimitReachedMail(
                    $company, $usedCount, $company->monthly_job_limit, true
                ));
        }
    }

    return response()->json(['ok' => true]);
})->name('internal.job-completed');
