{{-- resources/views/auth/forgot-password.blade.php --}}
@extends('layouts.guest')
@section('title', 'パスワードリセット — FormFree')

@section('content')
<h1 class="text-lg font-medium text-gray-900 mb-2">パスワードのリセット</h1>
<p class="text-sm text-gray-500 mb-6">
  登録済みのメールアドレスを入力してください。パスワードリセットのリンクをお送りします。
</p>

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 mb-5 text-sm text-green-700">
  {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-5 text-sm text-red-700">
  {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
  @csrf
  <div>
    <label class="block text-xs font-medium text-gray-700 mb-1.5">メールアドレス</label>
    <input type="email" name="email" value="{{ old('email') }}"
           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
           placeholder="you@company.com" required autofocus>
  </div>
  <button type="submit"
          class="w-full bg-blue-600 text-white font-medium py-2.5 rounded-lg text-sm
                 hover:bg-blue-700 transition-colors">
    リセットリンクを送信
  </button>
</form>

<div class="mt-5 text-center">
  <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">
    ← ログインに戻る
  </a>
</div>
@endsection
