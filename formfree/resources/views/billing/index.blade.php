{{-- resources/views/billing/index.blade.php --}}
@extends('layouts.app')
@section('title', 'プランとお支払い')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">

  <h1 class="text-base font-medium text-gray-900 mb-1">プランとお支払い</h1>
  <p class="text-xs text-gray-400 mb-8">現在のプラン：
    <span class="font-medium text-gray-700">
      {{ match($company->plan) {
        'standard' => 'Standard（月額 ¥3,980）',
        'pro'      => 'Pro（月額 ¥9,800）',
        default    => '無料プラン',
      } }}
    </span>
  </p>

  {{-- 現在のプラン：有料 → 管理ポータルへ --}}
  @if($company->plan !== 'free')
  <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-8 flex items-center justify-between">
    <div>
      <p class="text-sm font-medium text-blue-900">サブスクリプションを管理する</p>
      <p class="text-xs text-blue-700 mt-0.5">プランの変更・解約・お支払い方法の更新はこちらから</p>
    </div>
    <a href="{{ route('billing.portal') }}"
       class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 flex-shrink-0">
      Stripeポータルを開く →
    </a>
  </div>
  @endif

  {{-- プラン比較 --}}
  <div class="grid grid-cols-3 gap-4 mb-8">

    {{-- 無料 --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5
                {{ $company->plan === 'free' ? 'ring-2 ring-blue-500' : '' }}">
      @if($company->plan === 'free')
      <div class="text-xs bg-blue-100 text-blue-700 rounded-full px-2.5 py-0.5 inline-block mb-3">現在のプラン</div>
      @endif
      <h2 class="text-sm font-medium text-gray-900 mb-1">Free</h2>
      <p class="text-2xl font-medium text-gray-900 mb-0.5">¥0</p>
      <p class="text-xs text-gray-400 mb-4">月額・永久無料</p>
      <ul class="space-y-2 mb-5">
        @foreach(['月10件まで変換','出力定義の保存（3件）','テキストPDF対応','Shift-JIS出力'] as $f)
        <li class="flex items-center gap-2 text-xs text-gray-600">
          <span class="text-green-500 flex-shrink-0">✓</span>{{ $f }}
        </li>
        @endforeach
        @foreach(['スキャンPDF対応','一括処理','APIアクセス'] as $f)
        <li class="flex items-center gap-2 text-xs text-gray-400 line-through">
          <span class="flex-shrink-0">–</span>{{ $f }}
        </li>
        @endforeach
      </ul>
      <div class="text-center text-xs text-gray-400 py-2">現在のプラン</div>
    </div>

    {{-- Standard --}}
    <div class="bg-white border-2 border-blue-500 rounded-xl p-5 relative
                {{ $company->plan === 'standard' ? 'ring-2 ring-blue-500' : '' }}">
      <div class="absolute -top-3 left-1/2 -translate-x-1/2">
        <span class="text-xs bg-blue-600 text-white rounded-full px-3 py-1">人気No.1</span>
      </div>
      @if($company->plan === 'standard')
      <div class="text-xs bg-blue-100 text-blue-700 rounded-full px-2.5 py-0.5 inline-block mb-3">現在のプラン</div>
      @else
      <div class="mb-5"></div>
      @endif
      <h2 class="text-sm font-medium text-gray-900 mb-1">Standard</h2>
      <p class="text-2xl font-medium text-gray-900 mb-0.5">¥3,980</p>
      <p class="text-xs text-gray-400 mb-4">月額（税込）</p>
      <ul class="space-y-2 mb-5">
        @foreach(['月200件まで変換','出力定義の保存（無制限）','テキスト・スキャンPDF対応','Shift-JIS / UTF-8出力','処理履歴30日間保存','メールサポート'] as $f)
        <li class="flex items-center gap-2 text-xs text-gray-600">
          <span class="text-green-500 flex-shrink-0">✓</span>{{ $f }}
        </li>
        @endforeach
      </ul>
      @if($company->plan === 'free')
      <button onclick="startCheckout('standard')"
              class="w-full bg-blue-600 text-white text-sm font-medium py-2.5 rounded-lg
                     hover:bg-blue-700 transition-colors" id="btn-standard">
        Standardにアップグレード
      </button>
      @elseif($company->plan === 'standard')
      <div class="text-center text-xs text-blue-600 py-2">現在のプラン</div>
      @else
      <button onclick="startCheckout('standard')"
              class="w-full border border-gray-200 text-sm py-2.5 rounded-lg hover:bg-gray-50"
              id="btn-standard">
        Standardにダウングレード
      </button>
      @endif
    </div>

    {{-- Pro --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5
                {{ $company->plan === 'pro' ? 'ring-2 ring-blue-500' : '' }}">
      @if($company->plan === 'pro')
      <div class="text-xs bg-blue-100 text-blue-700 rounded-full px-2.5 py-0.5 inline-block mb-3">現在のプラン</div>
      @else
      <div class="mb-5"></div>
      @endif
      <h2 class="text-sm font-medium text-gray-900 mb-1">Pro</h2>
      <p class="text-2xl font-medium text-gray-900 mb-0.5">¥9,800</p>
      <p class="text-xs text-gray-400 mb-4">月額（税込）</p>
      <ul class="space-y-2 mb-5">
        @foreach(['無制限変換','Standardの全機能','一括処理（複数PDF同時）','REST APIアクセス','Webhook連携','優先サポート','処理履歴1年間保存'] as $f)
        <li class="flex items-center gap-2 text-xs text-gray-600">
          <span class="text-green-500 flex-shrink-0">✓</span>{{ $f }}
        </li>
        @endforeach
      </ul>
      @if($company->plan === 'pro')
      <div class="text-center text-xs text-blue-600 py-2">現在のプラン</div>
      @else
      <button onclick="startCheckout('pro')"
              class="w-full border border-gray-200 text-sm py-2.5 rounded-lg hover:bg-gray-50"
              id="btn-pro">
        Proにアップグレード
      </button>
      @endif
    </div>
  </div>

  {{-- よくある質問 --}}
  <div class="bg-white border border-gray-200 rounded-xl p-6">
    <h2 class="text-sm font-medium text-gray-900 mb-4">よくある質問</h2>
    <div class="space-y-4">
      @foreach([
        ['途中でプランを変更できますか？','はい。いつでもアップグレード・ダウングレードが可能です。アップグレードの場合は差額を日割りで請求します。'],
        ['解約したらデータはどうなりますか？','解約後も変換履歴と出力定義は30日間保持されます。その後、無料プランの上限に切り替わります。'],
        ['請求書（インボイス）は発行できますか？','Stripeの顧客ポータルから領収書・請求書をPDF形式でダウンロードできます。インボイス対応済みです。'],
        ['スキャンPDFの変換精度はどのくらいですか？','印刷品質の良いスキャンPDFであれば95%以上の精度で抽出できます。解像度が低い場合や手書き文字は精度が下がる場合があります。'],
      ] as [$q, $a])
      <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
        <p class="text-sm font-medium text-gray-800 mb-1">{{ $q }}</p>
        <p class="text-xs text-gray-500 leading-relaxed">{{ $a }}</p>
      </div>
      @endforeach
    </div>
  </div>

</div>

{{-- ローディングオーバーレイ --}}
<div id="checkout-loading"
     class="hidden fixed inset-0 z-50 flex items-center justify-center"
     style="background:rgba(255,255,255,0.9)">
  <div class="text-center">
    <div class="w-10 h-10 border-2 border-gray-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-3"
         style="border-width:2px"></div>
    <p class="text-sm text-gray-600">Stripeの決済ページに移動中...</p>
  </div>
</div>

@push('scripts')
<script>
async function startCheckout(plan) {
  const btnId = plan === 'standard' ? 'btn-standard' : 'btn-pro';
  const btn   = document.getElementById(btnId);
  if (btn) { btn.disabled = true; btn.textContent = '処理中...'; }

  document.getElementById('checkout-loading').classList.remove('hidden');

  try {
    const res  = await fetch('{{ route("billing.checkout") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ plan }),
    });
    const data = await res.json();

    if (data.checkout_url) {
      window.location.href = data.checkout_url;
    } else {
      throw new Error(data.error || '決済ページの取得に失敗しました');
    }
  } catch (err) {
    document.getElementById('checkout-loading').classList.add('hidden');
    if (btn) { btn.disabled = false; btn.textContent = plan === 'standard' ? 'Standardにアップグレード' : 'Proにアップグレード'; }
    alert(err.message || 'エラーが発生しました。もう一度お試しください。');
  }
}
</script>
@endpush
@endsection
