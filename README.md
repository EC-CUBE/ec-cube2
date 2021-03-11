# EC-CUBE 2.17系

[![GitHub Actions status](https://github.com/EC-CUBE/ec-cube2/workflows/CI/CD%20for%20EC-CUBE/badge.svg)](https://github.com/EC-CUBE/ec-cube2/actions)
[![Build Status](https://travis-ci.org/EC-CUBE/ec-cube2.svg)](https://travis-ci.org/EC-CUBE/ec-cube2)
[![AppVeyor](https://img.shields.io/appveyor/ci/ECCUBE/ec-cube2)](https://ci.appveyor.com/project/ECCUBE/ec-cube2/branch/master)
[![codecov](https://codecov.io/gh/EC-CUBE/ec-cube2/branch/master/graph/badge.svg?token=4oNLGhIQwy)](https://codecov.io/gh/EC-CUBE/ec-cube2)
[![PHP Versions Supported](https://img.shields.io/badge/php-%3E%3D%205.4-8892BF.svg)](#php-version-support)
[![GitHub All Releases](https://img.shields.io/github/downloads/EC-CUBE/ec-cube2/total)](https://github.com/EC-CUBE/ec-cube2/releases)
[![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)

---

## EC-CUBE Trac について

EC-CUBE 2.13系については、2014年10月以前に利用されていた、[EC-CUBE Trac](http://svn.ec-cube.net/open_trac/) と[SVN](http://svn.ec-cube.net/open/)がございますので、合わせてご参照ください。
新規のご投稿やコミットはいただけませんが、GitHubに移されていない不具合の情報や過去の経緯などをご確認いただけます。

EC-CUBE Trac にある議論の再開や不具合の修正についても、GitHubにIssueを再作成していただいたり、Pull requestをいただけますと幸いです。

## 開発協力

コードの提供・追加、修正・変更その他「EC-CUBE」への開発の御協力（以下「コミット」といいます）を行っていただく場合には、
[EC-CUBEのコピーライトポリシー](https://github.com/EC-CUBE/ec-cube/wiki/EC-CUBE%E3%81%AE%E3%82%B3%E3%83%94%E3%83%BC%E3%83%A9%E3%82%A4%E3%83%88%E3%83%9D%E3%83%AA%E3%82%B7%E3%83%BC)をご理解いただき、ご了承いただく必要がございます。
Pull requestを送信する際は、EC-CUBEのコピーライトポリシーに同意したものとみなします。

## 開発方針

本リポジトリでは、以下方針で開発を行っています。

### 2.17系

* EC-CUBE 2.13 系の PHP7 対応バージョンです。
* `master` ブランチで開発を行っています。

#### システム要件

| 分類      | ソフトウェア         | Version                                                                 |
|-----------|----------------------|-------------------------------------------------------------------------|
| WebServer | IIS                  | 8.x or higher                                                           |
| WebServer | Apache               | 2.4.x or higher<br> (mod_rewrite / mod_ssl 必須)                        |
| PHP       | PHP                  | 5.4.16 or higher                                                        |
| Database  | PostgreSQL           | 9.x or higher                                                           |
| Database  | MySQL                | 5.x / 8.x or higher<br> (InnoDBエンジン 必須)                           |

##### 必要な PHP Extensions

| 分類           | Extensions                                                                                                                                                                                                                                                                               |
|----------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 必須      | pgsql / mysqli (利用するデータベースに合わせること) <br> pdo_pgsql / pdo_mysql (利用するデータベースに合わせること) <br> pdo <br> mbstring <br> zlib <br> ctype <br> session <br> JSON <br> xml <br> libxml <br> OpenSSL <br> zip <br> cURL <br> gd                                      |
| 推奨      | hash <br> APCu / WinCache (利用する環境に合わせること) <br> Zend OPcache <br> mcrypt                                                                                                                                                                                                     |

## インストール方法

EC-CUBEのインストールは、以下の方法があります。

1. パッケージを使用してインストールする
1. コマンドラインからインストールする

### パッケージを使用してインストールする

[EC-CUBE のパッケージ](https://github.com/EC-CUBE/ec-cube2/releases)をダウンロードし、解凍してください。

FTP/SSHを使用し、ファイルをサーバへアップロードしてください。
※ファイル数が多いためエラーが発生することがございます。エラー発生時は分割してアップロードをお願いします。

データベースを作成し、Webサーバを起動してください。
*DocumentRoot を `{EC-CUBEをアップロードしたディレクトリ}/html` に設定しておく必要があります。*

ブラウザからEC-CUBEにアクセスするとWebインストーラが表示されますので、指示にしたがってインストールしてください。

### コマンドラインからインストールする

- *不具合修正やバージョンアップに追従しやすくしたい場合におすすめです。*

以下をコマンドラインで実行します。

```shell
git clone https://github.com/EC-CUBE/ec-cube2.git
cd ec-cube2
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
php composer.phar install --no-dev --no-interaction -o
```

データベースを作成し、Webサーバを起動してください。
*DocumentRoot を `{EC-CUBEをアップロードしたディレクトリ}/html` に設定しておく必要があります。*

ブラウザからEC-CUBEにアクセスするとWebインストーラが表示されますので、指示にしたがってインストールしてください。

---

- EC-CUBE4系については、 [EC-CUBE/ec-cube](https://github.com/EC-CUBE/ec-cube) にて開発を行っております。
- EC-CUBE3系については、 [EC-CUBE/ec-cube3](https://github.com/EC-CUBE/ec-cube3) にて開発を行っております。
