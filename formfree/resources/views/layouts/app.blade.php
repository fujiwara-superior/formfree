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
  .animate-spin{animation:spin 1s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}

  /* ページ遷移ローディングバー */
  #nprogress {
    position:fixed; top:0; left:0; right:0; z-index:9999;
    height:3px; background:#2563eb;
    transition:width .2s ease;
    width:0%;
  }
  #nprogress.loading { animation:progress-grow 1.5s ease-out forwards; }
  @keyframes progress-grow {
    0%   { width:0%; opacity:1; }
    80%  { width:85%; opacity:1; }
    100% { width:85%; opacity:1; }
  }
  #nprogress.done { width:100%; opacity:0; transition:width .15s ease, opacity .2s ease .1s; }

  /* ボタン・リンクの即時フィードバック */
  a, button { transition:opacity .1s ease, transform .1s ease; }
  a:active:not(.no-feedback), button:active:not(.no-feedback) {
    opacity:.7; transform:scale(.98);
  }
</style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

{{-- ページ遷移インジケーター --}}
<div id="nprogress"></div>
<script>
(function(){
  var bar = document.getElementById('nprogress');
  function start(){
    bar.className='loading';
  }
  function done(){
    bar.className='done';
    setTimeout(function(){ bar.className=''; bar.style.cssText=''; }, 400);
  }
  // 通常リンククリック時
  document.addEventListener('click', function(e){
    var a = e.target.closest('a');
    if(!a) return;
    var href = a.getAttribute('href');
    if(!href || href.startsWith('#') || href.startsWith('javascript') || a.target==='_blank') return;
    start();
  });
  // フォーム送信時
  document.addEventListener('submit', function(){ start(); });
  // ページ読み込み完了時
  window.addEventListener('pageshow', function(){ done(); });
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
