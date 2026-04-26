{{-- resources/views/conversion/index.blade.php --}}
@extends('layouts.app')
@section('title', '変換する')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">

  <form id="upload-form" enctype="multipart/form-data">
    @csrf

    {{-- PDFアップロードゾーン --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
      <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-4">PDFファイル</h2>

      <div id="drop-zone"
           class="border-2 border-dashed border-gray-300 rounded-lg p-10 text-center cursor-pointer
                  hover:border-blue-400 hover:bg-blue-50 transition-colors"
           onclick="document.getElementById('pdf-input').click()">
        <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg width="20" height="20" viewBox="0 0 16 16" fill="none">
            <path d="M4 2h6l4 4v8a1 1 0 01-1 1H4a1 1 0 01-1-1V3a1 1 0 011-1z" stroke="#2563eb" stroke-width="1.2"/>
            <path d="M10 2v4h4M8 8v4M6 10l2-2 2 2" stroke="#2563eb" stroke-width="1.2" stroke-linecap="round"/>
          </svg>
        </div>
        <p id="drop-label" class="text-sm font-medium text-gray-700 mb-1">PDFをドラッグ＆ドロップ</p>
        <p class="text-xs text-gray-400">またはクリックしてファイルを選択 · 最大20MB</p>
        <input type="file" id="pdf-input" name="pdf" accept=".pdf" class="hidden" required>
      </div>
    </div>

    {{-- 出力定義 --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
      <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-4">出力CSV定義</h2>

      {{-- 保存済み定義 --}}
      @if($definitions->isNotEmpty())
      <div class="mb-4">
        <label class="block text-xs font-medium text-gray-700 mb-1.5">保存済み定義から選択</label>
        <select id="definition-select" name="output_definition_id"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
          <option value="">── 選択してください ──</option>
          @foreach($definitions as $def)
          <option value="{{ $def->id }}" data-columns="{{ $def->columns }}">
            {{ $def->name }}（{{ count(json_decode($def->columns, true)) }}列）
          </option>
          @endforeach
        </select>
      </div>
      <div class="text-center text-xs text-gray-400 my-3">または 列を手動で設定</div>
      @endif

      {{-- 列定義テーブル --}}
      <div id="columns-section">
        <div class="border border-gray-200 rounded-lg overflow-hidden mb-3">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50">
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 w-32">列名</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">抽出ルール（AIへの指示）</th>
                <th class="px-3 py-2 w-8"></th>
              </tr>
            </thead>
            <tbody id="columns-body">
              <tr class="border-t border-gray-100">
                <td class="px-2 py-1.5">
                  <input type="text" name="columns[0][name]" placeholder="例：取引先名"
                         class="w-full border-0 text-sm focus:ring-0 bg-transparent">
                </td>
                <td class="px-2 py-1.5">
                  <input type="text" name="columns[0][description]" placeholder="例：発注元の会社名をそのまま"
                         class="w-full border-0 text-sm focus:ring-0 bg-transparent">
                </td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
        <button type="button" id="add-column"
                class="text-xs text-blue-600 hover:underline">+ 列を追加</button>
      </div>

      {{-- 設定の保存 --}}
      <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-3">
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
          <input type="checkbox" id="save-definition" name="save_definition" class="rounded">
          この定義を保存する
        </label>
        <input type="text" name="definition_name" id="definition-name" placeholder="定義の名前"
               class="flex-1 border border-gray-200 rounded px-2 py-1 text-sm hidden">
      </div>
    </div>

    {{-- オプション --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
      <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">オプション</h2>
      <div class="flex items-center gap-4">
        <label class="text-sm text-gray-700">文字コード</label>
        <label class="flex items-center gap-1.5 text-sm cursor-pointer">
          <input type="radio" name="csv_encoding" value="sjis" checked> Shift-JIS（推奨）
        </label>
        <label class="flex items-center gap-1.5 text-sm cursor-pointer">
          <input type="radio" name="csv_encoding" value="utf8"> UTF-8
        </label>
      </div>
    </div>

    {{-- アクションボタン --}}
    <div class="flex justify-end gap-3">
      <a href="{{ route('dashboard') }}"
         class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
        キャンセル
      </a>
      <button type="submit" id="submit-btn"
              class="px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
        変換を開始
      </button>
    </div>
  </form>

</div>

{{-- 処理中オーバーレイ --}}
<div id="processing-overlay" class="hidden fixed inset-0 bg-white bg-opacity-90 flex items-center justify-center z-50">
  <div class="text-center">
    <div class="w-12 h-12 border-3 border-gray-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"
         style="border-width:3px"></div>
    <p class="text-base font-medium text-gray-800 mb-1">変換処理中...</p>
    <p id="processing-step" class="text-sm text-gray-500">PDFを解析しています</p>
  </div>
</div>

@push('scripts')
<script>
let columnIndex = 1;
const steps = ['PDFを受信しました', 'テキストを抽出中...', 'AIが内容を解析中...', 'CSVを生成中...'];

// ドラッグ＆ドロップ
const dropZone = document.getElementById('drop-zone');
const pdfInput = document.getElementById('pdf-input');

['dragover','dragenter'].forEach(e => dropZone.addEventListener(e, ev => {
  ev.preventDefault();
  dropZone.classList.add('border-blue-400','bg-blue-50');
}));
['dragleave','drop'].forEach(e => dropZone.addEventListener(e, ev => {
  ev.preventDefault();
  dropZone.classList.remove('border-blue-400','bg-blue-50');
}));
dropZone.addEventListener('drop', ev => {
  const file = ev.dataTransfer.files[0];
  if (file?.type === 'application/pdf') setFile(file);
});
pdfInput.addEventListener('change', () => setFile(pdfInput.files[0]));

function setFile(file) {
  document.getElementById('drop-label').textContent = file.name;
}

// 列追加
document.getElementById('add-column').addEventListener('click', () => {
  const tbody = document.getElementById('columns-body');
  const row   = document.createElement('tr');
  row.className = 'border-t border-gray-100';
  row.innerHTML = `
    <td class="px-2 py-1.5">
      <input type="text" name="columns[${columnIndex}][name]" placeholder="列名"
             class="w-full border-0 text-sm focus:ring-0 bg-transparent">
    </td>
    <td class="px-2 py-1.5">
      <input type="text" name="columns[${columnIndex}][description]" placeholder="抽出ルール"
             class="w-full border-0 text-sm focus:ring-0 bg-transparent">
    </td>
    <td class="px-2 py-1.5">
      <button type="button" onclick="this.closest('tr').remove()"
              class="text-gray-300 hover:text-red-400 text-lg leading-none">×</button>
    </td>`;
  tbody.appendChild(row);
  columnIndex++;
});

// 定義保存チェックボックス
document.getElementById('save-definition').addEventListener('change', function() {
  document.getElementById('definition-name').classList.toggle('hidden', !this.checked);
});

// フォーム送信
document.getElementById('upload-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const overlay = document.getElementById('processing-overlay');
  const stepEl  = document.getElementById('processing-step');
  overlay.classList.remove('hidden');

  // ステップ表示
  let si = 0;
  const interval = setInterval(() => {
    if (si < steps.length) stepEl.textContent = steps[si++];
  }, 2000);

  try {
    const formData = new FormData(e.target);
    const res      = await fetch('{{ route("conversion.upload") }}', {
      method: 'POST',
      body:   formData,
    });
    const data = await res.json();
    clearInterval(interval);

    if (!res.ok) {
      overlay.classList.add('hidden');
      if (data.limit_reached) {
        window.location.href = '{{ route("billing.index") }}';
      } else {
        alert(data.error || 'エラーが発生しました');
      }
      return;
    }

    // ポーリング開始
    pollStatus(data.job_id);
  } catch (err) {
    clearInterval(interval);
    overlay.classList.add('hidden');
    alert('通信エラーが発生しました。もう一度お試しください。');
  }
});

// ステータスポーリング
async function pollStatus(jobId) {
  const stepEl = document.getElementById('processing-step');
  const timer  = setInterval(async () => {
    try {
      const res  = await fetch(`/conversion/${jobId}/status`);
      const data = await res.json();

      if (data.status === 'completed') {
        clearInterval(timer);
        stepEl.textContent = '完了しました！プレビュー画面に移動します';
        setTimeout(() => window.location.href = `/conversion/${jobId}/preview`, 800);
      }
      if (data.status === 'failed') {
        clearInterval(timer);
        document.getElementById('processing-overlay').classList.add('hidden');
        alert('変換に失敗しました：' + (data.error || '不明なエラー'));
      }
    } catch {}
  }, 3000);
}
</script>
@endpush
@endsection
