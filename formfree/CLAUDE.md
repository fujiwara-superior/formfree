# FormFree 残作業 — 実装指示書

## 前提

- プロジェクト: FormFree（Laravel + Python/FastAPI）
- デプロイ先: Railway（プロジェクト名: gracious-courage）
- DB: Supabase PostgreSQL

---

## Claude Code がやること（ファイル変更）

以下を順番に実行してください。
各タスク完了後に `git add . && git commit -m "fix: ..."` でコミットすること。

---

### タスク1　Procfile の修正

`Procfile` を開いて `migrate:fresh` を `migrate` に変更する。

**変更前:**
```
web: php artisan migrate:fresh --force && php artisan serve ...
```

**変更後:**
```
web: php artisan migrate --force && php artisan serve ...
```

`migrate:fresh` はDBを全消去するため本番では絶対に使わない。
`migrate` に戻すことで既存データを保持したままマイグレーションが走る。

---

### タスク2　Python ソースの環境変数リネーム

Python サービス内で `SUPABASE_KEY` を参照している箇所をすべて `SUPA_SERVICE_KEY` に変更する。

```bash
# まず該当箇所を検索して確認
grep -rn "SUPABASE_KEY" .
```

見つかったファイルの `os.environ.get("SUPABASE_KEY")` または `os.getenv("SUPABASE_KEY")` を
すべて `SUPA_SERVICE_KEY` に置換する。

---

### タスク3　APP_DEBUG を false に固定

`config/app.php` を確認し、デバッグ設定が環境変数に依存していることを確認する。

```php
// config/app.php — この形になっていればOK
'debug' => (bool) env('APP_DEBUG', false),
```

次に `.env.example` に以下が記載されていることを確認・追記する:

```
APP_DEBUG=false
```

**注意:** `.env` 本体は Railway の環境変数で管理するためリポジトリには含めない。

---

### タスク4　/terms と /privacy ページの確認・作成

以下のルートが存在するか確認する:

```php
Route::get('/terms', fn() => view('terms'))->name('terms');
Route::get('/privacy', fn() => view('privacy'))->name('privacy');
```

存在しない場合は `routes/web.php` に追加する。

対応するBladeビューが `resources/views/terms.blade.php` と
`resources/views/privacy.blade.php` に存在するか確認する。

存在しない場合は以下の内容で作成する:

```blade
{{-- resources/views/terms.blade.php --}}
<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-12">
        <h1 class="text-2xl font-semibold mb-8">利用規約</h1>
        <p class="text-gray-600 text-sm mb-4">最終更新日：2026年6月1日</p>
        <section class="prose prose-sm text-gray-700 space-y-6">
            <div>
                <h2 class="font-medium text-base mb-2">第1条（適用）</h2>
                <p>本規約は、株式会社スペリオル（以下「当社」）が提供するFormFree（以下「本サービス」）の利用に関する条件を定めるものです。</p>
            </div>
            <div>
                <h2 class="font-medium text-base mb-2">第2条（利用登録）</h2>
                <p>登録希望者が当社の定める方法によって利用登録を申請し、当社がこれを承認することによって、利用登録が完了するものとします。</p>
            </div>
            <div>
                <h2 class="font-medium text-base mb-2">第3条（禁止事項）</h2>
                <p>ユーザーは、本サービスの利用にあたり、以下の行為をしてはなりません。法令または公序良俗に違反する行為、不正アクセス、その他当社が不適切と判断する行為。</p>
            </div>
            <div>
                <h2 class="font-medium text-base mb-2">第4条（免責事項）</h2>
                <p>当社は、本サービスに関して、ユーザーと他のユーザーまたは第三者との間において生じた取引、連絡または紛争等について一切責任を負いません。</p>
            </div>
            <div>
                <h2 class="font-medium text-base mb-2">お問い合わせ</h2>
                <p>株式会社スペリオル<br>Email: info@superior-dx.co.jp</p>
            </div>
        </section>
    </div>
</x-app-layout>
```

```blade
{{-- resources/views/privacy.blade.php --}}
<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-12">
        <h1 class="text-2xl font-semibold mb-8">プライバシーポリシー</h1>
        <p class="text-gray-600 text-sm mb-4">最終更新日：2026年6月1日</p>
        <section class="prose prose-sm text-gray-700 space-y-6">
            <div>
                <h2 class="font-medium text-base mb-2">1. 取得する情報</h2>
                <p>当社は、本サービスの提供にあたり、氏名、メールアドレス、会社名、アップロードされたPDFファイルおよび変換後のデータを取得します。</p>
            </div>
            <div>
                <h2 class="font-medium text-base mb-2">2. 利用目的</h2>
                <p>取得した情報は、本サービスの提供・改善、お問い合わせへの対応、および利用規約に違反する行為への対応のために利用します。</p>
            </div>
            <div>
                <h2 class="font-medium text-base mb-2">3. 第三者提供</h2>
                <p>当社は、法令に基づく場合を除き、ユーザーの同意なく第三者に個人情報を提供しません。</p>
            </div>
            <div>
                <h2 class="font-medium text-base mb-2">4. 安全管理</h2>
                <p>当社は、取得した個人情報について、漏洩・滅失・毀損の防止のために適切な安全管理措置を講じます。</p>
            </div>
            <div>
                <h2 class="font-medium text-base mb-2">5. お問い合わせ</h2>
                <p>株式会社スペリオル<br>Email: info@superior-dx.co.jp</p>
            </div>
        </section>
    </div>
</x-app-layout>
```

---

### タスク5　動作確認

```bash
php artisan route:list | grep -E "terms|privacy"
php artisan config:clear
php artisan view:clear
php artisan test
```

エラーがあれば修正してから次に進む。

---

### タスク6　コミット & プッシュ

```bash
git add .
git commit -m "fix: migrate:fresh→migrate, SUPA_SERVICE_KEY rename, terms/privacy pages, APP_DEBUG=false"
git push origin main
```

プッシュ後、Railwayが自動デプロイを開始する。

---

## 浩章さんが手動でやること（Railway ダッシュボード）

Claude Code が完了した後、以下の2点だけ手動で行う。
所要時間：約5分。

### 手動作業①　環境変数のリネーム

Railway ダッシュボード → `gracious-courage` → `formfree-laravel` サービス
→ Variables タブ

| 操作 | 変数名 | 値 |
|---|---|---|
| リネーム（削除して再作成） | `SUPABASE_KEY` → `SUPA_SERVICE_KEY` | 既存の値をそのままコピー |
| 新規追加 | `PYTHON_CONVERTER_URL` | `http://formfree-python.railway.internal:8000` |
| 新規追加 | `PYTHON_CONVERTER_SECRET` | 任意の長いランダム文字列 |
| 変更 | `APP_DEBUG` | `false` |

Pythonサービス（`formfree-python`）側にも同じ `PYTHON_CONVERTER_SECRET` を追加する。

### 手動作業②　デプロイ確認

Variables 保存後、Railwayが自動再デプロイするのを待つ（1〜2分）。
デプロイ完了後、以下を確認する:

- [ ] ユーザー登録・ログインが動く
- [ ] PDFアップロード → CSV変換が動く
- [ ] `/terms` と `/privacy` が表示される
- [ ] ブラウザのコンソールにエラーが出ていない

---

## まとめ

| 作業 | 担当 | 所要時間 |
|---|---|---|
| Procfile修正 | Claude Code | 自動 |
| Pythonリネーム | Claude Code | 自動 |
| APP_DEBUG設定確認 | Claude Code | 自動 |
| terms/privacyページ作成 | Claude Code | 自動 |
| Railway環境変数の変更 | 浩章さん（手動） | 5分 |
| デプロイ後動作確認 | 浩章さん（手動） | 5分 |
