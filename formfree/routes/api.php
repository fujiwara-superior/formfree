<?php
// routes/api.php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Stripe Webhook（CSRF不要・認証不要・Cashier経由）
Route::post('/stripe/webhook', [App\Http\Controllers\WebhookController::class, 'handleWebhook']);

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
        'rows'      => 'nullable|array',
        'error'     => 'nullable|string',
    ]);

    $job = \Illuminate\Support\Facades\DB::table('conversion_jobs')
        ->where('id', $request->job_id)
        ->first();

    if (!$job) {
        return response()->json(['error' => 'Job not found'], 404);
    }

    // jobステータスをSQLiteに反映（ポーリングで参照される）
    $update = [
        'status'     => $request->status,
        'updated_at' => now(),
    ];
    if ($request->status === 'completed') {
        $update['row_count']    = $request->row_count ?? 0;
        $update['completed_at'] = now();
    }
    if ($request->status === 'failed') {
        $update['error_message'] = $request->error ? substr($request->error, 0, 500) : null;
    }
    \Illuminate\Support\Facades\DB::table('conversion_jobs')
        ->where('id', $request->job_id)
        ->update($update);

    // conversion_rowsをSQLiteに保存
    if ($request->status === 'completed' && $request->rows) {
        $inserts = [];
        foreach ($request->rows as $i => $row) {
            $inserts[] = [
                'id'        => (string) \Illuminate\Support\Str::uuid(),
                'job_id'    => $request->job_id,
                'row_index' => $i,
                'data'      => json_encode($row, JSON_UNESCAPED_UNICODE),
                'is_edited' => 0,
            ];
        }
        if ($inserts) {
            \Illuminate\Support\Facades\DB::table('conversion_rows')->insert($inserts);
        }

        // 自動検出モード（columns_snapshotが空）の場合、最初の行のキーからカラム定義を生成
        $existingColumns = json_decode($job->columns_snapshot ?? '[]', true);
        if (empty($existingColumns) && !empty($request->rows[0])) {
            $autoColumns = array_map(
                fn($key) => ['name' => $key, 'description' => ''],
                array_keys((array) $request->rows[0])
            );
            \Illuminate\Support\Facades\DB::table('conversion_jobs')
                ->where('id', $request->job_id)
                ->update(['columns_snapshot' => json_encode($autoColumns, JSON_UNESCAPED_UNICODE)]);
        }
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
