@extends('emails.layout')
@section('body')
@if($isWarning)
<h1>無料変換枠が残りわずかです</h1>
<p>今月の変換回数が {{ $usedCount }}/{{ $limit }} 件に達しました。このまま使い続けると今月の上限に達してしまいます。</p>
@else
<h1>今月の無料変換枠を使い切りました</h1>
<p>今月の無料プランの変換回数（{{ $limit }}件）をすべて使用しました。引き続きご利用いただくにはプランのアップグレードが必要です。</p>
@endif
<div class="card">
  <div class="row"><span class="lbl">今月の使用数</span><span class="val">{{ $usedCount }} / {{ $limit }} 件</span></div>
  <div class="row"><span class="lbl">リセット日</span><span class="val">翌月1日</span></div>
</div>
<div class="alert alert-warn">
  Standardプランにアップグレードすると月200件まで変換できます。保存済みの出力定義はそのまま使えます。
</div>
<div class="center">
  <a href="{{ route('billing.index') }}" class="btn btn-warning">プランをアップグレード（月額 3,980円〜）</a>
</div>
@endsection
