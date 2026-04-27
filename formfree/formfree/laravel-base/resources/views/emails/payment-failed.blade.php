@php $topColor = '#7f1d1d'; @endphp
@extends('emails.layout')
@section('body')
<h1 style="color:#991b1b">お支払いが処理できませんでした</h1>
<p>ご登録のカードへの請求が失敗しました。サービスを継続してご利用いただくために、お支払い情報の更新をお願いします。</p>
<div class="alert alert-danger">
  このまま7日以内にお手続きがない場合、プランが自動的に無料プランに変更されます。
</div>
<div class="card">
  <div class="row"><span class="lbl">失敗理由</span><span class="val" style="color:#dc2626">{{ $failureReason }}</span></div>
  <div class="row"><span class="lbl">次回リトライ</span><span class="val">{{ $nextRetryDate }}</span></div>
</div>
<div class="center">
  <a href="{{ route('billing.portal') }}" class="btn btn-danger">支払い情報を更新する</a>
</div>
<p class="muted">ご不明な点はサポートまでお問い合わせください</p>
@endsection
