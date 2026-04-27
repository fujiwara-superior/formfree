{{-- resources/views/definitions/index.blade.php --}}
@extends('layouts.app')
@section('title', '出力定義')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

  {{-- ヘッダー --}}
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-base font-medium text-gray-900">出力定義</h1>
      <p class="text-xs text-gray-400 mt-0.5">よく使うCSV出力パターンを保存・管理します</p>
    </div>
    <button onclick="openCreateModal()"
            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
      + 新規作成
    </button>
  </div>

  @if(session('success'))
  <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg mb-5">
    {{ session('success') }}
  </div>
  @endif

  {{-- 定義カード一覧 --}}
  @if($definitions->isEmpty())
  <div class="bg-white border border-dashed border-gray-300 rounded-xl p-16 text-center">
    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
      <svg width="20" height="20" viewBox="0 0 16 16" fill="none">
        <path d="M2 4h12M2 8h8M2 12h10" stroke="#9ca3af" stroke-width="1.2" stroke-linecap="round"/>
      </svg>
    </div>
    <p class="text-sm font-medium text-gray-700 mb-1">出力定義がまだありません</p>
    <p class="text-xs text-gray-400 mb-4">PDFから取り出したいCSVの列パターンを保存しておくと、次回から一発で使えます</p>
    <button onclick="openCreateModal()"
            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
      最初の定義を作成
    </button>
  </div>
  @else
  <div class="grid grid-cols-2 gap-4">
    @foreach($definitions as $def)
    <div class="bg-white border border-gray-200 rounded-xl p-5 hover:border-gray-300 transition-colors"
         id="def-card-{{ $def->id }}">
      {{-- カードヘッダー --}}
      <div class="flex items-start justify-between mb-3">
        <div class="flex-1 min-w-0">
          <h2 class="text-sm font-medium text-gray-900 truncate">{{ $def->name }}</h2>
          <p class="text-xs text-gray-400 mt-0.5">
            使用 {{ $def->use_count }}回
            @if($def->updated_at)
            · 更新 {{ \Carbon\Carbon::parse($def->updated_at)->diffForHumans() }}
            @endif
          </p>
        </div>
        <div class="flex gap-1 ml-2 flex-shrink-0">
          <button onclick="openEditModal('{{ $def->id }}')"
                  class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
                  title="編集">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
              <path d="M11 2l3 3-9 9H2v-3L11 2z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/>
            </svg>
          </button>
          <button onclick="deleteDefinition('{{ $def->id }}', '{{ $def->name }}')"
                  class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-md transition-colors"
                  title="削除">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="none">
              <path d="M2 4h12M5 4V2h6v2M6 7v5M10 7v5M3 4l1 9h8l1-9" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
      </div>

      {{-- 列タグ --}}
      <div class="flex flex-wrap gap-1.5 mb-4">
        @foreach($def->columns_decoded as $col)
        <span class="text-xs bg-gray-100 text-gray-600 rounded-full px-2.5 py-0.5">
          {{ $col['name'] }}
        </span>
        @endforeach
      </div>

      {{-- クイック変換ボタン --}}
      <a href="{{ route('conversion.index') }}?def={{ $def->id }}"
         class="block text-center text-xs text-blue-600 border border-blue-200 rounded-lg py-2
                hover:bg-blue-50 transition-colors">
        この定義でPDFを変換する →
      </a>
    </div>
    @endforeach

    {{-- 新規作成カード --}}
    <div class="border-2 border-dashed border-gray-200 rounded-xl p-5 flex items-center
                justify-center cursor-pointer hover:border-blue-300 hover:bg-blue-50
                transition-colors group"
         onclick="openCreateModal()">
      <div class="text-center">
        <div class="text-2xl text-gray-300 group-hover:text-blue-400 mb-1 transition-colors">+</div>
        <p class="text-sm text-gray-400 group-hover:text-blue-500 transition-colors">新しい定義を作成</p>
      </div>
    </div>
  </div>
  @endif

</div>

{{-- ─── 作成・編集モーダル ─────────────────────────────────── --}}
<div id="definition-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(0,0,0,0.4)">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl mx-4 max-h-[90vh] overflow-y-auto">
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
      <h2 id="modal-title" class="text-sm font-medium text-gray-900">出力定義を作成</h2>
      <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">×</button>
    </div>

    <form id="definition-form" class="px-6 py-5">
      @csrf
      <input type="hidden" id="modal-def-id" value="">
      <input type="hidden" id="modal-method" value="POST">

      {{-- 定義名 --}}
      <div class="mb-5">
        <label class="block text-xs font-medium text-gray-700 mb-1.5">定義名</label>
        <input type="text" id="modal-name"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
               placeholder="例：A社発注書 → 受注CSV" required>
      </div>

      {{-- 列定義 --}}
      <div class="mb-4">
        <div class="flex justify-between items-center mb-2">
          <label class="text-xs font-medium text-gray-700">抽出する列の設定</label>
          <span class="text-xs text-gray-400">最大20列</span>
        </div>
        <div class="border border-gray-200 rounded-lg overflow-hidden mb-2">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50">
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 w-36">列名</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">抽出ルール（AIへの指示）</th>
                <th class="w-8"></th>
              </tr>
            </thead>
            <tbody id="modal-columns-body" class="divide-y divide-gray-100">
            </tbody>
          </table>
        </div>
        <button type="button" onclick="addModalColumn()"
                class="text-xs text-blue-600 hover:underline">
          + 列を追加
        </button>
      </div>

      {{-- エラー表示 --}}
      <div id="modal-error" class="hidden bg-red-50 border border-red-200 text-red-700
                                    text-sm px-3 py-2 rounded-lg mb-4"></div>

      {{-- ボタン --}}
      <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 mt-4">
        <button type="button" onclick="closeModal()"
                class="px-4 py-2 border border-gray-200 text-sm rounded-lg hover:bg-gray-50">
          キャンセル
        </button>
        <button type="submit" id="modal-submit"
                class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
          保存する
        </button>
      </div>
    </form>
  </div>
</div>

{{-- 定義データ（編集用） --}}
<script>
const definitions = @json($definitions->keyBy('id'));
let modalColumnIndex = 0;

// ─── モーダル制御 ──────────────────────────────────────────
function openCreateModal() {
  document.getElementById('modal-title').textContent   = '出力定義を作成';
  document.getElementById('modal-def-id').value        = '';
  document.getElementById('modal-method').value        = 'POST';
  document.getElementById('modal-name').value          = '';
  document.getElementById('modal-submit').textContent  = '作成する';
  document.getElementById('modal-error').classList.add('hidden');
  document.getElementById('modal-columns-body').innerHTML = '';
  modalColumnIndex = 0;
  addModalColumn(); // 1行目を自動追加
  addModalColumn(); // 2行目を自動追加
  document.getElementById('definition-modal').classList.remove('hidden');
  setTimeout(() => document.getElementById('modal-name').focus(), 100);
}

function openEditModal(defId) {
  const def     = definitions[defId];
  const columns = JSON.parse(def.columns);

  document.getElementById('modal-title').textContent   = '出力定義を編集';
  document.getElementById('modal-def-id').value        = defId;
  document.getElementById('modal-method').value        = 'PUT';
  document.getElementById('modal-name').value          = def.name;
  document.getElementById('modal-submit').textContent  = '更新する';
  document.getElementById('modal-error').classList.add('hidden');

  // 列を再構築
  const tbody = document.getElementById('modal-columns-body');
  tbody.innerHTML = '';
  modalColumnIndex = 0;
  columns.forEach(col => addModalColumn(col.name, col.description));

  document.getElementById('definition-modal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('definition-modal').classList.add('hidden');
}

// モーダル外クリックで閉じる
document.getElementById('definition-modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// ─── 列追加 ───────────────────────────────────────────────
function addModalColumn(name = '', description = '') {
  const tbody = document.getElementById('modal-columns-body');
  const tr    = document.createElement('tr');
  tr.dataset.idx = modalColumnIndex;
  tr.innerHTML = `
    <td class="px-2 py-1.5">
      <input type="text" placeholder="列名" value="${escHtml(name)}"
             class="w-full border-0 text-sm focus:ring-0 bg-transparent col-name"
             required>
    </td>
    <td class="px-2 py-1.5">
      <input type="text" placeholder="例：発注元の会社名をそのまま" value="${escHtml(description)}"
             class="w-full border-0 text-sm focus:ring-0 bg-transparent col-desc">
    </td>
    <td class="px-2 py-1.5">
      <button type="button" onclick="this.closest('tr').remove()"
              class="text-gray-300 hover:text-red-400 text-base leading-none">×</button>
    </td>`;
  tbody.appendChild(tr);
  modalColumnIndex++;
}

function escHtml(s) {
  return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

// ─── フォーム送信 ──────────────────────────────────────────
document.getElementById('definition-form').addEventListener('submit', async (e) => {
  e.preventDefault();

  const defId    = document.getElementById('modal-def-id').value;
  const method   = document.getElementById('modal-method').value;
  const name     = document.getElementById('modal-name').value.trim();
  const errorEl  = document.getElementById('modal-error');
  const submitEl = document.getElementById('modal-submit');

  // 列を収集
  const columns = [];
  document.querySelectorAll('#modal-columns-body tr').forEach(tr => {
    const colName = tr.querySelector('.col-name')?.value.trim();
    const colDesc = tr.querySelector('.col-desc')?.value.trim();
    if (colName) columns.push({ name: colName, description: colDesc || '' });
  });

  if (!name) { showModalError('定義名を入力してください'); return; }
  if (columns.length === 0) { showModalError('列を1つ以上設定してください'); return; }

  errorEl.classList.add('hidden');
  submitEl.disabled = true;
  submitEl.textContent = '保存中...';

  const url = defId
    ? `/definitions/${defId}`
    : '/definitions';

  try {
    const res = await fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ name, columns }),
    });

    const data = await res.json();
    if (!res.ok) {
      showModalError(data.message || '保存に失敗しました');
      return;
    }

    // 成功 → ページリロード
    closeModal();
    window.location.reload();
  } catch {
    showModalError('通信エラーが発生しました');
  } finally {
    submitEl.disabled = false;
    submitEl.textContent = defId ? '更新する' : '作成する';
  }
});

function showModalError(msg) {
  const el = document.getElementById('modal-error');
  el.textContent = msg;
  el.classList.remove('hidden');
}

// ─── 削除 ─────────────────────────────────────────────────
async function deleteDefinition(defId, name) {
  if (!confirm(`「${name}」を削除しますか？\nこの操作は取り消せません。`)) return;

  try {
    const res = await fetch(`/definitions/${defId}`, {
      method:  'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    });
    if (res.ok) {
      document.getElementById(`def-card-${defId}`)?.remove();
    } else {
      alert('削除に失敗しました');
    }
  } catch {
    alert('通信エラーが発生しました');
  }
}
</script>
@endsection
