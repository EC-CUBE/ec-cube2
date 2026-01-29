# EC-CUBE 2 PRIMARY KEY 命名規則

EC-CUBE 2系でテーブルを新規作成する際のPRIMARY KEY命名規則とシーケンス管理について。

## PRIMARY KEY 命名規則

### 基本ルール
- プライマリキー名: `{テーブル名からdtb_を除いた部分}_id`
- 例: `dtb_customer` → `customer_id`
- 例: `dtb_order` → `order_id`
- 例: `dtb_login_attempt` → `login_attempt_id`

### 実例
| テーブル名 | PRIMARY KEY |
|-----------|-------------|
| dtb_customer | customer_id |
| dtb_order | order_id |
| dtb_payment | payment_id |
| dtb_deliv | deliv_id |
| dtb_products | product_id |
| dtb_login_attempt | login_attempt_id |
| dtb_password_reset | password_reset_id |
| dtb_mailmaga_unsubscribe_token | mailmaga_unsubscribe_token_id |

## シーケンス管理

### シーケンス命名規則
- シーケンス名: `dtb_{テーブル名}_{プライマリキー名}_seq`
- 例: `dtb_customer_customer_id_seq`
- 例: `dtb_login_attempt_login_attempt_id_seq`

### シーケンスの登録場所
`eccube_install.sh` の `SEQUENCES` 変数に追加:

```bash
SEQUENCES="
dtb_customer_customer_id_seq
dtb_order_order_id_seq
...
dtb_login_attempt_login_attempt_id_seq
"
```

### PHPでの使用方法
```php
$id = $objQuery->nextVal('dtb_{テーブル名}_{プライマリキー名}');
$sqlval['{プライマリキー名}'] = $id;
$objQuery->insert('dtb_{テーブル名}', $sqlval);
```

## 3つのDBMS対応

新規テーブル作成時は以下の3ファイルに追加:

1. `html/install/sql/create_table_mysqli.sql` (MySQL)
2. `html/install/sql/create_table_pgsql.sql` (PostgreSQL)
3. `html/install/sql/create_table_sqlite3.sql` (SQLite3)

### SQLite3特有の注意点
- `int` → `INTEGER`
- `smallint` → `INTEGER`
- `timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP` → `TEXT NOT NULL DEFAULT (datetime('now','localtime'))`
- `datetime` → `TEXT`
- `varchar(n)` → `text`
- `AUTO_INCREMENT` は不要（INTEGER PRIMARY KEY は自動インクリメント）
- `INDEX` は `CREATE INDEX` で別途作成
- `COMMENT` は使用不可

### SQLite3での日付計算
MySQL/PostgreSQLの `INTERVAL` 構文はSQLite3では使えない:

```php
if (DB_TYPE == 'pgsql') {
    $interval = "create_date > NOW() - INTERVAL '1 hour'";
} elseif (DB_TYPE == 'sqlite3') {
    $interval = "create_date > datetime('now', 'localtime', '-1 hour')";
} else {
    // MySQL
    $interval = "create_date > NOW() - INTERVAL 1 HOUR";
}
```

## _Ex クラスについて

EC-CUBE 2系のオートローダーは `_Ex` クラスが存在しない場合、自動的にベースクラスのエイリアスを作成する:

```php
// SC_ClassAutoloader.php
if (!file_exists($classpath) && str_contains($class, '_Ex')) {
    class_alias(preg_replace('/_Ex$/', '', $class), $class);
}
```

そのため、新規ヘルパークラス追加時に `_Ex` ファイルは**必須ではない**。
コード内では `SC_Helper_XXX_Ex` を使用すればよい。
