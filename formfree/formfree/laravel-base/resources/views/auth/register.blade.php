{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.guest')
@section('title', '新規登録（無料） — FormFree')

@section('content')
<h1 class="text-lg font-medium text-gray-900 mb-1">無料で始める</h1>
<p class="text-sm text-gray-500 mb-6">月10件まで無料。クレジットカード不要。</p>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-5 text-sm text-red-700">
  <ul class="space-y-0.5">
    @foreach($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<form method="POST" action="{{ route('register') }}" class="space-y-4">
  @csrf

  {{-- 会社名 --}}
  <div>
    <label class="block text-xs font-medium text-gray-700 mb-1.5">会社名</label>
    <input type="text" name="company_name" value="{{ old('company_name') }}"
           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                  @error('company_name') border-red-300 @enderror"
           placeholder="株式会社○○" required>
  </div>

  {{-- 担当者名 --}}
  <div>
    <label class="block text-xs font-medium text-gray-700 mb-1.5">担当者名</label>
    <input type="text" name="name" value="{{ old('name') }}"
           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                  @error('name') border-red-300 @enderror"
           placeholder="山田 浩章" required>
  </div>

  {{-- メールアドレス --}}
  <div>
    <label class="block text-xs font-medium text-gray-700 mb-1.5">メールアドレス</label>
    <input type="email" name="email" value="{{ old('email') }}"
           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                  @error('email') border-red-300 @enderror"
           placeholder="you@company.com" required>
  </div>

  {{-- パスワード --}}
  <div>
    <label class="block text-xs font-medium text-gray-700 mb-1.5">パスワード</label>
    <input type="password" name="password"
           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                  @error('password') border-red-300 @enderror"
           placeholder="8文字以上" required>
  </div>

  {{-- パスワード確認 --}}
  <div>
    <label class="block text-xs font-medium text-gray-700 mb-1.5">パスワード（確認）</label>
    <input type="password" name="password_confirmation"
           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
           placeholder="••••••••" required>
  </div>

  {{-- 利用規約同意 --}}
  <div class="flex items-start gap-2.5 pt-1">
    <input type="checkbox" name="agree" id="agree"
           class="w-4 h-4 mt-0.5 rounded border-gray-300 text-blue-600 flex-shrink-0
                  @error('agree') border-red-300 @enderror">
    <label for="agree" class="text-sm text-gray-600 cursor-pointer leading-snug">
      <a href="/terms" class="text-blue-600 hover:underline" target="_blank">利用規約</a>
      および
      <a href="/privacy" class="text-blue-600 hover:underline" target="_blank">プライバシーポリシー</a>
      に同意します
    </label>
  </div>

  <button type="submit"
          class="w-full bg-blue-600 text-white font-medium py-2.5 rounded-lg text-sm
                 hover:bg-blue-700 transition-colors mt-1">
    無料アカウントを作成
  </button>
</form>

{{-- 特徴 --}}
<div class="mt-5 pt-5 border-t border-gray-100">
  <ul class="space-y-2">
    <li class="flex items-center gap-2 text-xs text-gray-500">
      <span class="text-green-500">✓</span> 月10件まで無料・クレジットカード不要
    </li>
    <li class="flex items-center gap-2 text-xs text-gray-500">
      <span class="text-green-500">✓</span> テンプレート設定なし・フォーマット変更も自動対応
    </li>
    <li class="flex items-center gap-2 text-xs text-gray-500">
      <span class="text-green-500">✓</span> Shift-JIS出力対応・基幹システムにそのまま取込可能
    </li>
  </ul>
</div>

<div class="mt-4 text-center">
  <p class="text-sm text-gray-500">
    すでにアカウントをお持ちの方は
    <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">
      ログイン
    </a>
  </p>
</div>
@endsection
