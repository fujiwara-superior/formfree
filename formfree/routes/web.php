<?php
// ============================================================
// routes/web.php
// ============================================================

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ConversionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ─── 認証不要 ───────────────────────────────────────────────
Route::get('/',  fn() => view('welcome'));
Route::get('/billing/success', [BillingController::class, 'success'])->name('billing.success');
Route::get('/billing/cancel',  [BillingController::class, 'cancel'])->name('billing.cancel');

// ─── 認証が必要なルート ──────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 変換
    Route::prefix('conversion')->name('conversion.')->group(function () {
        Route::get('/',                              [ConversionController::class, 'index'])   ->name('index');
        Route::post('/upload',                       [ConversionController::class, 'upload'])  ->name('upload');
        Route::get('/{jobId}/status',                [ConversionController::class, 'status'])  ->name('status');
        Route::get('/{jobId}/preview',               [ConversionController::class, 'preview']) ->name('preview');
        Route::put('/{jobId}/rows/{rowIndex}',       [ConversionController::class, 'updateRow'])->name('update-row');
        Route::get('/{jobId}/download',              [ConversionController::class, 'download'])->name('download');
        Route::get('/history',                       [ConversionController::class, 'history']) ->name('history');
    });

    // 出力定義
    Route::prefix('definitions')->name('definitions.')->group(function () {
        Route::get('/',          [OutputDefinitionController::class, 'index'])  ->name('index');
        Route::post('/',         [OutputDefinitionController::class, 'store'])  ->name('store');
        Route::put('/{id}',      [OutputDefinitionController::class, 'update']) ->name('update');
        Route::delete('/{id}',   [OutputDefinitionController::class, 'destroy'])->name('destroy');
    });

    // 課金
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/',          [BillingController::class, 'index'])                 ->name('index');
        Route::post('/checkout', [BillingController::class, 'createCheckoutSession']) ->name('checkout');
        Route::get('/portal',    [BillingController::class, 'portal'])                ->name('portal');
    });
});

// ─── 認証ルート（Laravel Breeze） ───────────────────────────
require __DIR__ . '/auth.php';
