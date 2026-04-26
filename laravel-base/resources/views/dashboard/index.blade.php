{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')
@section('title', 'ダッシュボード')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

  {{-- 統計カード --}}
  <div class="grid grid-cols-3 gap-4 mb-8">
    <div class="bg-gray-50 rounded-lg p-4">
      <p class="text-xs text-gray-500 mb-1">今月の変換数</p>
      <p class="text-3xl font-medium text-gray-900">{{ $usedCount }}</p>
      <p class="text-xs text-gray-400 mt-1">上限 {{ $company->monthly_job_limit }}件</p>
    </div>
    <div class="bg-gray-50 rounded-lg p-4">
      <p class="text-xs text-gray-500 mb-1">変換成功率</p>
      <p class="text-3xl font-medium text-gray-900">{{ $successRate }}%</p>
      <p class="text-xs text-gray-400 mt-1">今月実績</p>
    </div>
    <div class="bg-gray-50 rounded-lg p-4">
      <p class="text-xs text-gray-500 mb-1">保存済み定義</p>
      <p class="text-3xl font-medium text-gray-900">{{ $definitionCount }}</p>
      <p class="text-xs text-gray-400 mt-1">テンプレート数</p>
    </div>
  </div>

  {{-- 無料枠プログレスバー --}}
  @if($company->plan === 'free')
  <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6">
    <div class="flex justify-between items-center mb-2">
      <span class="text-sm font-medium text-gray-700">今月の使用状況（無料プラン）</span>
      <span class="text-sm text-gray-500">{{ $usedCount }} / {{ $company->monthly_job_limit }} 件</span>
    </div>
    @php $pct = min(100, $usedCount / $company->monthly_job_limit * 100); @endphp
    <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
      <div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-yellow-500' : 'bg-blue-500') }}"
           style="width: {{ $pct }}%"></div>
    </div>
    @if($pct >= 80)
    <div class="mt-3 flex items-center justify-between">
      <span class="text-xs text-yellow-700">プランをアップグレードすると月100件まで変換できます</span>
      <a href="{{ route('billing.index') }}"
         class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700">
        アップグレード
      </a>
    </div>
    @endif
  </div>
  @endif

  {{-- 最近の変換 --}}
  <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100">
      <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wide">最近の変換</h2>
    </div>
    <div class="divide-y divide-gray-100">
      @forelse($recentJobs as $job)
      <div class="flex items-center px-5 py-3 gap-3">
        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
            <path d="M4 2h6l4 4v8a1 1 0 01-1 1H4a1 1 0 01-1-1V3a1 1 0 011-1z" stroke="#2563eb" stroke-width="1.2"/>
            <path d="M10 2v4h4" stroke="#2563eb" stroke-width="1.2"/>
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm text-gray-900 truncate">{{ $job->pdf_filename }}</p>
          <p class="text-xs text-gray-400">
            {{ $job->row_count ? $job->row_count . '行抽出 · ' : '' }}
            {{ \Carbon\Carbon::parse($job->created_at)->diffForHumans() }}
          </p>
        </div>
        @php
          $badgeClass = match($job->status) {
            'completed'  => 'bg-green-100 text-green-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'failed'     => 'bg-red-100 text-red-800',
            default      => 'bg-gray-100 text-gray-600',
          };
          $badgeLabel = match($job->status) {
            'completed'  => '完了',
            'processing' => '処理中',
            'failed'     => '失敗',
            default      => '待機中',
          };
        @endphp
        <span class="text-xs px-2 py-1 rounded-full {{ $badgeClass }}">{{ $badgeLabel }}</span>
        @if($job->status === 'completed')
        <a href="{{ route('conversion.preview', $job->id) }}"
           class="text-xs text-blue-600 hover:underline">確認</a>
        @endif
      </div>
      @empty
      <div class="px-5 py-8 text-center text-sm text-gray-400">
        まだ変換履歴がありません。最初のPDFを変換してみましょう。
      </div>
      @endforelse
    </div>
  </div>

  {{-- クイックアクション --}}
  <div class="flex gap-3">
    <a href="{{ route('conversion.index') }}"
       class="flex-1 bg-blue-600 text-white text-sm font-medium py-3 rounded-lg text-center hover:bg-blue-700">
      PDFを変換する
    </a>
    <a href="{{ route('definitions.index') }}"
       class="flex-1 border border-gray-200 text-sm text-gray-700 py-3 rounded-lg text-center hover:bg-gray-50">
      出力定義を管理
    </a>
    <a href="{{ route('conversion.history') }}"
       class="flex-1 border border-gray-200 text-sm text-gray-700 py-3 rounded-lg text-center hover:bg-gray-50">
      変換履歴
    </a>
  </div>

</div>
@endsection
