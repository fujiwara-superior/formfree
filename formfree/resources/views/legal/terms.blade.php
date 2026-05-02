@extends('layouts.app')
@section('title', '利用規約')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
  <h1 class="text-xl font-medium text-gray-900 mb-6">利用規約</h1>

  <div class="prose prose-sm text-gray-700 space-y-6">

    <section>
      <h2 class="text-base font-medium text-gray-900 mb-2">第1条（適用）</h2>
      <p>本規約は、FormFree（以下「本サービス」）の利用に関する条件を定めるものです。ユーザーは本規約に同意した上で本サービスをご利用ください。</p>
    </section>

    <section>
      <h2 class="text-base font-medium text-gray-900 mb-2">第2条（サービス内容）</h2>
      <p>本サービスは、PDFファイルをCSV形式に変換するSaaSです。運営者はサービス内容を予告なく変更・停止する場合があります。</p>
    </section>

    <section>
      <h2 class="text-base font-medium text-gray-900 mb-2">第3条（料金）</h2>
      <p>有料プランの料金は下記のとおりです。</p>
      <ul class="list-disc list-inside space-y-1 mt-2">
        <li>Free：¥0 / 月（月10件まで）</li>
        <li>Standard：¥3,980 / 月（月200件まで）</li>
        <li>Pro：¥9,800 / 月（無制限）</li>
      </ul>
      <p class="mt-2">料金はStripeを通じて決済されます。解約はいつでも可能で、違約金はありません。</p>
    </section>

    <section>
      <h2 class="text-base font-medium text-gray-900 mb-2">第4条（禁止事項）</h2>
      <p>以下の行為を禁止します。</p>
      <ul class="list-disc list-inside space-y-1 mt-2">
        <li>法令・公序良俗に反する行為</li>
        <li>本サービスへの不正アクセス・リバースエンジニアリング</li>
        <li>第三者の権利を侵害する行為</li>
        <li>その他、運営者が不適切と判断する行為</li>
      </ul>
    </section>

    <section>
      <h2 class="text-base font-medium text-gray-900 mb-2">第5条（免責事項）</h2>
      <p>本サービスは現状有姿で提供されます。変換精度・可用性について運営者は保証しません。本サービスの利用により生じた損害について、運営者は一切責任を負いません。</p>
    </section>

    <section>
      <h2 class="text-base font-medium text-gray-900 mb-2">第6条（規約の変更）</h2>
      <p>運営者は、必要に応じて本規約を変更することができます。変更後の規約は本サービス上に掲示した時点で効力を生じます。</p>
    </section>

    <p class="text-xs text-gray-400 mt-8">制定日：2024年1月1日</p>
  </div>
</div>
@endsection
