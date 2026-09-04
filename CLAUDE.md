# プロジェクト基本情報

## プロジェクト概要

**EC-CUBE 2** - オープンソースのECサイト構築パッケージ

- リポジトリ: https://github.com/EC-CUBE/ec-cube2
- ライセンス: GPL
- バージョン: 2.25.0

## 技術スタック

### バックエンド
- PHP 7.4 / 8.0 / 8.1 / 8.2 / 8.3 / 8.4 / 8.5
- Smarty (テンプレートエンジン)
- PHPUnit (ユニットテスト)

### データベース
- MySQL 8.4
- PostgreSQL 16

### フロントエンド
- jQuery 3.7.1
- esbuild

### E2Eテスト
- Playwright 1.52.0
- Docker Compose

## ディレクトリ構造

```
ec-cube2/
├── data/                     # アプリケーションコア
│   ├── class/               # クラスファイル
│   │   ├── pages/          # ページクラス
│   │   ├── helper/         # ヘルパークラス
│   │   └── util/           # ユーティリティ
│   └── Smarty/             # Smartyテンプレート
│       └── templates/
│           ├── default/    # PC用テンプレート
│           └── sphone/     # スマートフォン用テンプレート
├── html/                    # 公開ディレクトリ
│   ├── admin/              # 管理画面
│   └── install/            # インストーラー
│       └── sql/            # DDL/DML
├── tests/                   # PHPUnitテスト
│   └── class/
├── e2e-tests/               # Playwrightテスト
│   ├── pages/              # Page Objectパターン
│   └── tests/
└── docker-compose*.yml      # Docker構成
```

## 環境構築

### 必要なツール
- Docker & Docker Compose
- Node.js (v22推奨)
- npm (Node.js同梱)

### E2Eテストの実行方法

E2Eテストは Playwright によって作成されています。

#### PostgreSQL の場合

```bash
# 必要な環境変数を設定
export COMPOSE_FILE=docker-compose.yml:docker-compose.pgsql.yml:docker-compose.dev.yml

# docker compose up を実行
docker compose up -d --wait

# ダミーデータ生成
docker compose exec -T ec-cube composer install
docker compose exec -T ec-cube composer require ec-cube2/cli "dev-master@dev" -W
docker compose exec -T ec-cube composer update 'symfony/*' -W
docker compose exec -T ec-cube php data/vendor/bin/eccube eccube:fixtures:generate --products=5 --customers=1 --orders=5

# 会員のメールアドレスを zap_user@example.com へ変更
docker compose exec -T postgres psql --user=eccube_db_user eccube_db -c "UPDATE dtb_customer SET email = 'zap_user@example.com' WHERE customer_id = (SELECT MAX(customer_id) FROM dtb_customer WHERE status = 2 AND del_flg = 0);"

# playwright をインストール
npm install
npx playwright install --with-deps chromium
npx playwright install-deps chromium

# 管理画面の E2E テストを実行
npm run test:e2e -- e2e-tests/test/admin

# フロント(ゲスト)のE2Eテストを実行
npm run test:e2e -- --workers=1 e2e-tests/test/front_guest

# フロント(ログイン)のE2Eテストを実行
npm run test:e2e -- --workers=1 e2e-tests/test/front_login
```

#### MySQL の場合

```bash
# 環境変数を設定
export COMPOSE_FILE=docker-compose.yml:docker-compose.mysql.yml:docker-compose.dev.yml

# docker compose up を実行
docker compose up -d --wait

# ダミーデータ生成
docker compose exec -T ec-cube composer install
docker compose exec -T ec-cube composer require ec-cube2/cli "dev-master@dev" -W
docker compose exec -T ec-cube composer update 'symfony/*' -W
docker compose exec -T ec-cube php data/vendor/bin/eccube eccube:fixtures:generate --products=5 --customers=1 --orders=5

# 会員のメールアドレスを zap_user@example.com へ変更
docker compose exec mysql mysql --user=eccube_db_user --password=password eccube_db -e "UPDATE dtb_customer SET email = 'zap_user@example.com' WHERE customer_id = (SELECT customer_id FROM (SELECT MAX(customer_id) FROM dtb_customer WHERE status = 2 AND del_flg = 0) AS A);"

# playwright をインストール
npm install
npx playwright install --with-deps chromium
npx playwright install-deps chromium

# 管理画面の E2E テストを実行
npm run test:e2e -- e2e-tests/test/admin

# フロント(ゲスト)のE2Eテストを実行
npm run test:e2e -- --workers=1 e2e-tests/test/front_guest

# フロント(ログイン)のE2Eテストを実行
npm run test:e2e -- --workers=1 e2e-tests/test/front_login
```

# ワークフロー

## 重要な注意事項

**push する前に必ずローカルで unit-test と e2e-test を実行して、動作確認すること**

```bash
# PHPUnitテスト実行
docker compose exec php-mysql vendor/bin/phpunit
docker compose exec php-pgsql vendor/bin/phpunit

# E2Eテスト実行（MySQL）
export COMPOSE_FILE=docker-compose.yml:docker-compose.mysql.yml:docker-compose.dev.yml
docker compose up -d --wait
npm run test:e2e

# E2Eテスト実行（PostgreSQL）
export COMPOSE_FILE=docker-compose.yml:docker-compose.pgsql.yml:docker-compose.dev.yml
docker compose up -d --wait
npm run test:e2e
```

## 開発フロー

### 1. ブランチ作成

```bash
# 機能追加
git checkout -b feature/issue-XXX-description

# バグ修正
git checkout -b fix/issue-XXX-description

# 依存関係更新
git checkout -b fix/dependabot-security-updates
```

### 2. 実装

```bash
# フロントエンドのビルド（開発モード、watch）
npm run dev

# コードスタイル修正
docker compose exec php-mysql vendor/bin/php-cs-fixer fix
```

### 3. テスト実行（必須）

**push前に必ず実行すること**

#### PHPUnitテスト

```bash
# 全テスト実行（MySQL）
docker compose exec php-mysql vendor/bin/phpunit

# 全テスト実行（PostgreSQL）
docker compose exec php-pgsql vendor/bin/phpunit

# 特定のテストクラス実行
docker compose exec php-mysql vendor/bin/phpunit tests/class/SC_CartSession/SC_CartSessionTest.php

# 特定のテストメソッド実行
docker compose exec php-mysql vendor/bin/phpunit --filter testGetAllProductsTotal
```

#### E2Eテスト

```bash
# 全E2Eテスト実行（MySQL）
export COMPOSE_FILE=docker-compose.yml:docker-compose.mysql.yml:docker-compose.dev.yml
docker compose up -d --wait
npm run test:e2e

# 全E2Eテスト実行（PostgreSQL）
export COMPOSE_FILE=docker-compose.yml:docker-compose.pgsql.yml:docker-compose.dev.yml
docker compose up -d --wait
npm run test:e2e

# 拡張テスト実行
npm run test:e2e-extends

# セキュリティテスト（ZAP Proxy）
npm run test:attack
```

### 4. コミット

```bash
git add .
git commit -m "Issue #XXX: 説明"
```

### 5. プルリクエスト作成

```bash
# ブランチをプッシュ
git push origin feature/issue-XXX-description

# GitHub CLIでPR作成
gh pr create --title "Issue #XXX: 説明" --body "$(cat <<'EOF'
## Summary
- 変更内容の要約

## Test plan
- [ ] PHPUnit全テスト通過（MySQL & PostgreSQL）
- [ ] E2Eテスト全テスト通過（MySQL & PostgreSQL）
- [ ] 手動テスト完了

🤖 Generated with Claude Code
EOF
)"
```

