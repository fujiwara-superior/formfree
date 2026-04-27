{{-- resources/views/billing/success.blade.php --}}
@extends('layouts.guest')
@section('title', 'アップグレード完了 — FormFree')

@section('content')
<div class="text-center">
  <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
    <svg width="24" height="24" viewBox="0 0 16 16" fill="none">
      <path d="M3 8l3.5 3.5L13 4" stroke="#059669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </div>
  <h1 class="text-lg font-medium text-gray-900 mb-2">アップグレード完了</h1>
  <p class="text-sm text-gray-500 mb-6 leading-relaxed">
    プランのアップグレードが完了しました。<br>
    すぐにご利用いただけます。
  </p>
  <a href="{{ route('dashboard') }}"
     class="block w-full bg-blue-600 text-white font-medium py-2.5 rounded-lg text-sm
            hover:bg-blue-700 text-center">
    ダッシュボードへ
  </a>
  <p class="text-xs text-gray-400 mt-4">
    確認メールをお送りしました。<br>
    請求書は
    <a href="{{ route('billing.portal') }}" class="text-blue-600 hover:underline">
      お支払い管理ページ
    </a>
    からダウンロードできます。
  </p>
</div>
@endsection
