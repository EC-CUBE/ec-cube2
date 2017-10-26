# EC-CUBE 2.13系 / 2.17系(開発中)
[![Build Status](https://travis-ci.org/EC-CUBE/eccube-2_13.svg)](https://travis-ci.org/EC-CUBE/eccube-2_13)
[![Coverage Status](https://coveralls.io/repos/EC-CUBE/eccube-2_13/badge.png)](https://coveralls.io/r/EC-CUBE/eccube-2_13)

---

## EC-CUBE Trac について

EC-CUBE 2.13系については、2014年10月以前に利用されていた、[EC-CUBE Trac](http://svn.ec-cube.net/open_trac/) と[SVN](http://svn.ec-cube.net/open/)がございますので、合わせてご参照ください。
新規のご投稿やコミットはいただけませんが、GitHubに移されていない不具合の情報や過去の経緯などをご確認いただけます。  

EC-CUBE Trac にある議論の再開や不具合の修正についても、GitHubにIssueを再作成していただいたり、Pull requestをいただけますと幸いです。

## 開発協力

コードの提供・追加、修正・変更その他「EC-CUBE」への開発の御協力（以下「コミット」といいます）を行っていただく場合には、
[EC-CUBEのコピーライトポリシー](https://github.com/EC-CUBE/ec-cube/blob/50de4ac511ab5a5577c046b61754d98be96aa328/LICENSE.txt)をご理解いただき、ご了承いただく必要がございます。
Pull requestを送信する際は、EC-CUBEのコピーライトポリシーに同意したものとみなします。

## 開発方針

本リポジトリでは、以下方針で開発を行っています。

### 2.13系

* 保守と不具合修正を行います。
* 修正時は `master` に対してPull requestを作成してください。

### 2.17系(開発中)

* EC-CUBE 2.13系に対して、PHP7対応を行うバージョンです。
* ブランチ `improve/php7` で開発を行っています。  
修正時は `improve/php7` に対してPull requestを作成してください。
* 2.17系に関連するIssueについては、[マイルストーン 2.17.0](https://github.com/EC-CUBE/eccube-2_13/milestone/5)を参照してください。

##### 2.17系 システム要件の変更

動作にはPHP5.4以降が必要になります。

##### 2.17系 インストールについて

Composerを導入に伴い、clone後に`composer install`の実行が必要です。

---

上記に含まれない新規機能開発や構造の変化を伴う修正等については、[EC-CUBE/ec-cube](https://github.com/EC-CUBE/ec-cube)にて開発を行っております。
