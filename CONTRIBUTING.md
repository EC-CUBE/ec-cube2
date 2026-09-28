# EC-CUBE 2 へのコントリビューション

EC-CUBE 2 への開発協力に関心をお寄せいただきありがとうございます。
Pull Request を送信する前に、本ドキュメントをご確認ください。

## コピーライトポリシー

Pull Request を送信する際は、[EC-CUBEのコピーライトポリシー](https://github.com/EC-CUBE/ec-cube/wiki/EC-CUBE%E3%81%AE%E3%82%B3%E3%83%94%E3%83%BC%E3%83%A9%E3%82%A4%E3%83%88%E3%83%9D%E3%83%AA%E3%82%B7%E3%83%BC)に同意したものとみなします。

## 開発環境とテスト

開発環境の構築方法やテストの実行方法は [README.md](README.md) を参照してください。

EC-CUBE 2 は MySQL / PostgreSQL / SQLite3 をサポートしています。データベースに関わる変更は、すべてのデータベースで動作するようにしてください。

## データベースの変更

テーブル・カラム・インデックスの追加や変更、初期データの追加や変更を行う場合は、
**マイグレーションとインストール用 SQL の両方を更新してください。**

- インストール用 SQL (`html/install/sql/`) は、最新バージョンの完全なスキーマと初期データを表します。新規インストールで使用されます。
- マイグレーション (`data/migrations/`) は、既存のサイトを最新バージョンへアップグレードするために使用されます。

新規インストール時はマイグレーションを実行せず、`data/migrations/` のすべてのマイグレーションを適用済みとして `dtb_migration` に登録します。
そのため、どちらか一方だけを更新すると、新規インストールした環境とアップグレードした環境でデータベースの状態が食い違います。

### 更新対象のファイル

| 変更内容 | 更新するファイル |
|---|---|
| テーブルの追加・変更 | `data/migrations/Version*.php`<br>`html/install/sql/create_table_mysqli.sql`<br>`html/install/sql/create_table_pgsql.sql`<br>`html/install/sql/create_table_sqlite3.sql` |
| テーブルの追加・削除 | 上記に加えて `html/install/sql/drop_table.sql` |
| テーブル・カラムの説明 | `html/install/sql/comment_set_pgsql.sql` |
| 初期データの追加・変更 | `data/migrations/Version*.php`<br>`html/install/sql/insert_data.sql` |
| 上記いずれかの変更 | テーブル定義書 (`docs/database-schema/`) の再生成 |

### マイグレーションの作成

マイグレーションは [ec-cube2-migration](https://github.com/nobuhiko/ec-cube2-migration) を使用しています。
以下のコマンドで `data/migrations/` にマイグレーションファイルの雛形を作成できます。

```shell
php data/vendor/bin/eccube migrate:create <Name>
```

- ファイル名は `Version<日時>_<内容>.php` の形式です。
- `down()` にはロールバック処理を記述してください。
- `sql()` はパラメータのバインドに対応していません。第2引数・第3引数は、それぞれ PostgreSQL・SQLite3 向けの SQL です。
- マイグレーションでカラムを追加すると、テーブルの末尾に追加されます。インストール用 SQL でも、カラムはテーブル定義の末尾に追加してください。
- `dtb_migration` への登録はインストーラが行います。インストール用 SQL に記述する必要はありません。

### CI による検証

Pull Request では、以下を CI で検証します (`.github/scripts/test-migration.sh`)。

- 新規インストールした環境で、すべてのマイグレーションが適用済みとして登録されていること
- EC-CUBE 2.25.0 のインストール用 SQL でインストールしてからマイグレーションを実行した環境と、新規インストールした環境のスキーマが一致すること (MySQL / PostgreSQL)
- テーブル定義書 (`docs/database-schema/`) が最新であること (PostgreSQL)

初期データの差分は検証対象外です。`insert_data.sql` とマイグレーションの内容が一致していることは、Pull Request の作成者とレビュアーが確認してください。

### テーブル定義書の再生成

テーブル定義書は [k1LoW/tbls](https://github.com/k1LoW/tbls) で生成しています。
再生成の手順は [docs/README.md](docs/README.md) を参照してください。

### メンテナによる補完

マイグレーションとインストール用 SQL のどちらか一方しか含まれていない Pull Request は、
メンテナが不足している側を追加する場合があります。
すべてのデータベース向けの SQL を用意することが難しい場合は、Pull Request の説明にその旨を記載してください。
