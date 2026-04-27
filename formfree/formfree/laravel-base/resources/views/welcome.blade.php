{{-- resources/views/welcome.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FormFree — テンプレート不要のPDF→CSV変換</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white">

{{-- ナビ --}}
<nav class="border-b border-gray-100 px-6 py-4 flex items-center justify-between max-w-5xl mx-auto">
  <span class="font-medium text-gray-900">FormFree</span>
  <div class="flex items-center gap-4">
    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900">ログイン</a>
    <a href="{{ route('register') }}"
       class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
      無料で始める
    </a>
  </div>
</nav>

{{-- ヒーロー --}}
<section class="max-w-3xl mx-auto px-6 py-20 text-center">
  <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 text-xs px-3 py-1.5
              rounded-full mb-6 border border-blue-100">
    <span>AI-OCRの次世代版</span>
  </div>
  <h1 class="text-4xl font-medium text-gray-900 leading-tight mb-5">
    PDFをアップロードするだけで<br>
    <span class="text-blue-600">CSVに自動変換</span>
  </h1>
  <p class="text-base text-gray-500 leading-relaxed mb-8 max-w-xl mx-auto">
    座標設定・テンプレート不要。LLMが文脈を理解して読み取るので、
    取引先ごとにフォーマットが違っても自動対応。基幹システムへの取込がこれ一本で完結します。
  </p>
  <div class="flex items-center justify-center gap-3">
    <a href="{{ route('register') }}"
       class="bg-blue-600 text-white font-medium px-6 py-3 rounded-lg hover:bg-blue-700 text-sm">
      無料で試す（月10件まで）
    </a>
    <a href="#features" class="text-sm text-gray-500 hover:text-gray-700">詳しく見る →</a>
  </div>
  <p class="text-xs text-gray-400 mt-4">クレジットカード不要・30秒で登録完了</p>
</section>

{{-- 特徴 --}}
<section id="features" class="bg-gray-50 py-16">
  <div class="max-w-4xl mx-auto px-6">
    <h2 class="text-xl font-medium text-gray-900 text-center mb-10">
      AI-OCRとの違い
    </h2>
    <div class="grid grid-cols-3 gap-5">
      @foreach([
        ['テンプレート不要', '「座標を設定して…」という作業が不要。PDFをアップロードするだけで、どんなフォーマットでも読み取ります。'],
        ['フォーマット変更に自動対応', '取引先がPDFの書式を変えても再設定は不要。LLMが文脈で理解するため自動で追従します。'],
        ['基幹連携をすぐに実現', 'Shift-JIS出力に対応。生成されたCSVをそのまま既存の基幹システムに取り込めます。'],
      ] as [$title, $desc])
      <div class="bg-white rounded-xl p-5 border border-gray-200">
        <h3 class="text-sm font-medium text-gray-900 mb-2">{{ $title }}</h3>
        <p class="text-xs text-gray-500 leading-relaxed">{{ $desc }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- 料金 --}}
<section class="py-16">
  <div class="max-w-3xl mx-auto px-6 text-center">
    <h2 class="text-xl font-medium text-gray-900 mb-2">シンプルな料金体系</h2>
    <p class="text-sm text-gray-500 mb-10">いつでも解約可能。違約金なし。</p>
    <div class="grid grid-cols-3 gap-4">
      @foreach([
        ['Free', '¥0', '月額', ['月10件まで', '出力定義3件', 'テキストPDF', 'Shift-JIS出力'], false],
        ['Standard', '¥29,800', '月額（税込）', ['月100件まで', '出力定義無制限', 'スキャンPDF対応', '一括処理', 'メールサポート'], true],
        ['Pro', '¥79,800', '月額（税込）', ['月500件まで', 'Standardの全機能', 'APIアクセス', 'Webhook連携', '優先サポート'], false],
      ] as [$name, $price, $period, $features, $popular])
      <div class="rounded-xl p-5 border {{ $popular ? 'border-blue-500 border-2' : 'border-gray-200' }}">
        @if($popular)
        <div class="text-xs bg-blue-600 text-white rounded-full px-2.5 py-0.5 inline-block mb-2">人気</div>
        @endif
        <h3 class="text-sm font-medium text-gray-900 mb-1">{{ $name }}</h3>
        <p class="text-2xl font-medium text-gray-900">{{ $price }}</p>
        <p class="text-xs text-gray-400 mb-3">{{ $period }}</p>
        <ul class="space-y-1.5 mb-4 text-left">
          @foreach($features as $f)
          <li class="text-xs text-gray-600 flex items-center gap-1.5">
            <span class="text-green-500">✓</span>{{ $f }}
          </li>
          @endforeach
        </ul>
        <a href="{{ route('register') }}"
           class="block text-center text-sm py-2 rounded-lg
                  {{ $popular ? 'bg-blue-600 text-white hover:bg-blue-700' : 'border border-gray-200 hover:bg-gray-50' }}">
          {{ $name === 'Free' ? '無料で始める' : '試してみる' }}
        </a>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- フッター --}}
<footer class="border-t border-gray-100 py-8">
  <div class="max-w-5xl mx-auto px-6 text-center text-xs text-gray-400">
    <p>© FormFree &nbsp;·&nbsp; <a href="/terms" class="hover:underline">利用規約</a> &nbsp;·&nbsp; <a href="/privacy" class="hover:underline">プライバシーポリシー</a></p>
  </div>
</footer>

</body>
</html>
