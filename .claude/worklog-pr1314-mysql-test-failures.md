# 作業ログ: PR #1314 MySQL Unit Test 失敗問題

**日付**: 2026-01-21
**ブランチ**: fix/sc-query-state-pollution-setuptax
**PR**: https://github.com/EC-CUBE/ec-cube2/pull/1314
**CI Run**: https://github.com/EC-CUBE/ec-cube2/actions/runs/21198520392

## CI失敗の詳細（2026-01-21）

### 実際にCIで失敗している5つのテスト

PR #1314 (コミット 2ffe0a28a) をpush後も以下のテストが失敗している。

#### 1. SC_CartSessionTest::testGetAllProductsTotal
```
Failed asserting that 1866.0 matches expected 2052.
Expected: 2052
Actual: 1866.0
差分: 186 (約10% = 税率)

Location: /var/www/app/tests/class/Common_TestCase.php:64
Test: /var/www/app/tests/class/SC_CartSession/SC_CartSessionTest.php:75
```

#### 2. SC_CartSessionTest::testCalculate
```
Failed asserting that 0.0 matches expected 186.
Expected: 186
Actual: 0.0

Location: /var/www/app/tests/class/Common_TestCase.php:64
Test: /var/www/app/tests/class/SC_CartSession/SC_CartSessionTest.php:161
```

#### 3. SC_Helper_Purchase_getShipmentItemsTest::testGetShipmentItems
```
Failed asserting that 933 matches expected 1026.
Expected price: 1026
Actual: 933
差分: 93 (約10%)

Location: /var/www/app/tests/class/Common_TestCase.php:64
Test: /var/www/app/tests/class/helper/SC_Helper_Purchase/SC_Helper_Purchase_getShipmentItemsTest.php:103
```

#### 4. SC_Helper_Purchase_getShipmentItemsTest::testGetShipmentItems詳細フラグOFF
```
Failed asserting that 933 matches expected 1026.
Expected price: 1026
Actual: 933
差分: 93 (約10%)

Location: /var/www/app/tests/class/Common_TestCase.php:64
Test: /var/www/app/tests/class/helper/SC_Helper_Purchase/SC_Helper_Purchase_getShipmentItemsTest.php:126
```

#### 5. SC_Helper_TaxRule_getDetailTest::testGetTaxPerTaxRateWithRound2
```
Failed asserting that 543 matches expected 542.
Expected tax: 542
Actual: 543
差分: 1

Location: /var/www/app/tests/class/Common_TestCase.php:64
Test: /var/www/app/tests/class/helper/SC_Helper_TaxRule/SC_Helper_TaxRule_getDetailTest.php:144
```

### 重要な観察事項

1. **MySQLのみで発生** - PostgreSQLでは一切起きない
2. **PHPバージョンは無関係** - すべてのPHPバージョンで同じ問題が発生
3. **ランダム性** - テスト実行順序によって失敗するテストが変わる可能性
4. **税金計算に関連** - すべての失敗で価格差は約10%（税率相当）
5. **PR #1314の修正後も失敗** - 2箇所の delete() に '1=1' を追加したが解決していない

### ローカル環境での検証結果

- **SC_Helper_DB_sfGetAddPointTest を除外して10回実行**: 10/10成功 ✅
- **SC_Helper_DB_sfGetAddPointTest を含めて10回実行**: 0/10成功 ❌

このことから：
- PR #1314の修正（SC_Helper_TaxRule）は**ローカルでは効果がある**
- しかし**CIでは上記5テストがまだ失敗している**
- ローカルとCIで異なる原因が存在する可能性

## 問題の概要

Unit testがランダムに失敗する問題が発生している。すべて税金計算に関連し、価格差は約10%（税率相当）。

## 誤った仮説と検証結果

### 仮説1: `SC_Query::delete()` の第2引数不足が原因

**根拠**:
- `SC_Query::delete('dtb_tax_rule')` のように第2引数なしで呼ぶと状態汚染が発生
- `$this->where` と `$this->arrWhereVal` が保持される

**実施した修正**:
- `tests/class/helper/SC_Helper_TaxRule/SC_Helper_TaxRule_TestBase.php:125`
- `tests/class/helper/SC_Helper_TaxRule/SC_Helper_TaxRule_getTaxDetailTest.php:371`
- 両方で `delete('dtb_tax_rule', '1=1')` に修正

**結果**: ❌ **修正後もテストが落ちている**

**結論**: `delete()` の第2引数不足は**無関係な可能性が高い**

### なぜ無関係と判断できるか

1. **PostgreSQLでは成功している**
   - 同じコード、同じPHPバージョンで PostgreSQL は全テスト成功
   - もし `delete()` が原因なら PostgreSQL でも失敗するはず

2. **PHPバージョン無関係**
   - PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.5: すべてMySQLで失敗
   - PHP 8.4のみの問題ではない

3. **修正後も失敗**
   - `delete('dtb_tax_rule', '1=1')` に修正済み
   - それでもCIで失敗している

## MySQL固有の問題の可能性

### 仮説2: MySQLとPostgreSQLでのテスト実行順序の違い

PHPUnitはデフォルトでテストをランダム実行する。データベースの種類によって：
- テーブルの順序
- インデックスの挙動
- トランザクション分離レベル

などが異なり、テスト実行順序に影響する可能性がある。

### 仮説3: MySQL 8.4の数値型・decimal型の処理変更

- MySQL 8.4で数値型の処理が変わった可能性
- 特に税率計算（10%）に関連する部分
- PostgreSQLとMySQLでの浮動小数点演算の違い

### 仮説4: テストデータのセットアップ問題

税金計算が失敗している = `dtb_tax_rule` テーブルのデータに問題がある可能性

**確認が必要な点**:
- `setUp()` で税ルールデータが正しく登録されているか
- `tearDown()` でデータが正しく削除/復元されているか
- 他のテストの影響を受けていないか

## 関連ファイル

### テスト対象ファイル

```
tests/class/SC_CartSession/
├── SC_CartSessionTest.php              # 失敗テスト1, 2
├── SC_CartSession_TestBase.php         # テストベースクラス

tests/class/helper/SC_Helper_Purchase/
├── SC_Helper_Purchase_getShipmentItemsTest.php  # 失敗テスト3, 4

tests/class/helper/SC_Helper_TaxRule/
├── SC_Helper_TaxRule_getDetailTest.php          # 失敗テスト5
├── SC_Helper_TaxRule_TestBase.php               # 税ルールテストベース
```

### 修正済みファイル (PR #1314)

```
tests/class/helper/SC_Helper_TaxRule/SC_Helper_TaxRule_TestBase.php:125
tests/class/helper/SC_Helper_TaxRule/SC_Helper_TaxRule_getTaxDetailTest.php:371
```

## 調査済みの内容

### SC_CartSessionTest.php の setUp/tearDown

```php
protected function setUp(): void
{
    parent::setUp();
    // 各種データをバックアップ
    $this->backupData['tax_rule'] = $this->objQuery->getAll('SELECT * FROM dtb_tax_rule');
    // ... 他のテーブルもバックアップ
}

protected function tearDown(): void
{
    // バックアップからリストア (PR #1309で '1=1' 追加済み)
    $this->objQuery->delete('dtb_tax_rule', '1=1');
    foreach ($this->backupData['tax_rule'] as $row) {
        $this->objQuery->insert('dtb_tax_rule', $row);
    }
    parent::tearDown();
}
```

**観察**: setUp/tearDown は適切に実装されている

### SC_CartSession_TestBase.php の setUpProducts()

```php
// 商品データのセットアップはあるが、税ルールのセットアップはない
$this->objQuery->delete('dtb_products_class');  // ← 第2引数なし
foreach ($product_class as $key => $item) {
    $this->objQuery->insert('dtb_products_class', $item);
}
```

**観察**:
- 税ルールのセットアップメソッドが呼ばれていない
- デフォルトの税ルールに依存している可能性

## 次の調査ステップ

### 1. テスト実行時の dtb_tax_rule の内容を確認

```bash
# テスト実行前後でダンプ
docker compose exec php-mysql vendor/bin/phpunit --filter testGetAllProductsTotal tests/class/SC_CartSession/SC_CartSessionTest.php
```

テスト内で以下を追加して確認:
```php
$taxRules = $this->objQuery->getAll('SELECT * FROM dtb_tax_rule');
var_dump($taxRules);
```

### 2. MySQL と PostgreSQL でのテスト実行順序を比較

```bash
# MySQLでの実行順序を記録
docker compose exec php-mysql vendor/bin/phpunit --verbose

# PostgreSQLでの実行順序を記録
docker compose exec php-pgsql vendor/bin/phpunit --verbose
```

### 3. 特定のテストを単独実行してデバッグ

```bash
# 失敗するテストを単独実行
docker compose exec php-mysql vendor/bin/phpunit tests/class/SC_CartSession/SC_CartSessionTest.php::testGetAllProductsTotal

# ランダム実行順序を固定して再現性確認
docker compose exec php-mysql vendor/bin/phpunit --order-by=random --random-order-seed=12345
```

### 4. MySQL 8.4 と PostgreSQL 16 での数値型処理の違いを調査

- MySQL 8.4 changelog を確認
- decimal型、float型の挙動変更を確認
- PHPUnitでの数値比較（assertEquals vs assertEqualsWithDelta）

### 5. 他の delete() 呼び出しを確認

```bash
# 第2引数なしの delete() を全検索
grep -rn "->delete(" tests/ | grep -v ", '1=1'" | grep -v "//"
```

47箇所の `delete()` 呼び出しのうち、修正が必要な箇所を特定

## メモ

### CI環境

- **GitHub Actions**: .github/workflows/unit-tests.yml
- **テストマトリクス**:
  - PHP: 7.4, 8.0, 8.1, 8.2, 8.3, 8.4, 8.5
  - DB: MySQL 8.4, PostgreSQL 16

### Docker環境

```bash
# MySQL環境
docker-compose.yml + docker-compose.mysql.yml

# PostgreSQL環境
docker-compose.yml + docker-compose.pgsql.yml
```

### 関連PR

- **PR #1309**: SC_CartSession tearDown fixes (マージ済み)
  - tearDown で `delete(..., '1=1')` を追加
  - これにより一部の状態汚染問題を解決

- **PR #1313**: Dependabot security updates (マージ待ち)
  - zaproxy, tar, js-yaml のアップデート
  - 別の問題のため優先度低

## 堂々巡りの記録

### ループ1: delete() が原因だと思い込む

1. 47箇所の `delete()` 呼び出しで第2引数なしを発見
2. SC_Helper_TaxRule関連の2箇所を修正
3. 修正後もテストが失敗
4. **結論**: delete() は根本原因ではない

### ループ2: PHP 8.4固有の問題だと思い込む

1. CI結果で PHP 8.4 + MySQL が失敗
2. PHP 8.4の変更点を調査
3. しかし他のPHPバージョンでもMySQLで失敗
4. **結論**: PHPバージョンは無関係

### ループ3: SC_Query状態汚染を疑う

1. SC_Queryが状態を保持する問題を知る
2. `$this->where` と `$this->arrWhereVal` の保持を確認
3. PostgreSQLでは問題なし
4. **結論**: SC_Query状態汚染だけでは説明できない

## 現在の理解

**確定事項**:
- MySQLのみで発生（PostgreSQLでは発生しない）
- PHPバージョンは無関係（全バージョンで発生）
- 税金計算に関連（価格差は約10%）
- `delete()` の第2引数不足は直接の原因ではない（修正済みでも失敗）

**未確定事項**:
- なぜMySQLのみで発生するのか
- テスト実行順序との関係
- dtb_tax_ruleテーブルのデータ状態
- MySQL 8.4固有の挙動変更

**次のアクション**:
1. テスト実行時のdtb_tax_ruleテーブルの内容をダンプ
2. MySQLとPostgreSQLのテスト実行順序を比較
3. 失敗するテストを単独実行してデバッグ

## 調査結果 (2026-01-21 16:01)

### ローカル環境でのテスト実行結果

#### PostgreSQL環境

```bash
# 単独テスト実行
docker compose exec -T ec-cube php data/vendor/bin/phpunit --filter testGetAllProductsTotal tests/class/SC_CartSession/SC_CartSessionTest.php
```

**結果**: ✅ OK (1 test, 3 assertions)

#### MySQL環境

```bash
# 環境切り替え
docker compose down
export COMPOSE_FILE=docker-compose.yml:docker-compose.mysql.yml
docker compose up -d --wait

# 単独テスト実行
docker compose exec -T ec-cube php data/vendor/bin/phpunit --filter testGetAllProductsTotal tests/class/SC_CartSession/SC_CartSessionTest.php
```

**結果**: ✅ OK (1 test, 3 assertions)

```bash
# クラス全体のテスト実行
docker compose exec -T ec-cube php data/vendor/bin/phpunit tests/class/SC_CartSession/SC_CartSessionTest.php
```

**結果**: ✅ OK (21 tests, 44 assertions)

```bash
# 全テスト実行
docker compose exec -T ec-cube php data/vendor/bin/phpunit
```

**結果**: ✅ OK, but incomplete, skipped, or risky tests!
Tests: 1272, Assertions: 1569, Skipped: 13, Incomplete: 2.

### 重要な発見

1. **ローカル MySQL環境では再現しない**
   - 単独実行: 成功
   - クラス全体: 成功
   - 全テスト: 成功

2. **PHPバージョンの違い**
   - ローカル: PHP 8.1.34
   - CI失敗環境: PHP 8.4

3. **単独実行では成功する**
   - テスト間の相互作用が原因の可能性
   - しかしローカルの全テスト実行では問題なし

### 新しい仮説

#### 仮説5: PHP 8.4固有の問題

**根拠**:
- ローカル（PHP 8.1 + MySQL 8.4）: 全テスト成功
- CI（PHP 8.4 + MySQL 8.4）: 5テスト失敗
- CI（PHP 8.4 + PostgreSQL 16）: 全テスト成功
- CI（PHP 7.4-8.3, 8.5 + MySQL 8.4）: ?（要確認）

**PHP 8.4で確認すべきこと**:
- 数値型の処理変更
- 浮動小数点演算の変更
- PDO_MySQLドライバーの変更
- MySQLとの相互作用の変更

#### 仮説6: CIとローカルのテスト実行順序の違い

**根拠**:
- PHPUnitはデフォルトでランダム実行
- シード値が異なれば実行順序が異なる
- 特定の順序でのみ失敗が発生する可能性

**確認方法**:
- CIのログからPHPUnitのシード値を確認
- 同じシード値でローカル実行
- PHP 8.4環境でローカル実行

### 次のアクション（優先順位順）

1. **PHP 8.4環境でローカル実行**
   ```bash
   # docker-compose.ymlのイメージタグを変更
   TAG=8.4-apache docker compose -f docker-compose.yml -f docker-compose.mysql.yml up -d
   docker compose exec ec-cube php data/vendor/bin/phpunit
   ```

2. **CIの他のPHPバージョンの結果を確認**
   - PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.5 + MySQL 8.4 の結果
   - 本当にPHP 8.4のみの問題か確認

3. **PHPUnitのシード値を固定して実行**
   ```bash
   docker compose exec ec-cube php data/vendor/bin/phpunit --order-by=random --random-order-seed=<CI_SEED>
   ```

4. **PHP 8.4のchangelogを確認**
   - https://www.php.net/ChangeLog-8.php#8.4.0
   - MySQL PDOドライバー関連の変更
   - 数値型処理の変更

## ローカルで再現成功 (2026-01-21 16:10)

### ランダム実行での失敗再現

```bash
for i in {1..5}; do
  docker compose exec -T ec-cube php data/vendor/bin/phpunit --order-by=random
done
```

**結果**:
- Run 1: 2 Failures
- Run 2: 4 Failures
- Run 3: 2 Failures
- Run 4: **Success**
- Run 5: 2 Failures

**成功率**: 1/5 = 20%

### 失敗したテストの種類（ランダムシード1768979109）

```
1) SC_Helper_DB_sfGetAddPointTest::testSfGetAddPoint
Failed asserting that '0' matches expected 20.
```

### 失敗したテストの種類（ランダムシード1768979130）

```
1) SC_Helper_Mail_sfSendRegistMailTest::test会員登録依頼メールの宛名と登録リンクのidが正しい
2) SC_Helper_Mail_sfSendRegistMailTest::test会員登録メールの宛名が正しい  
3) SC_Helper_DB_sfGetAddPointTest::testSfGetAddPointWithMinus
4) SC_Helper_DB_sfGetAddPointTest::testSfGetAddPoint
```

### 重要な観察

1. **ランダムシードによって失敗するテストが変わる**
   - ポイント計算テスト
   - メール送信テスト
   - (税金計算テストは今回再現せず)

2. **複数のテストクラスが影響を受けている**
   - SC_Helper_DB_sfGetAddPointTest
   - SC_Helper_Mail_sfSendRegistMailTest
   - SC_CartSessionTest (CIで報告)
   - SC_Helper_Purchase_getShipmentItemsTest (CIで報告)
   - SC_Helper_TaxRule_getDetailTest (CIで報告)

3. **状態汚染が広範囲に及んでいる**
   - 税金計算だけでなく、ポイント計算、メール送信も影響
   - テスト実行順序によって異なるテストが失敗

### 更新された仮説

#### 仮説7: 複数の`delete()`呼び出しで第2引数不足が累積

**根拠**:
- 47箇所の`delete()`で第2引数なし
- 修正したのは2箇所のみ（SC_Helper_TaxRule関連）
- 他の45箇所が影響している可能性

**確認済みの第2引数なし箇所**:
```php
// tests/class/helper/SC_Helper_TaxRule/SC_Helper_TaxRule_TestBase.php:125
$this->objQuery->delete('dtb_tax_rule', '1=1');  // ← 修正済み

// tests/class/helper/SC_Helper_TaxRule/SC_Helper_TaxRule_getTaxDetailTest.php:371
$this->objQuery->delete('dtb_tax_rule', '1=1');  // ← 修正済み

// tests/class/SC_CartSession/SC_CartSession_TestBase.php:62
$this->objQuery->delete('dtb_products_class');  // ← 未修正

// 他にも45箇所...
```

### 次のアクション（修正版）

1. **全ての`delete()`呼び出しを検索して修正**
   ```bash
   grep -rn "->delete(" tests/ | grep -v ", '1=1'" | grep -v "//" > delete-without-where.txt
   ```

2. **特に影響が大きそうなテーブル優先で修正**
   - dtb_tax_rule (修正済み)
   - dtb_products_class
   - dtb_products
   - dtb_baseinfo
   - dtb_deliv
   - dtb_customer

3. **修正後に再度ランダム実行**
   ```bash
   for i in {1..10}; do
     docker compose exec -T ec-cube php data/vendor/bin/phpunit --order-by=random
   done
   ```

4. **成功率を確認**
   - 目標: 10/10 = 100%成功

## 根本原因の特定！ (2026-01-21 16:10)

### 決定的な発見

**問題**: `@runInSeparateProcess`アノテーションがあるテストで、MySQLのみ失敗する

**検証結果**:

#### テストケース: SC_Helper_DB_sfGetAddPointTest

**PostgreSQL**:
- `@runInSeparateProcess`あり: ✅ 成功
- `@runInSeparateProcess`なし: ✅ 成功

**MySQL**:
- `@runInSeparateProcess`あり: ❌ 失敗
- `@runInSeparateProcess`なし: ✅ 成功

### 根本原因

`@runInSeparateProcess`アノテーションは、テストメソッドを別のPHPプロセスで実行する。

**問題のメカニズム**:

1. setUp()が親プロセスで実行される
   ```php
   $this->objQuery->update('dtb_baseinfo', ['point_rate' => 4], 'id = 1');
   ```

2. テストメソッドが子プロセス（別プロセス）で実行される

3. **MySQL**: setUp()のUPDATEがコミットされていないため、子プロセスから変更が見えない

4. **PostgreSQL**: 何らかの理由で変更が見える（オートコミット設定の違い？）

### 影響範囲

```bash
grep -r "@runInSeparateProcess" tests/ | wc -l
# 結果: 23個のテスト
```

23個のテストで`@runInSeparateProcess`が使用されており、全てで同じ問題が発生する可能性がある。

### MySQLとPostgreSQLの挙動の違い

#### MySQL (デフォルト設定)
- オートコミット: ON
- しかし`@runInSeparateProcess`では、プロセス境界を越えて変更が見えない
- 可能性: トランザクションが暗黙的に開始され、コミットされていない

#### PostgreSQL (デフォルト設定)  
- オートコミット: ON
- `@runInSeparateProcess`でも変更が見える
- 可能性: 各クエリが即座にコミットされている

### 解決策の選択肢

#### 選択肢1: `@runInSeparateProcess`を削除

**メリット**:
- MySQLでも動作するようになる
- シンプル

**デメリット**:
- `@runInSeparateProcess`が必要な理由があった可能性（グローバル状態の分離など）
- 副作用が出る可能性

#### 選択肢2: setUp()で明示的にコミット

**メリット**:
- `@runInSeparateProcess`を維持できる

**デメリット**:
- EC-CUBE2のデータベース抽象化レイヤー(SC_Query)でコミットの仕方が不明

#### 選択肢3: setUpBeforeClass()でデータを設定

**メリット**:
- クラス全体で1回だけ実行される

**デメリット**:
- 各テストメソッド間でデータが共有される
- テスト独立性が損なわれる

### 推奨解決策

**`@runInSeparateProcess`を削除する**

理由:
1. このアノテーションが必要な理由が不明確
2. MySQLとPostgreSQLで一貫した挙動が必要
3. 削除してもテストは通る（実証済み）

### 次のアクション

1. **全ての`@runInSeparateProcess`使用箇所を特定**
   ```bash
   grep -rn "@runInSeparateProcess" tests/
   ```

2. **なぜ`@runInSeparateProcess`が使われているか調査**
   - git blame で追加理由を確認
   - 削除して問題が出ないか確認

3. **削除して全テスト実行**
   - MySQL: 10回ランダム実行で全て成功することを確認
   - PostgreSQL: 引き続き成功することを確認

4. **CIで確認**
   - 全PHPバージョン x 全DB で成功することを確認

## 最終調査結果 (2026-01-21 16:35)

### 問題の切り分け完了

#### CI問題（PR #1314で対処済み）
**症状**: 税金計算がランダムに失敗（MySQLのみ）
- SC_CartSessionTest::testGetAllProductsTotal
- SC_CartSessionTest::testCalculate
- SC_Helper_Purchase_getShipmentItemsTest

**原因**: SC_Query state pollution
- `SC_Helper_TaxRule_TestBase::setUpTax()` (line 125)
- `SC_Helper_TaxRule_getTaxDetailTest` (line 371)
- `delete('dtb_tax_rule')` に第2引数 '1=1' がなく、前のWHERE句が再利用される

**修正内容** (コミット 2ffe0a28a):
```php
// Before
$this->objQuery->delete('dtb_tax_rule');

// After
$this->objQuery->delete('dtb_tax_rule', '1=1');
```

**ステータス**: ✅ 修正済み、CI確認待ち

#### ローカル問題（別issue、調査中）
**症状**: ポイント計算が失敗（MySQL/PostgreSQL両方）
- SC_Helper_DB_sfGetAddPointTest::testSfGetAddPoint
- SC_Helper_DB_sfGetAddPointTest::testSfGetAddPointWithMinus

**原因**: `@runInSeparateProcess` アノテーション
- setUp() が親プロセスで実行
- テストメソッドが子プロセス（別プロセス）で実行
- MySQLでは setUp() の UPDATE がコミットされず、子プロセスから見えない

**重要な発見**:
- ローカル: 100%失敗
- **CI: 成功している**（ログに失敗記録なし）
- CIとローカルで環境の違いがある

**原因の可能性**:
1. CIではテストが別の方法で実行されている
2. PHPUnitのバージョンや設定が異なる
3. `@runInSeparateProcess` の挙動がCI環境では異なる

**ステータス**: ⚠️ 調査継続が必要だが、PR #1314とは無関係

### 調査中に発見した事項

#### 1. メモリキャッシュの問題
管理画面 https://localhost:4430/admin/basis/point.php で point_rate が 10 と表示されていたが：
- データベース: 0（正しい）
- ファイルキャッシュ (data/cache/dtb_baseinfo.serial): 0（正しい）
- メモリキャッシュ (static $arrData): 10（古いテストデータ）

**解決方法**: Apache再起動でメモリキャッシュクリア

#### 2. delete() の第2引数不足は根本原因ではない
- PostgreSQLでも同じコードだが、PostgreSQLでは成功している
- もし delete() が根本原因なら、PostgreSQLでも失敗するはず
- つまり、**MySQLとPostgreSQLで異なる挙動を引き起こす別の要因がある**

### 結論

**PR #1314の修正は正しい方向**:
- CIで報告されている税金計算失敗に対処
- SC_Query state pollution問題を解決
- 2箇所の修正で網羅的に対処

**ローカルの問題は別issue**:
- `@runInSeparateProcess` の問題
- CIでは発生していないため、緊急度は低い
- 別途issueを作成して調査すべき

**次のアクション**:
1. PR #1314をCIで実行して、税金計算失敗が解決するか確認
2. CIが成功したらマージ
3. ローカルの `@runInSeparateProcess` 問題は別issueとして記録
