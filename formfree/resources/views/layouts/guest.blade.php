{{-- resources/views/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'FormFree')</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12">

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
