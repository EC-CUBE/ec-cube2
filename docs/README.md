# 開発ドキュメント

## プラグイン開発

### フックポイント一覧

EC-CUBE2でプラグイン開発を行う際に利用可能なすべてのフックポイントの完全なリファレンスです。

[plugin-hook-points.md](plugin-hook-points.md) をご確認ください。

## テーブル定義書

[k1LoW/tbls](https://github.com/k1LoW/tbls) を使用して、テーブル定義書の自動出力に対応しています。

[database-schema/README.md](database-schema/README.md) をご確認ください。

### テーブル定義書の更新方法

#### 前提条件

[PostgreSQL を使用して、docker-compose で EC-CUBE をインストールしてください](../README.md#postgresql-%E3%82%92%E4%BD%BF%E7%94%A8%E3%81%99%E3%82%8B%E5%A0%B4%E5%90%88)

*MySQL を使用したい場合は、 [.tbls.yml の DSN](.tbls.yml) を適宜修正してください*

#### テーブル・カラムの説明を反映する

テーブル定義書の説明は、PostgreSQL のコメントから生成されます。
[html/install/sql/comment_set_pgsql.sql](../html/install/sql/comment_set_pgsql.sql) を適用してください

``` shell
docker compose exec -T postgres psql --user=eccube_db_user eccube_db < html/install/sql/comment_set_pgsql.sql
```

#### テーブル定義書を更新する

テーブル構成が変更された場合は、以下のコマンドで更新してください

``` shell
docker run --rm -v $PWD:/work ghcr.io/k1low/tbls:v1.96.0 doc -c /work/docs/.tbls.yml --force
```

テーブル定義書が最新であることは CI で検証しています。
CI と同じ tbls のバージョンを使用してください。

#### テーブル定義書との差分を表示する

受託案件などで、 EC-CUBE デフォルトのテーブル構成との差分を見たい場合は以下のコマンドを実行してください

``` shell
docker run --rm -v $PWD:/work ghcr.io/k1low/tbls:v1.96.0 diff -c /work/docs/.tbls.yml
```
