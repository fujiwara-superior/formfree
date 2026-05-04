{{-- resources/views/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'FormFree')</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  #nprogress{position:fixed;top:0;left:0;right:0;z-index:9999;height:3px;background:#2563eb;width:0%}
  #nprogress.loading{animation:progress-grow 1.5s ease-out forwards}
  @keyframes progress-grow{0%{width:0%;opacity:1}80%{width:85%;opacity:1}100%{width:85%;opacity:1}}
  #nprogress.done{width:100%;opacity:0;transition:width .15s ease,opacity .2s ease .1s}
  a,button{transition:opacity .1s ease,transform .1s ease}
  a:active,button:active{opacity:.7;transform:scale(.98)}
</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12">
<div id="nprogress"></div>
<script>
(function(){
  var bar=document.getElementById('nprogress');
  function start(){bar.className='loading';}
  function done(){bar.className='done';setTimeout(function(){bar.className='';bar.style.cssText='';},400);}
  document.addEventListener('click',function(e){
    var a=e.target.closest('a');
    if(!a)return;
    var href=a.getAttribute('href');
    if(!href||href.startsWith('#')||href.startsWith('javascript')||a.target==='_blank')return;
    start();
  });
  document.addEventListener('submit',function(){start();});
  window.addEventListener('pageshow',function(){done();});
})();
</script>

<div class="w-full max-w-md px-4">
  {{-- ロゴ --}}
  <div class="text-center mb-8">
    <a href="https://superior.co.jp/formfree/" class="text-xs text-gray-400 hover:text-gray-600 mb-3 inline-block">
      ← サービス紹介ページ
    </a>
    <div>
      <a href="/" class="text-xl font-medium text-gray-900">FormFree</a>
    </div>
    <p class="text-sm text-gray-500 mt-1">テンプレート不要のPDF→CSV変換</p>
  </div>

  {{-- カード --}}
  <div class="bg-white rounded-2xl border border-gray-200 p-8">
    @yield('content')
  </div>

  {{-- フッター --}}
  <p class="text-center text-xs text-gray-400 mt-6">
    © FormFree &nbsp;·&nbsp;
    <a href="/privacy" class="hover:underline">プライバシーポリシー</a>
    &nbsp;·&nbsp;
    <a href="/terms" class="hover:underline">利用規約</a>
  </p>
</div>

@stack('scripts')
</body>
</html>
