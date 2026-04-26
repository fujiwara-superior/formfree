{{-- resources/views/conversion/history.blade.php --}}
@extends('layouts.app')
@section('title', '変換履歴')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

  <div class="flex items-center justify-between mb-6">
    <h1 class="text-base font-medium text-gray-900">変換履歴</h1>
    <a href="{{ route('conversion.index') }}"
       class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
      + 新規変換
    </a>
  </div>

  {{-- フィルター --}}
  <form method="GET" action="{{ route('conversion.history') }}"
        class="flex gap-3 mb-5">
    <select name="status" onchange="this.form.submit()"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="" {{ !request('status') ? 'selected' : '' }}>すべての状態</option>
      <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>完了</option>
      <option value="failed"    {{ request('status') === 'failed'    ? 'selected' : '' }}>失敗</option>
      <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>処理中</option>
    </select>
    <select name="definition_id" onchange="this.form.submit()"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="">すべての定義</option>
      @foreach($definitions as $def)
      <option value="{{ $def->id }}"
              {{ request('definition_id') === $def->id ? 'selected' : '' }}>
        {{ $def->name }}
      </option>
      @endforeach
    </select>
  </form>

  {{-- テーブル --}}
  @if($jobs->isEmpty())
  <div class="bg-white border border-gray-200 rounded-xl p-16 text-center">
    <p class="text-sm text-gray-500">該当する変換履歴がありません</p>
  </div>
  @else
  <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-gray-50 border-b border-gray-200">
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">ファイル名</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 w-32">定義</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 w-16">行数</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 w-20">状態</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 w-28">日時</th>
          <th class="px-4 py-3 w-24"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($jobs as $job)
        @php
          $defName = $definitions->firstWhere('id', $job->output_definition_id)?->name ?? '手動設定';
          $badgeClass = match($job->status) {
            'completed'  => 'bg-green-100 text-green-800',
            'failed'     => 'bg-red-100 text-red-800',
            'processing' => 'bg-blue-100 text-blue-800',
            default      => 'bg-gray-100 text-gray-500',
          };
          $badgeLabel = match($job->status) {
            'completed'  => '完了',
            'failed'     => '失敗',
            'processing' => '処理中',
            default      => '待機中',
          };
        @endphp
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 text-gray-900 max-w-xs">
            <span class="truncate block" title="{{ $job->pdf_filename }}">
              {{ $job->pdf_filename }}
            </span>
          </td>
          <td class="px-4 py-3 text-gray-500 text-xs">{{ $defName }}</td>
          <td class="px-4 py-3 text-gray-700">
            {{ $job->row_count ? $job->row_count . '行' : '—' }}
          </td>
          <td class="px-4 py-3">
            <span class="text-xs px-2 py-1 rounded-full {{ $badgeClass }}">{{ $badgeLabel }}</span>
          </td>
          <td class="px-4 py-3 text-gray-400 text-xs">
            {{ \Carbon\Carbon::parse($job->created_at)->format('m/d H:i') }}
          </td>
          <td class="px-4 py-3">
            <div class="flex gap-2">
              @if($job->status === 'completed')
              <a href="{{ route('conversion.preview', $job->id) }}"
                 class="text-xs text-blue-600 hover:underline">確認</a>
              <a href="{{ route('conversion.download', $job->id) }}"
                 class="text-xs text-gray-500 hover:underline">再DL</a>
              @elseif($job->status === 'failed')
              <a href="{{ route('conversion.index') }}"
                 class="text-xs text-orange-600 hover:underline">再試行</a>
              @endif
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    {{-- ページネーション --}}
    @if($jobs->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">
      {{ $jobs->withQueryString()->links() }}
    </div>
    @endif
  </div>
  @endif

</div>
@endsection
