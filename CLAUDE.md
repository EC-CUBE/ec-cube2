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
