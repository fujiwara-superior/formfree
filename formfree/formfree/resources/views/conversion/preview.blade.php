{{-- resources/views/conversion/preview.blade.php --}}
@extends('layouts.app')
@section('title', 'プレビュー・修正')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

  {{-- ヘッダー --}}
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-base font-medium text-gray-900">{{ $job->pdf_filename }}</h1>
      <p class="text-xs text-gray-400 mt-0.5">{{ $rows->count() }}行抽出 · {{ strtoupper($job->csv_encoding) }}</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('conversion.index') }}"
         class="px-4 py-2 border border-gray-200 text-sm rounded-lg hover:bg-gray-50">← 再変換</a>
      <button onclick="downloadCsv()"
              class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
        CSVをダウンロード
      </button>
    </div>
  </div>

  {{-- 警告バナー --}}
  @php $editedCount = $rows->where('is_edited', true)->count(); @endphp
  @if($editedCount > 0)
  <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 mb-4 flex items-center gap-2 text-sm text-yellow-800">
    <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
      <path d="M8 1L1 14h14L8 1z" stroke="#d97706" stroke-width="1.2"/>
      <path d="M8 6v4M8 11v1" stroke="#d97706" stroke-width="1.2" stroke-linecap="round"/>
    </svg>
    {{ $editedCount }}行でAIが低確信度で読み取りました。黄色のセルをご確認ください。
  </div>
  @endif

  {{-- プレビューテーブル --}}
  <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-5">
    <div class="overflow-x-auto">
      <table class="w-full text-sm" id="preview-table">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-200">
            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 w-10">#</th>
            @foreach($columns as $col)
            <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500">{{ $col['name'] }}</th>
            @endforeach
            <th class="px-3 py-2.5 w-8"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($rows as $row)
          @php $data = json_decode($row->data, true); @endphp
          <tr class="{{ $row->is_edited ? 'bg-yellow-50' : '' }} hover:bg-gray-50 group"
              data-job-id="{{ $job->id }}"
              data-row-index="{{ $row->row_index }}">
            <td class="px-3 py-2 text-xs text-gray-400">{{ $row->row_index + 1 }}</td>
            @foreach($columns as $col)
            @php $val = $data[$col['name']] ?? ''; @endphp
            <td class="px-3 py-2 text-gray-900"
                data-col="{{ $col['name'] }}"
                ondblclick="startEdit(this)">
              {{ $val !== null ? $val : '─' }}
            </td>
            @endforeach
            <td class="px-2 py-2">
              <button onclick="startEditRow(this.closest('tr'))"
                      class="text-gray-300 hover:text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity"
                      title="編集">✏</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- 定義保存オプション --}}
  @if(!$job->output_definition_id)
  <div class="flex items-center gap-3 bg-blue-50 rounded-lg px-4 py-3 mb-5 text-sm">
    <label class="flex items-center gap-2 text-blue-800 cursor-pointer">
      <input type="checkbox" id="save-def-check">
      この設定を定義として保存する
    </label>
    <input type="text" id="save-def-name" placeholder="例：A社発注書 → 受注CSV"
           class="flex-1 border border-blue-200 rounded px-2 py-1 text-sm bg-white hidden">
    <button id="save-def-btn" class="hidden bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">
      保存
    </button>
  </div>
  @endif

  {{-- ダウンロードボタン（下部） --}}
  <div class="flex justify-end">
    <button onclick="downloadCsv()"
            class="px-8 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
      CSV ダウンロード（{{ strtoupper($job->csv_encoding) }}）
    </button>
  </div>
</div>

@push('scripts')
<script>
function downloadCsv() {
  window.location.href = '/conversion/{{ $job->id }}/download';
}

// セルのダブルクリック編集
function startEdit(td) {
  if (td.querySelector('input')) return;
  const original = td.textContent.trim();
  const colName  = td.dataset.col;
  td.innerHTML   = `<input type="text" value="${original}"
    class="w-full border-0 bg-yellow-100 text-sm focus:ring-1 focus:ring-blue-400 rounded px-1"
    onblur="saveCell(this,'${colName}')"
    onkeydown="if(event.key==='Enter')this.blur();if(event.key==='Escape'){this.closest('td').textContent='${original}'}">`;
  td.querySelector('input').focus();
}

async function saveCell(input, colName) {
  const tr       = input.closest('tr');
  const jobId    = tr.dataset.jobId;
  const rowIndex = parseInt(tr.dataset.rowIndex);
  const newVal   = input.value;

  // 現在の行全体のデータを収集
  const rowData = {};
  tr.querySelectorAll('td[data-col]').forEach(td => {
    const col = td.dataset.col;
    const inp = td.querySelector('input');
    rowData[col] = inp ? inp.value : td.textContent.trim();
  });
  rowData[colName] = newVal;

  // サーバーに保存
  await fetch(`/conversion/${jobId}/rows/${rowIndex}`, {
    method:  'PUT',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({ data: rowData }),
  });

  input.closest('td').textContent = newVal;
  tr.classList.add('bg-yellow-50');
}

// 定義保存UI
const saveCheck = document.getElementById('save-def-check');
if (saveCheck) {
  saveCheck.addEventListener('change', function() {
    document.getElementById('save-def-name').classList.toggle('hidden', !this.checked);
    document.getElementById('save-def-btn').classList.toggle('hidden', !this.checked);
  });
}
</script>
@endpush
@endsection
