{{-- resources/views/emails/layout.blade.php --}}
<!DOCTYPE html>
<html lang="ja"><head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{margin:0;padding:0;background:#f0f0ee;font-family:-apple-system,"Hiragino Sans","Yu Gothic",sans-serif}
.wrap{max-width:560px;margin:24px auto}
.top{background:{{$topColor ?? '#1e3a5f'}};padding:18px 32px;border-radius:8px 8px 0 0}
.logo{color:#fff;font-size:15px;font-weight:500;letter-spacing:-.3px;text-decoration:none}
.body{background:#fff;padding:28px 32px}
.foot{background:#fff;padding:14px 32px;border-top:1px solid #e8e6e0;border-radius:0 0 8px 8px;text-align:center}
.foot p{font-size:11px;color:#888;margin:0}
.foot a{color:#888;text-decoration:none}
h1{font-size:18px;font-weight:500;color:#1a1a1a;margin:0 0 10px;line-height:1.4}
p{font-size:14px;color:#444;line-height:1.8;margin:0 0 14px}
.card{background:#f8f7f4;border-radius:8px;padding:16px 20px;margin-bottom:16px}
.row{display:flex;justify-content:space-between;font-size:13px;padding:5px 0;border-bottom:1px solid #e8e6e0}
.row:last-child{border-bottom:none}
.lbl{color:#666}.val{color:#1a1a1a;font-weight:500}
.btn{display:inline-block;padding:11px 28px;border-radius:6px;font-size:14px;font-weight:500;color:#fff;text-decoration:none}
.btn-primary{background:#2563eb}
.btn-warning{background:#d97706}
.btn-danger{background:#dc2626}
.alert{border-radius:6px;padding:12px 16px;margin-bottom:16px;font-size:13px;line-height:1.6}
.alert-warn{background:#fef3c7;color:#92400e;border-left:3px solid #d97706}
.alert-danger{background:#fee2e2;color:#991b1b;border-left:3px solid #dc2626}
.center{text-align:center}
.muted{font-size:12px;color:#888;text-align:center;margin-top:4px}
ol{padding-left:20px;margin:0 0 16px}
ol li{font-size:13px;color:#444;line-height:1.8;padding:3px 0}
</style>
</head><body>
<div class="wrap">
  <div class="top"><span class="logo">FormFree</span></div>
  <div class="body">
    <p style="color:#888;font-size:12px;margin-bottom:8px">{{ $recipientName ?? '' }} 様</p>
    @yield('body')
  </div>
  <div class="foot">
    <p>© FormFree &nbsp;·&nbsp;
       <a href="{{ route('unsubscribe', ['token' => $unsubToken ?? '']) }}">配信停止</a>
       &nbsp;·&nbsp;
       <a href="{{ config('app.url') }}/privacy">プライバシーポリシー</a>
    </p>
  </div>
</div>
</body></html>
