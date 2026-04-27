@extends('emails.layout')
@section('body')
<h1>PDFの変換が完了しました</h1>
<p>以下のファイルの変換処理が完了しました。プレビュー画面でご確認の上、CSVをダウンロードしてください。</p>
<div class="card">
  <div class="row"><span class="lbl">ファイル名</span><span class="val">{{ $job->pdf_filename }}</span></div>
  <div class="row"><span class="lbl">抽出行数</span><span class="val">{{ $job->row_count }}行</span></div>
  <div class="row"><span class="lbl">文字コード</span><span class="val">{{ strtoupper($job->csv_encoding) }}</span></div>
</div>
<div class="center">
  <a href="{{ $downloadUrl }}" class="btn btn-primary">プレビューを確認してダウンロード</a>
</div>
<p class="muted">※ダウンロードリンクは7日間有効です</p>
@endsection
