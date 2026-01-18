# Claude Code 開発メモ

このファイルはAI開発アシスタント（Claude Code）による開発時の注意事項をまとめたものです。
人間の開発者も参考にしてください。

## E2E テストの実行

### ローカル開発環境
ローカル開発環境（`http://localhost:8080`）でe2e-testsを実行する場合：

```bash
npm run test:e2e:local
```

または環境変数を明示的に設定：

```bash
BASE_URL=http://localhost:8080 npm run test:e2e
```

### CI環境（GitHub Actions）
CI環境では`BASE_URL`環境変数を設定せず、playwright.config.tsのデフォルト設定（`https://localhost:4430`）を使用します：

```bash
npm run test:e2e
```

**注意事項:**
- ローカル環境では `http://localhost:8080` を使用
- CI環境（GitHub Actions）では `https://localhost:4430` を使用
- `BASE_URL`環境変数が設定されている場合、playwright.config.tsの設定よりも優先されます

## テストメソッド名の文字化け問題

### 問題
PHPUnitのテストメソッド名に日本語を使用すると、以下の環境で文字化けが発生します：
- GitHub Actions CI環境
- php-cs-fixer実行時

### 原因
- ファイルのエンコーディングがUTF-8でも、環境によって日本語メソッド名が正しく処理されない
- php-cs-fixerが日本語を含むメソッド名を検証する際に文字化けを検出してエラーになる

### 解決策
**テストメソッド名には日本語を使用しない**

```php
// ❌ 悪い例（文字化けする）
public function testRequestModeメールアドレスが空の場合エラーになる()
public function testValidateToken期限切れトークンは無効とみなされる()

// ✅ 良い例（英語のみ）
public function testRequestModeWithEmptyEmailReturnsError()
public function testValidateTokenWithExpiredTokenReturnsNull()

// または
public function testRequestMode_EmptyEmail_ReturnsError()
public function testValidateToken_ExpiredToken_ReturnsNull()
```

### 日本語の説明が必要な場合
テストメソッドのDocBlockコメントに日本語で説明を記載してください：

```php
/**
 * requestモードのバリデーションテスト: メールアドレス必須
 * メールアドレスが空の場合、バリデーションエラーが発生することを確認
 */
public function testRequestMode_EmptyEmail_ReturnsError()
{
    // テスト実装
}
```

## その他の開発ガイドライン

### 文字エンコーディング
- すべてのファイルはUTF-8 (BOMなし) で保存
- 特にPHPファイルとテンプレートファイルは厳密に管理

### ローカル設定ファイル
以下のファイルはコミットしないこと（`.gitignore`に追加済み）：
- `tests/bootstrap_local.php`
- `tests/config_mysql_local.php`
- `.claude/settings.local.json`

### セキュリティ
- 入力値は必ずバリデーション
- XSS対策: Smartyの`|h`フィルタを使用
- SQLインジェクション対策: プリペアドステートメント使用
- CSRF対策: トランザクションIDを必ず検証

### テスト
- 新機能追加時は必ずテストを作成
- テストは`tests/`ディレクトリ配下に配置
- MailCatcherを使用したメール送信テストが可能

## Issue #368 パスワード再発行機能改善の記録

### 実装内容
トークンベースのパスワードリセット機能を実装しました。

主な変更点：
- 秘密の質問認証を廃止
- メール送信によるワンタイムトークン方式に変更
- レート制限の実装（メール/IP各1時間3回まで）
- 暗号学的に安全なトークン生成（`random_bytes(32)`）
- トークンのハッシュ化保存（SHA-256）

### 学んだ教訓
1. **文字化け問題**: テストメソッド名に日本語を使わないこと
2. **php-cs-fixer**: コミット前に必ず実行して確認
3. **セキュリティレビュー**: security-engineerエージェントによる包括的レビューが有効
4. **CI環境**: ローカルで動作してもCI環境で失敗することがある
5. **テーブル作成**: テストファイルでテーブルを作成せず、`eccube_install.sh` で作成する
6. **PostgreSQLシーケンス**: 新規テーブルのシーケンスは `eccube_install.sh` の `create_sequence_tables()` 関数に追加する
7. **テスト戦略**: EC-CUBEではページクラスの統合テストは作らない。ヘルパークラスやユーティリティクラスの単体テストのみ実装する
8. **ページクラステスト**: `action()` や `process()` を直接呼ぶテストは他に存在しない。この方式はEC-CUBEのテスト戦略に合わない

### データベーステーブル追加時のチェックリスト
新しいテーブルを追加する場合：
1. `html/install/sql/create_table_mysqli.sql` にテーブル定義を追加
2. `html/install/sql/create_table_pgsql.sql` にテーブル定義を追加
3. `eccube_install.sh` の `create_sequence_tables()` にシーケンス名を追加（PostgreSQL用）
4. テストファイルでは **テーブルを作成しない**（eccube_install.shで作成されたものを使用）
5. ヘルパークラスの単体テストを作成（ページクラスの統合テストは作らない）

### 参考PR
- PR #1299: https://github.com/EC-CUBE/ec-cube2/pull/1299

## Issue #1301 ログインエラー表示改善 + AJAX統一対応の記録

### 実装内容
ログインエラー表示の改善とAJAX統一対応を実装しました。

主な変更点：
- ログインエラーを同一ページに表示（別画面遷移を廃止）
- 全デバイス（PC/スマートフォン/モバイル）でAJAXログインに統一
- サーバー側のデバイス判定を削除
- HTTP/HTTPS両対応のURL指定
- レート制限機能の実装（メール5回、IP10回/1時間）

### 学んだ教訓

#### 1. AJAX統一対応のパターン
デバイス判定を削除してコードをシンプルにする：

```php
// ❌ 従来のパターン（デバイス判定あり）
if (SC_Display_Ex::detectDevice() === DEVICE_TYPE_SMARTPHONE) {
    echo SC_Utils_Ex::jsonEncode(['error' => $this->arrErr['login']]);
    SC_Response_Ex::actionExit();
} else {
    $_SESSION['login_error'] = $this->arrErr['login'];
    SC_Response_Ex::sendRedirect($url);
    SC_Response_Ex::actionExit();
}

// ✅ 改善後（AJAX統一）
echo SC_Utils_Ex::jsonEncode(['error' => $this->arrErr['login']]);
SC_Response_Ex::actionExit();
```

#### 2. HTTP/HTTPS両対応のURL指定
テンプレートでは相対パスを使用する：

```smarty
// ❌ 絶対URL（HTTPとHTTPSで問題が発生）
url: "<!--{$smarty.const.HTTPS_URL}-->frontparts/login_check.php"

// ✅ 相対パス（HTTP/HTTPS両対応）
url: "<!--{$smarty.const.ROOT_URLPATH}-->frontparts/login_check.php"
```

**理由:**
- `HTTPS_URL`を使うと、HTTPからHTTPSへのクロスプロトコルリクエストになる
- ローカル開発環境（http://localhost:8080）とCI環境（https://localhost:4430）の両方で動作させるため
- `ROOT_URLPATH`は現在のプロトコルを継承する

#### 3. エラー表示のUI設計
スペースに応じてエラー表示方法を使い分ける：

- **ヘッダー・サイドバーログインブロック**: alert表示（省スペース化）
- **マイページ・ショッピングカートログイン**: ページ内表示（詳細表示可能）

#### 4. E2Eテストでのalertダイアログの扱い方

```typescript
// alertダイアログを処理するパターン
let alertMessage = '';
page.once('dialog', async dialog => {
    alertMessage = dialog.message();
    await dialog.accept();
});

// ボタンをクリック
await page.locator('#button').click();

// 待機後に検証
await page.waitForTimeout(2000);
expect(alertMessage).toMatch(/期待するメッセージ/);
```

**注意点:**
- `page.once('dialog')` はクリック前に設定する
- `page.on('dialog')` ではなく `page.once('dialog')` を使う（1回だけ処理）
- alertが表示されるまで適切に待機する

#### 5. E2Eテストのセレクタ設計
一般的なクラスセレクタではなく、具体的なIDセレクタを使用する：

```typescript
// ❌ 一般的すぎる（複数マッチする可能性）
await page.locator('div.attention').first()

// ✅ 具体的なID（確実に目的の要素を取得）
await page.locator('#undercolumn_login #login_error_area')
await page.locator('#header_login_area div.attention')
```

**理由:**
- ページ内に複数の`.attention`が存在する場合、意図しない要素を取得する
- テストの信頼性向上

#### 6. レート制限のタイミング
レート制限チェックは**ログイン試行前**に実行される：

```php
// 6回まで失敗を許可する場合
if ($email_count >= 6) {  // 6回以上でブロック
    return ['allowed' => false, ...];
}
```

**動作:**
- 5回失敗後、6回目の試行: カウントは5 → 許可される
- 6回失敗後、7回目の試行: カウントは6 → ブロックされる

**E2Eテスト:**
- 6回連続で失敗させる
- 7回目でレート制限エラーを期待する

#### 7. PostgreSQLとMySQLの両対応
開発環境とCI環境でDBが異なる場合の注意点：

- **ローカル（http://localhost:8080）**: PostgreSQL
- **CI環境（https://localhost:4430）**: MySQL（場合による）

**対応方法:**
```bash
# PostgreSQLのデータクリア
docker exec ec-cube2-test-postgres psql -U postgres -d eccube_db -c "TRUNCATE TABLE dtb_login_attempt;"

# MySQLのデータクリア
docker exec ec-cube-mysql-1 mysql -uroot -proot eccubedb -e "TRUNCATE TABLE dtb_login_attempt;"
```

#### 8. Dockerコンテナの再起動
テンプレートやPHPファイルを変更した場合、OPcacheをクリアするため再起動が必要：

```bash
docker compose restart ec-cube
```

**症状:**
- テンプレートを変更してもブラウザで変更が反映されない
- PHPファイルを変更しても古いコードが実行される

#### 9. ブラウザ拡張機能の影響
ブラウザ拡張機能がJavaScriptの実行を妨害する可能性がある：

**症状:**
- コンソールに `content.js:2 Uncaught TypeError: t.substring is not a function` エラー
- フォーム送信に異常な遅延（`'submit' handler took 793ms`）

**対処:**
- E2Eテスト: Playwright はクリーンなブラウザコンテキストを使用（影響なし）
- 手動テスト: シークレットモードで確認

#### 10. JSONパースエラーのデバッグ
AJAXリクエストで `parsererror` が発生した場合：

```javascript
error: function(XMLHttpRequest, textStatus, errorThrown) {
    console.error('AJAX Error Details:');
    console.error('  Status:', XMLHttpRequest.status);
    console.error('  Response Text:', XMLHttpRequest.responseText);
    console.error('  Text Status:', textStatus);
}
```

**よくある原因:**
- サーバーがJSONではなくHTMLを返している
- レート制限などでリダイレクトが発生している
- デバイス判定の残骸でJSON応答されていない

### データベーステーブル作成の注意点
PostgreSQL環境でテーブルを作成する場合：

```bash
# MySQLのCREATE文をそのまま実行できない場合がある
# PostgreSQL用のCREATE文を使用する

docker exec ec-cube2-test-postgres psql -U postgres -d eccube_db -c "
CREATE TABLE IF NOT EXISTS dtb_login_attempt (
    attempt_id SERIAL PRIMARY KEY,
    login_id TEXT NOT NULL,
    ip_address TEXT,
    user_agent TEXT,
    result SMALLINT NOT NULL,
    create_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_login_id_create_date ON dtb_login_attempt(login_id, create_date);
CREATE INDEX idx_ip_create_date ON dtb_login_attempt(ip_address, create_date);
"
```

### 参考PR
- PR #1302: https://github.com/EC-CUBE/ec-cube2/pull/1302

## CI/CDパイプラインのパフォーマンス改善と安定性向上

### 実装内容
EC-CUBE 4系のPR #6576を参考に、EC-CUBE 2系のCI/CDパイプラインを高速化・安定化しました。

主な変更点：
- GitHub Actions concurrency設定による古いCI実行の自動キャンセル
- ワークフローの並列実行化（phpstan, unit-tests, e2e-testsを並列化）
- Composer/yarn/Playwrightのキャッシュ戦略の導入
- Flaky Test対策として自動リトライ機能を追加

### 学んだ教訓

#### 1. GitHub Actions concurrency設定
同じブランチの古いCI実行を自動キャンセルする：

```yaml
concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true
```

**効果:**
- 新しいpush時に古い実行中のCIが自動キャンセルされる
- ランナーリソースの節約
- 不要な重複実行を防止

#### 2. ワークフローの並列実行化
依存関係を見直して並列実行を最適化：

```yaml
# 従来（直列実行）
jobs:
  dockerbuild:
  php-cs-fixer:
    needs: [ dockerbuild ]
  phpstan:
    needs: [ php-cs-fixer ]  # php-cs-fixerに依存
  unit-tests:
    needs: [ php-cs-fixer ]
  e2e-tests:
    needs: [ php-cs-fixer ]

# 改善後（並列実行）
jobs:
  dockerbuild:
  php-cs-fixer:
    needs: [ dockerbuild ]
  phpstan:
    needs: [ dockerbuild ]  # dockerbuildにのみ依存
  unit-tests:
    needs: [ dockerbuild ]
  e2e-tests:
    needs: [ dockerbuild ]
```

**ポイント:**
- phpstanはphp-cs-fixerに依存する必要がない
- 静的解析とテストは独立して実行可能
- dockerbuildの後で全て並列実行

#### 3. GitHub Actions キャッシュ戦略
actions/cache@v4を使用して依存関係をキャッシュ：

**Composerキャッシュ:**
```yaml
- name: Cache Composer packages
  uses: actions/cache@v4
  with:
    path: ~/.cache/composer
    key: ${{ runner.os }}-php${{ matrix.php }}-composer-${{ hashFiles('**/composer.lock') }}
    restore-keys: |
      ${{ runner.os }}-php${{ matrix.php }}-composer-
```

**yarnキャッシュ:**
```yaml
- name: Cache yarn packages
  uses: actions/cache@v4
  with:
    path: ~/.cache/yarn
    key: ${{ runner.os }}-yarn-${{ hashFiles('**/yarn.lock') }}
    restore-keys: |
      ${{ runner.os }}-yarn-
```

**Playwrightブラウザキャッシュ:**
```yaml
- name: Cache Playwright browsers
  uses: actions/cache@v4
  with:
    path: ~/.cache/ms-playwright
    key: ${{ runner.os }}-playwright-${{ hashFiles('**/yarn.lock') }}
    restore-keys: |
      ${{ runner.os }}-playwright-
```

**ポイント:**
- `key`: 完全一致でキャッシュをヒット（composer.lock/yarn.lockのハッシュを使用）
- `restore-keys`: 部分一致でキャッシュをヒット（フォールバック）
- PHPバージョンが異なる場合は異なるキャッシュを使用（`php${{ matrix.php }}`）

#### 4. Flaky Test対策 - 自動リトライ
nick-invision/retry@v3を使用してE2Eテストに自動リトライを追加：

```yaml
- name: Run to E2E testing
  uses: nick-invision/retry@v3
  with:
    timeout_minutes: 30
    max_attempts: 2
    retry_on: error
    command: yarn ${{ matrix.pattern }} e2e-tests/${{ matrix.group }}
  env:
    CI: 1
    FORCE_COLOR: 1
```

**ポイント:**
- `max_attempts: 2`: 最大2回試行（1回失敗しても1回リトライ）
- `timeout_minutes: 30`: 各試行のタイムアウト
- `retry_on: error`: エラー時にリトライ
- E2Eテストとペネトレーションテストに適用

**注意点:**
- 従来の `run:` ステップを `uses: nick-invision/retry@v3` に変更
- `run:` の内容を `command:` パラメータに移動
- `env:` は同じ階層で指定

#### 5. キャッシュキーの設計
キャッシュキーには以下を含める：

1. **OS**: `${{ runner.os }}` - Linux/macOS/Windowsで異なるキャッシュ
2. **言語バージョン**: `php${{ matrix.php }}` - PHPバージョンごとに異なるキャッシュ
3. **依存関係ファイルのハッシュ**: `${{ hashFiles('**/composer.lock') }}` - 依存関係が変わったら新しいキャッシュ

**restore-keysの使い方:**
```yaml
key: ${{ runner.os }}-php8.3-composer-abc123
restore-keys: |
  ${{ runner.os }}-php8.3-composer-
  ${{ runner.os }}-php8.3-
```

- 完全一致がなければ、プレフィックス一致でキャッシュを復元
- 古いキャッシュでも部分的に使える（再インストールより速い）

#### 6. EC-CUBE 4系のPRを参考にする
EC-CUBE 2系とEC-CUBE 4系は異なるプロジェクトだが、CI/CDの改善パターンは共通：

**参考にできる点:**
- concurrency設定
- キャッシュ戦略（Composer, yarn, Playwright）
- 自動リトライ
- 並列実行の最適化

**異なる点:**
- EC-CUBE 4: Rector（PHPリファクタリングツール）あり
- EC-CUBE 2: 単純な構成（php-cs-fixer, phpstan, unit-tests, e2e-tests）

#### 7. ワークフローファイルの構造
EC-CUBE 2のCIは以下のワークフローで構成：

1. **main.yml**: メインワークフロー（全体の制御）
2. **php-cs-fixer.yml**: コードスタイルチェック
3. **phpstan.yml**: 静的解析
4. **unit-tests.yml**: ユニットテスト
5. **e2e-tests.yml**: E2Eテスト（2つのjob: run-on-linux, installer）
6. **penetration-tests.yml**: ペネトレーションテスト（schedule実行）

**main.ymlの役割:**
- 他のワークフローを呼び出す（`uses: ./.github/workflows/xxx.yml`）
- 実行順序を制御（`needs:` で依存関係を定義）
- concurrency設定で古い実行をキャンセル

#### 8. matrix戦略でのキャッシュ
matrixで複数バージョンをテストする場合、キャッシュキーにmatrix変数を含める：

```yaml
strategy:
  matrix:
    php: [ '7.4', '8.0', '8.1', '8.2', '8.3', '8.4', '8.5' ]
    db: [ mysql, pgsql ]

steps:
  - name: Cache Composer packages
    uses: actions/cache@v4
    with:
      path: ~/.cache/composer
      key: ${{ runner.os }}-php${{ matrix.php }}-composer-${{ hashFiles('**/composer.lock') }}
```

**効果:**
- PHP 8.3とPHP 8.4で異なるキャッシュを使用
- DB (mysql/pgsql) はキャッシュキーに含めない（Composerには影響しない）

#### 9. キャッシュのパス指定
各ツールのキャッシュパス：

- **Composer**: `~/.cache/composer` (Linux), `~/Library/Caches/composer` (macOS)
- **yarn**: `~/.cache/yarn` (Linux), `~/Library/Caches/yarn` (macOS)
- **Playwright**: `~/.cache/ms-playwright` (Linux), `~/Library/Caches/ms-playwright` (macOS)

**注意:**
- Dockerコンテナ内で実行する場合、キャッシュパスが異なる可能性がある
- EC-CUBE 2のCIはubuntu-24.04ランナー上でDockerを実行しているが、キャッシュはランナー側に保存

#### 10. 自動リトライの適用箇所
自動リトライは以下の箇所に適用：

**適用すべき:**
- E2Eテスト（Flaky Testが多い）
- ペネトレーションテスト（ネットワーク依存）
- 外部APIを使うテスト

**適用すべきでない:**
- ユニットテスト（Flaky Testが少ない、問題があればコードを修正すべき）
- 静的解析（決定的、リトライしても結果は同じ）
- ビルド（失敗したらコードを修正すべき）

### 期待される効果

1. **並列化によるCI時間短縮**
   - phpstan, unit-tests, e2e-testsが並列実行
   - 従来: 合計60分（順次） → 改善後: 最長ジョブ時間（並列）

2. **キャッシュによる高速化**
   - 2回目以降のCI実行で依存関係インストール時間を削減
   - Composer: 2-3分 → 30秒
   - yarn + Playwright: 3-5分 → 1分

3. **安定性向上**
   - Flaky Testによる失敗を自動リトライで吸収
   - CI失敗率を低減

4. **リソース効率化**
   - 古いCI実行の自動キャンセルでランナー使用時間を削減
   - 重複実行を防止

### 参考PR
- EC-CUBE 4系の同様の改善: https://github.com/EC-CUBE/ec-cube/pull/6576
- EC-CUBE 2系の実装: (このPR)
