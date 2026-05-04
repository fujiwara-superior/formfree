{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'FormFree') — FormFree</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  [x-cloak]{display:none}
  .animate-spin{animation:ff-spin 1s linear infinite}

  /* ── プログレスバー ── */
  #ff-bar {
    position:fixed; top:0; left:0; z-index:9999; height:5px; width:0%;
    background:linear-gradient(90deg,#1d4ed8,#38bdf8,#1d4ed8);
    background-size:200% 100%;
    box-shadow:0 0 10px rgba(37,99,235,.8), 0 0 20px rgba(56,189,248,.4);
    pointer-events:none;
  }
  #ff-bar.active {
    animation:ff-bar-grow 2.5s cubic-bezier(.4,0,.2,1) forwards,
              ff-bar-shine 1.2s linear infinite;
  }
  @keyframes ff-bar-grow {
    0%{width:0%} 25%{width:55%} 60%{width:78%} 100%{width:88%}
  }
  @keyframes ff-bar-shine {
    0%{background-position:100% 0} 100%{background-position:-100% 0}
  }
  #ff-bar.done {
    width:100%!important; animation:none!important;
    transition:width .12s ease, opacity .25s ease .1s; opacity:0;
  }

  /* ── 全画面オーバーレイ（1.2秒後） ── */
  #ff-overlay {
    position:fixed; inset:0; z-index:9998;
    background:rgba(255,255,255,.75); backdrop-filter:blur(3px);
    display:none; flex-direction:column;
    align-items:center; justify-content:center; gap:16px;
  }
  #ff-overlay.show { display:flex; }
  #ff-overlay svg { animation:ff-spin .8s linear infinite; }
  #ff-overlay p { font-size:.875rem; color:#475569; font-family:sans-serif; }

  /* ── ボタン押下の即時フィードバック ── */
  .ff-pressed {
    opacity:.65!important; transform:scale(.97)!important;
    pointer-events:none!important; position:relative;
  }
  .ff-pressed::after {
    content:'';
    position:absolute; right:12px; top:50%;
    transform:translateY(-50%);
    width:13px; height:13px;
    border:2px solid currentColor; border-top-color:transparent;
    border-radius:50%; animation:ff-spin .7s linear infinite;
  }
  @keyframes ff-spin { to{transform:translateY(-50%) rotate(360deg)} }
</style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

{{-- プログレスバー & オーバーレイ --}}
<div id="ff-bar"></div>
<div id="ff-overlay">
  <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
    <circle cx="20" cy="20" r="16" stroke="#e2e8f0" stroke-width="4"/>
    <path d="M20 4a16 16 0 0 1 16 16" stroke="#2563eb" stroke-width="4" stroke-linecap="round"/>
  </svg>
  <p>読み込み中...</p>
</div>

<script>
(function(){
  var bar=document.getElementById('ff-bar');
  var overlay=document.getElementById('ff-overlay');
  var timer;

  function start(){
    clearTimeout(timer);
    bar.className='active';
    timer=setTimeout(function(){ overlay.classList.add('show'); }, 1200);
  }
  function done(){
    clearTimeout(timer);
    overlay.classList.remove('show');
    bar.className='done';
    setTimeout(function(){ bar.className=''; bar.style.cssText=''; }, 500);
  }

  // キャプチャフェーズ（クリック直後に反応）
  document.addEventListener('click', function(e){
    var el=e.target.closest('a,button');
    if(!el) return;
    if(el.tagName==='A'){
      var href=el.getAttribute('href');
      if(!href||href.startsWith('#')||href.startsWith('javascript')||el.target==='_blank') return;
    }
    el.classList.add('ff-pressed');
    if(el.tagName==='A') start();
  }, true);

  document.addEventListener('submit', function(e){
    var btn=e.target.querySelector('button[type=submit],button:not([type])');
    if(btn) btn.classList.add('ff-pressed');
    start();
  }, true);

  window.addEventListener('pageshow', done);
})();
</script>

{{-- ナビゲーション --}}
<nav class="bg-white border-b border-gray-200">
  <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
    <div class="flex items-center gap-8">
      <a href="https://superior.co.jp/formfree/" class="text-sm font-medium text-gray-900">FormFree</a>
      <div class="flex items-center gap-5 text-sm">
        <a href="{{ route('dashboard') }}"
           class="{{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-800' }}">
          ダッシュボード
        </a>
        <a href="{{ route('conversion.index') }}"
           class="{{ request()->routeIs('conversion.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-800' }}">
          変換する
        </a>
        <a href="{{ route('definitions.index') }}"
           class="{{ request()->routeIs('definitions.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-800' }}">
          出力定義
        </a>
        <a href="{{ route('conversion.history') }}"
           class="{{ request()->routeIs('conversion.history') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-800' }}">
          履歴
        </a>
      </div>
    </div>
    <div class="flex items-center gap-3">
      @if(auth()->user()?->company?->plan === 'free')
      <a href="{{ route('billing.index') }}"
         class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700">
        アップグレード
      </a>
      @else
      <a href="{{ route('billing.index') }}"
         class="text-xs text-gray-500 hover:text-gray-800">
        プランとお支払い
      </a>
      @endif
      <div class="text-xs text-gray-500">{{ auth()->user()?->name }}</div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-xs text-gray-400 hover:text-gray-600">ログアウト</button>
      </form>
    </div>
  </div>
</nav>

{{-- フラッシュメッセージ --}}
@if(session('success'))
<div class="max-w-6xl mx-auto px-4 pt-4">
  <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">
    {{ session('success') }}
  </div>
</div>
@endif
@if(session('error'))
<div class="max-w-6xl mx-auto px-4 pt-4">
  <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
    {{ session('error') }}
  </div>
</div>
@endif

{{-- メインコンテンツ --}}
@yield('content')

@stack('scripts')
</body>
</html>
