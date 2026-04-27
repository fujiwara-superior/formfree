{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.guest')
@section('title', 'ログイン — FormFree')

@section('content')
<h1 class="text-lg font-medium text-gray-900 mb-6">ログイン</h1>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-5 text-sm text-red-700">
  {{ $errors->first() }}
</div>
@endif

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 mb-5 text-sm text-green-700">
  {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('login') }}" class="space-y-4">
  @csrf

  <div>
    <label class="block text-xs font-medium text-gray-700 mb-1.5">
      メールアドレス
    </label>
    <input type="email" name="email" value="{{ old('email') }}"
           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                  @error('email') border-red-300 @enderror"
           placeholder="you@company.com" required autofocus>
  </div>

  <div>
    <div class="flex justify-between items-center mb-1.5">
      <label class="text-xs font-medium text-gray-700">パスワード</label>
      <a href="{{ route('password.request') }}"
         class="text-xs text-blue-600 hover:underline">
        パスワードをお忘れですか？
      </a>
    </div>
    <input type="password" name="password"
           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
           placeholder="••••••••" required>
  </div>

  <div class="flex items-center gap-2">
    <input type="checkbox" name="remember" id="remember"
           class="w-4 h-4 rounded border-gray-300 text-blue-600">
    <label for="remember" class="text-sm text-gray-600 cursor-pointer">
      ログイン状態を保持する
    </label>
  </div>

  <button type="submit"
          class="w-full bg-blue-600 text-white font-medium py-2.5 rounded-lg text-sm
                 hover:bg-blue-700 transition-colors mt-2">
    ログイン
  </button>
</form>

<div class="mt-6 pt-5 border-t border-gray-100 text-center">
  <p class="text-sm text-gray-500">
    アカウントをお持ちでない方は
    <a href="{{ route('register') }}" class="text-blue-600 hover:underline font-medium">
      無料で登録
    </a>
  </p>
</div>
@endsection
