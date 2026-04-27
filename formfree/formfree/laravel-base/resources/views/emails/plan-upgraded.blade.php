@extends('emails.layout')
@section('body')
<h1>プランのアップグレードが完了しました</h1>
<p>FormFree {{ $plan === 'pro' ? 'Pro' : 'Standard' }} プランへのアップグレードありがとうございます。</p>
<div class="card">
  <div class="row"><span class="lbl">新しいプラン</span><span class="val">{{ $plan === 'pro' ? 'Pro' : 'Standard' }}</span></div>
  <div class="row"><span class="lbl">月次変換上限</span><span class="val">{{ $plan === 'pro' ? '500' : '100' }}件</span></div>
  <div class="row"><span class="lbl">適用開始</span><span class="val">即時</span></div>
</div>
<div class="center">
  <a href="{{ route('dashboard') }}" class="btn btn-primary">ダッシュボードへ</a>
</div>
@endsection
