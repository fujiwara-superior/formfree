<?php
// routes/auth.php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// ─── ゲストのみアクセス可 ────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register',       [AuthController::class, 'showRegister'])    ->name('register');
    Route::post('/register',      [AuthController::class, 'register']);

    Route::get('/login',          [AuthController::class, 'showLogin'])       ->name('login');
    Route::post('/login',         [AuthController::class, 'login']);

    Route::get('/forgot-password',[AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password',[AuthController::class, 'sendResetLink'])    ->name('password.email');
});

// ─── 認証済みのみ ────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
