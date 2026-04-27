#!/bin/bash
# ============================================================
# FormFree セットアップスクリプト
# 使い方: bash setup.sh
# ============================================================

set -e

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  FormFree セットアップ"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# ─── Laravel セットアップ ─────────────────────────────────────
echo ""
echo "▶ 1. Composerパッケージのインストール"
cd formfree
composer install --no-dev --optimize-autoloader

echo ""
echo "▶ 2. .envファイルの準備"
cp .env.example .env
echo "  ！ .envファイルを編集してください"
echo "    - DB_HOST, DB_PASSWORD（Supabase）"
echo "    - STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET"
echo "    - STRIPE_PRICE_STANDARD, STRIPE_PRICE_PRO"
echo "    - MAIL_MAILER, RESEND_KEY"
echo "    - PYTHON_CONVERTER_SECRET（任意の64文字）"
read -p "  .envを編集後、Enterを押してください..."

echo ""
echo "▶ 3. アプリケーションキーの生成"
php artisan key:generate

echo ""
echo "▶ 4. データベースマイグレーション"
php artisan migrate --force

echo ""
echo "▶ 5. Stripeライブラリのインストール確認"
composer require stripe/stripe-php

echo ""
echo "▶ 6. キュー用テーブルの作成"
php artisan queue:table
php artisan migrate --force

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Pythonサービスのセットアップ"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
cd ../formfree-python

echo ""
echo "▶ 7. Python仮想環境の作成"
python3 -m venv venv
source venv/bin/activate

echo ""
echo "▶ 8. Pythonパッケージのインストール"
pip install -r requirements.txt

echo ""
echo "▶ 9. .envの準備"
cp .env.example .env
echo "  ！ formfree-python/.envを編集してください"
echo "    - ANTHROPIC_API_KEY"
echo "    - SUPABASE_URL, SUPABASE_KEY"
echo "    - INTERNAL_API_SECRET（Laravelの PYTHON_CONVERTER_SECRET と同じ値）"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Railway デプロイ手順"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  1. railway.app でプロジェクト作成"
echo "  2. formfree/ を 'laravel' サービスとしてデプロイ"
echo "     Start command: php artisan serve --host 0.0.0.0 --port \$PORT"
echo "     Worker command: php artisan queue:work --queue=emails,default --tries=3"
echo "  3. formfree-python/ を 'python-converter' サービスとしてデプロイ"
echo "     Start command: uvicorn main:app --host 0.0.0.0 --port \$PORT"
echo "  4. Private Networkを有効化（Railwayダッシュボード）"
echo "  5. 環境変数を各サービスに設定"
echo ""
echo "  Stripe Webhook URL:"
echo "  https://your-app.railway.app/api/stripe/webhook"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  ✓ セットアップ完了"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
