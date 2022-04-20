# 開発ドキュメント

## テーブル定義書

[k1LoW/tbls](https://github.com/k1LoW/tbls) を使用して、テーブル定義書の自動出力に対応しています。

[database-schema/README.md](database-schema/README.md) をご確認ください。

### テーブル定義書の更新方法

#### 前提条件

[PostgreSQL を使用して、docker-compose で EC-CUBE をインストールしてください](../README.md#postgresql-%E3%82%92%E4%BD%BF%E7%94%A8%E3%81%99%E3%82%8B%E5%A0%B4%E5%90%88)

*MySQL を使用したい場合は、 [.tbls.yml の DSN](.tbls.yml) を適宜修正してください*

#### テーブル定義書を更新する

テーブル構成が変更された場合は、以下のコマンドで更新してください

``` shell
docker run --rm -v $PWD:/work ghcr.io/k1low/tbls doc -c /work/docs/.tbls.yml --force
```

#### テーブル定義書との差分を表示する

受託案件などで、 EC-CUBE デフォルトのテーブル構成との差分を見たい場合は以下のコマンドを実行してください

``` shell
docker run --rm -v $PWD:/work ghcr.io/k1low/tbls diff -c /work/docs/.tbls.yml
```
