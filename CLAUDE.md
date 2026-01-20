# Claude Code 向けガイド

このドキュメントは、Claude Codeを使用してEC-CUBE2の開発を行う際の参考情報を記載しています。

## 開発環境

### Docker と PostgreSQL を使用した環境構築

EC-CUBE2の開発環境は、DockerとPostgreSQLを使用して構築します。

```bash
# 必要な環境変数を設定
export COMPOSE_FILE=docker-compose.yml:docker-compose.pgsql.yml:docker-compose.dev.yml

# docker compose up を実行
docker compose up -d --wait
```

## E2Eテストの実行方法

E2Eテストは Playwright によって作成されています。以下の手順で実行します。

### PostgreSQL の場合

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
yarn install
yarn run playwright install --with-deps chromium
yarn playwright install-deps chromium

# 管理画面の E2E テストを実行
yarn test:e2e e2e-tests/test/admin

# フロント(ゲスト)のE2Eテストを実行
yarn test:e2e --workers=1 e2e-tests/test/front_guest

# フロント(ログイン)のE2Eテストを実行
yarn test:e2e --workers=1 e2e-tests/test/front_login
```

### テスト実行時の注意事項

- `front_guest`と`front_login`のテストは`--workers=1`オプションを付けて、並列実行を避けてください
- テスト実行前に、必ずダミーデータの生成と会員メールアドレスの変更を行ってください
- Docker環境が正しく起動していることを確認してください
