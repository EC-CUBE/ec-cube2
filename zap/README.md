# EC-CUBE Penetration Testing with OWASP ZAP

このツールは、サイトを実際に攻撃し、脆弱性が無いかを確認するツールです。
必ずローカル環境の Docker でのみ使用し、稼動中のサイトには決して使用しないでください。
意図せずデータが更新されたり、削除される場合があります。
テストは自己責任で実施し、株式会社イーシーキューブ及び、関連する開発コミュニティは一切の責任を負いかねますのであらかじめご了承ください。

## Quick Start

**Attention!** 意図しない外部サイトへの攻撃を防ぐため、 OWASP ZAP は必ず **プロテクトモード** で使用してください

1. docker-compose を使用して EC-CUBE をインストールします
    ```shell
    # MySQL を使用する例
    export COMPOSE_FILE=docker-compose.yml:docker-compose.mysql.yml:docker-compose.dev.yml:docker-compose.owaspzap.yml:docker-compose.owaspzap.daemon.yml
    docker-compose up -d
    # PostgreSQL を使用する例
    export COMPOSE_FILE=docker-compose.yml:docker-compose.pgsql.yml:docker-compose.dev.yml:docker-compose.owaspzap.yml:docker-compose.owaspzap.daemon.yml
    docker-compose up -d
1. テスト用のデータを生成します    ```
    ```shell
    # MySQL を使用する例
    ## ec-cube2/cli をインストールしておく
    docker-compose exec ec-cube composer install
    docker-compose exec -T ec-cube composer require ec-cube2/cli "dev-master@dev" --ignore-platform-req=php -W
    docker-compose exec -T ec-cube composer update 'symfony/*' --ignore-platform-req=php -W
    ## ダミーデータを生成
    docker-compose exec -T ec-cube php data/vendor/bin/eccube eccube:fixtures:generate --products=5 --customers=1 --orders=5
    ## メールアドレスを zap_user@example.com に変更
    docker-compose exec mysql mysql --user=eccube_db_user --password=password eccube_db -e "UPDATE dtb_customer SET email = 'zap_user@example.com' WHERE customer_id = (SELECT customer_id FROM (SELECT MAX(customer_id) FROM dtb_customer WHERE status = 2 AND del_flg = 0) AS A);"

    # PostgreSQL を使用する例
    ## ec-cube2/cli をインストールしておく
    docker-compose exec ec-cube composer install
    docker-compose exec -T ec-cube composer require ec-cube2/cli "dev-master@dev" --ignore-platform-req=php -W
    docker-compose exec -T ec-cube composer update 'symfony/*' --ignore-platform-req=php -W
    ## ダミーデータを生成
    docker-compose exec -T ec-cube php data/vendor/bin/eccube eccube:fixtures:generate --products=5 --customers=1 --orders=5
    ## メールアドレスを zap_user@example.com に変更
    docker-compose exec postgres psql --user=eccube_db_user eccube_db -c "UPDATE dtb_customer SET email = 'zap_user@example.com' WHERE customer_id = (SELECT MAX(customer_id) FROM dtb_customer WHERE status = 2 AND del_flg = 0);"
    ```
1. OWASP ZAP を起動します。Firefox 以外のブラウザで `http://localhost:8081/zap/` へアクセスすると、OWASP ZAP の管理画面が表示されます
1. Firefox を起動し、設定→ネットワーク設定→接続設定からプロキシーの設定をします
   - **手動でプロキシーを設定する** を選択
   - HTTPプロキシー: localhost, ポート: 8090
   - **このプロキシーを FTP と HTTPS でも使用する** にチェックを入れる
1. Firefox に SSL ルート CA 証明書をインポートします
   - ローカルの `path/to/ec-cube/zap/owasp_zap_root_ca.cer` に証明書が生成されています
   - 設定→プライバシーとセキュリティ→証明書→証明書を表示から証明書マネージャーを表示
   - 認証局証明書→読み込むをクリックし、 `path/to/ec-cube/zap/owasp_zap_root_ca.cer` を選択
   - **この認証局によるウェブサイトの識別を信頼する** にチェックを入れ、 OK をクリック、設定を閉じます
1. Firefox で `https://ec-cube/` へアクセスし、プロキシー経由で EC-CUBE にアクセスできるのを確認します。
1. コンテキストをインポートします。
    ```shell
    ## 管理画面用
    docker-compose exec zap zap-cli -p 8090 context import /zap/wrk/admin.context
    ## フロント(ログイン用)
    docker-compose exec zap zap-cli -p 8090 context import /zap/wrk/front_login.context
    ## フロント(ゲスト用)
    docker-compose exec zap zap-cli -p 8090 context import /zap/wrk/front_guest.context
    ```
   **Note:** *複数のコンテキストを同時にインポートすると、セッションが競合してログインできなくなる場合があるため注意*
   {: .notice--warning}
1. OWASP ZAP のツールバーにある [Forced User Mode On/Off ボタン](https://www.zaproxy.org/docs/desktop/ui/tltoolbar/#--forced-user-mode-on--off) を ON にすると、OWASP ZAP の自動ログインが有効になり、ユーザーログイン中のテストが有効になります
1. テストを実施します
   1. Firefox でページを巡回(手動探索)します
   1. 手動探索して検出された URL に対して動的スキャンを実施します
   1. アラートの検出を確認します
