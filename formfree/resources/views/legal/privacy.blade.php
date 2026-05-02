@extends('layouts.app')
@section('title', 'プライバシーポリシー')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
  <h1 class="text-xl font-medium text-gray-900 mb-6">プライバシーポリシー</h1>

  <div class="prose prose-sm text-gray-700 space-y-6">

    <section>
      <h2 class="text-base font-medium text-gray-900 mb-2">1. 取得する情報</h2>
      <p>本サービスでは、以下の情報を取得します。</p>
      <ul class="list-disc list-inside space-y-1 mt-2">
        <li>氏名・メールアドレス（登録時）</li>
        <li>アップロードされたPDFファイル（変換処理後に削除）</li>
        <li>利用ログ・アクセス情報</li>
        <li>Stripeが取得する決済情報（当社は保持しません）</li>
      </ul>
    </section>

    <section>
      <h2 class="text-base font-medium text-gray-900 mb-2">2. 利用目的</h2>
      <p>取得した情報は以下の目的で利用します。</p>
      <ul class="list-disc list-inside space-y-1 mt-2">
        <li>サービスの提供・運営</li>
        <li>利用料金の請求</li>
        <li>サービスに関するお知らせの送信</li>
        <li>サービス改善・統計分析</li>
      </ul>
    </section>

    <section>
      <h2 class="text-base font-medium text-gray-900 mb-2">3. 第三者提供</h2>
      <p>法令に基づく場合を除き、ユーザーの同意なく第三者に個人情報を提供しません。決済処理にはStripe, Inc.を利用しており、同社のプライバシーポリシーが適用されます。</p>
    </section>

    <section>
      <h2 class="text-base font-medium text-gray-900 mb-2">4. データの保管・削除</h2>
      <p>アップロードされたPDFは変換処理完了後に削除します。変換結果（CSV）はアカウント削除時または解約後30日経過後に削除します。</p>
    </section>

    <section>
      <h2 class="text-base font-medium text-gray-900 mb-2">5. お問い合わせ</h2>
      <p>個人情報の開示・訂正・削除のご依頼は、ログイン後のサポート窓口またはメールにてお問い合わせください。</p>
    </section>

    <p class="text-xs text-gray-400 mt-8">制定日：2024年1月1日</p>
  </div>
</div>
@endsection
