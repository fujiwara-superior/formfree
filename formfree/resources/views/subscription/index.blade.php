@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-12 px-4">
  <h1 class="text-2xl font-bold text-gray-800 mb-2">プランを選択</h1>
  <p class="text-gray-500 mb-8">現在のプラン: <strong>{{ ucfirst($user->plan) }}</strong></p>

  @if(session('success'))
    <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-6">{{ session('success') }}</div>
  @endif

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Standard --}}
    <div class="border-2 {{ $user->plan === 'standard' ? 'border-blue-500' : 'border-gray-200' }} rounded-xl p-6">
      <div class="text-sm font-bold text-blue-600 mb-1">STANDARD</div>
      <div class="text-3xl font-black text-gray-800 mb-1">¥3,980<span class="text-base font-normal text-gray-400">/月</span></div>
      <ul class="text-sm text-gray-600 space-y-2 my-4">
        <li>✅ 月200件まで変換</li>
        <li>✅ 出力定義 無制限</li>
        <li>✅ 変換履歴 90日間</li>
        <li>✅ メールサポート</li>
      </ul>
      @if($user->plan === 'standard')
        <div class="text-center text-blue-600 font-bold py-2">現在のプラン</div>
        <form method="POST" action="{{ route('subscription.cancel') }}">
          @csrf
          <button class="w-full text-sm text-red-500 underline mt-2">解約する</button>
        </form>
      @else
        <form method="POST" action="{{ route('subscription.checkout') }}">
          @csrf
          <input type="hidden" name="plan" value="standard">
          <button class="w-full bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700">
            このプランにする
          </button>
        </form>
      @endif
    </div>

    {{-- Pro --}}
    <div class="border-2 {{ $user->plan === 'pro' ? 'border-indigo-500' : 'border-gray-200' }} rounded-xl p-6">
      <div class="text-sm font-bold text-indigo-600 mb-1">PRO</div>
      <div class="text-3xl font-black text-gray-800 mb-1">¥9,800<span class="text-base font-normal text-gray-400">/月</span></div>
      <ul class="text-sm text-gray-600 space-y-2 my-4">
        <li>✅ 月間変換 無制限</li>
        <li>✅ 出力定義 無制限</li>
        <li>✅ 変換履歴 無制限</li>
        <li>✅ API連携対応</li>
        <li>✅ 優先サポート</li>
      </ul>
      @if($user->plan === 'pro')
        <div class="text-center text-indigo-600 font-bold py-2">現在のプラン</div>
        <form method="POST" action="{{ route('subscription.cancel') }}">
          @csrf
          <button class="w-full text-sm text-red-500 underline mt-2">解約する</button>
        </form>
      @else
        <form method="POST" action="{{ route('subscription.checkout') }}">
          @csrf
          <input type="hidden" name="plan" value="pro">
          <button class="w-full bg-indigo-600 text-white font-bold py-2 rounded-lg hover:bg-indigo-700">
            このプランにする
          </button>
        </form>
      @endif
    </div>

  </div>

  <p class="text-center text-sm text-gray-400 mt-8">
    <a href="{{ route('dashboard') }}" class="underline">ダッシュボードに戻る</a>
  </p>
</div>
@endsection
