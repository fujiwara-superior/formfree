@extends('emails.layout')
@section('body')
<h1>FormFreeへようこそ</h1>
<p>ご登録ありがとうございます。無料プランでは月10件までPDFをCSVに変換できます。</p>
<ol>
  <li>PDFをアップロードする（ドラッグ＆ドロップOK）</li>
  <li>出力したいCSVの列を設定する</li>
  <li>変換結果をプレビューして確認・修正する</li>
  <li>CSVをダウンロードして基幹システムに取り込む</li>
</ol>
<div class="center">
  <a href="{{ route('conversion.index') }}" class="btn btn-primary">最初の変換を試してみる</a>
</div>
@endsection
