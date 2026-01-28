# Issue #1301 実装進捗メモ

## 実装状況

### 完了した実装
- ✅ レート制限機能の実装（SC_Helper_LoginRateLimit.php）
- ✅ マイページログイン処理の改善（LC_Page_FrontParts_LoginCheck.php）
- ✅ ログインエラーの同一ページ表示（セッション経由）
- ✅ タイムゾーン問題の修正（DB NOW()関数の使用）
- ✅ バリデーションエラーも失敗として記録
- ✅ 手動動作確認完了（レート制限が6回失敗後に正常動作）

### 未完了の実装
- ⚠️ ショッピングカートログイン処理の改善（LC_Page_Shopping.php）- 計画に含まれているが未着手
- ⚠️ スクリーンショットキャプチャ（03-05のショッピングカート関連）
- ⚠️ テストの修正とCI通過確認

## Docker環境でのテストセットアップ手順

### 1. docker-compose.ymlの修正

EC-CUBE 2系のディレクトリ構造に合わせてボリュームマウントを修正:

```yaml
volumes:
  ### カレントディレクトリをマウント
  - ".:/var/www/app"
  ### 同期対象からコストの重いフォルダを除外
  - "vendor:/var/www/app/data/vendor"
```

### 2. データベースのセットアップ

PostgreSQLを使用してeccube_install.shでデータベースを初期化:

```bash
# Dockerコンテナに入る
docker exec -it ec-cube2-test-apache bash

# eccube_install.shを実行
cd /var/www/app
./eccube_install.sh pgsql
```

### 3. config.phpの修正

`data/config/config.php` を以下のように修正:

```php
// URL設定
defined('HTTP_URL') or define('HTTP_URL', 'http://localhost:8080/');
defined('HTTPS_URL') or define('HTTPS_URL', 'http://localhost:8080/');

// PostgreSQL設定
defined('DB_TYPE') or define('DB_TYPE', 'pgsql');
defined('DB_USER') or define('DB_USER', 'postgres');
defined('DB_PASSWORD') or define('DB_PASSWORD', 'password');
defined('DB_SERVER') or define('DB_SERVER', 'ec-cube2-test-postgres');
defined('DB_NAME') or define('DB_NAME', 'eccube_db');
defined('DB_PORT') or define('DB_PORT', '5432');
```

### 4. Dockerコンテナの再起動

```bash
docker compose restart
```

### 5. 手動動作確認

http://localhost:8080/mypage/login.php で:
- 6回ログイン失敗を試行
- 7回目でレート制限エラーメッセージが表示されることを確認
- エラーが同一ページに表示されることを確認（別ページにリダイレクトされない）

## 実装中に解決した問題

### 問題1: レート制限のカウントが常に0

**原因**:
- PostgreSQLのタイムスタンプがUTC、PHPのdate()がJSTを使用
- タイムゾーンのミスマッチで、1時間前の条件が正しく動作しない

**解決策**:
```php
// 修正前（動作しない）
$threshold = date('Y-m-d H:i:s', strtotime('-1 hour'));
$where = "create_date > ? AND ...";

// 修正後（動作する）
if (DB_TYPE == 'pgsql') {
    $interval_clause = "create_date > NOW() - INTERVAL '1 hour'";
} else {
    $interval_clause = 'create_date > NOW() - INTERVAL 1 HOUR';
}
$where = "login_id = ? AND result = 0 AND {$interval_clause}";
```

データベースのNOW()関数を使用することで、タイムゾーンの一貫性を保証。

### 問題2: バリデーションエラーがレート制限にカウントされない

**原因**:
- バリデーションエラー（空のメールアドレス、不正な形式など）が `recordLoginAttempt` を呼んでいなかった
- レート制限をバイパス可能な脆弱性

**解決策**:
`LC_Page_FrontParts_LoginCheck.php` の Line 118-136 にバリデーションエラー時の記録を追加:

```php
$arrErr = $objFormParam->checkError();

if (count($arrErr) > 0) {
    // バリデーションエラーの場合
    $this->arrErr['login'] = 'メールアドレスもしくはパスワードが正しくありません。';

    // バリデーションエラーも失敗として記録
    SC_Helper_LoginRateLimit_Ex::recordLoginAttempt($login_email, $ip_address, $user_agent, 0);

    // エラー処理...
}
```

### 問題3: Dockerでコード変更が反映されない

**原因**:
- ボリュームマウントが設定されていなかった

**解決策**:
- docker-compose.ymlにボリュームマウント追加
- OPcacheクリアが必要な場合: `docker compose restart`

## 変更ファイル一覧

### コア実装
- `data/class/helper/SC_Helper_LoginRateLimit.php` - レート制限ヘルパー（タイムゾーン修正）
- `data/class/pages/frontparts/LC_Page_FrontParts_LoginCheck.php` - ログインチェックページ（バリデーションエラー記録追加）
- `html/mypage/login.php` - セッションからエラー取得
- `data/Smarty/templates/default/mypage/login.tpl` - エラー表示追加

### テスト
- `tests/class/helper/SC_Helper_LoginRateLimit/SC_Helper_LoginRateLimitTest.php` - ヘルパークラステスト

### データベース
- `html/install/sql/create_table_mysqli.sql` - dtb_login_attemptテーブル定義
- `html/install/sql/create_table_pgsql.sql` - dtb_login_attemptテーブル定義
- `eccube_install.sh` - dtb_login_attemptシーケンス追加

### 環境設定
- `docker-compose.yml` - ボリュームマウント設定

## 残作業

### 必須
1. **ショッピングカートログイン処理の改善**
   - `data/class/pages/shopping/LC_Page_Shopping.php` の修正
   - 計画には含まれているが、マイページログインのみ実装済み
   - ショッピングカートでも同様のエラー表示とレート制限を実装する必要あり

2. **テストの修正**
   - タイムゾーン修正に伴うテストの更新が必要
   - CIで通るか確認

3. **スクリーンショットキャプチャ**
   - 01: マイページログイン通常 ✅
   - 02: マイページログインエラー ✅
   - 03: ショッピングカートログイン通常 ❌
   - 04: ショッピングカートログインエラー ❌
   - 05: レート制限エラー（6回失敗後）❌ または手動スクリーンショット

### 任意
- テンプレートの他のデバイス対応確認（スマートフォン、モバイル）
- 管理画面での統計表示機能（将来拡張）
- バッチ処理での古いレコード削除（将来拡張）

## 動作確認済み

✅ マイページログイン画面（/mypage/login.php）
- ログインエラーが同一ページに表示される
- 6回失敗後、レート制限エラーメッセージが表示される
- セッションを介したエラーメッセージの受け渡しが動作

❌ ショッピングカートログイン画面（/shopping/）
- 未実装（LC_Page_Shopping.phpの修正が必要）

## 注意事項

### タイムゾーン
- PostgreSQLはUTCでタイムスタンプを保存
- MySQLもタイムゾーン設定に依存
- 必ずデータベースのNOW()関数を使用すること（PHP date()は使わない）

### レート制限ルール
- 同一メールアドレス: 1時間に6回失敗でブロック（5回まで許可）
- 同一IPアドレス: 1時間に11回失敗でブロック（10回まで許可）
- バリデーションエラーもカウント対象

### セキュリティ
- すべてのログイン試行を記録（監視用）
- アカウント列挙攻撃対策として、存在しないメールアドレスも同じエラーメッセージ
- タイミング攻撃対策としてエラーメッセージを統一

## 次のステップ

1. ショッピングカートログイン処理の実装
2. テストの修正とCI通過確認
3. スクリーンショットの追加（または手動取得）
4. PR #1302の更新と最終レビュー
