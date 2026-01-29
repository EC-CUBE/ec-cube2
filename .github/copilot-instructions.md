# EC-CUBE 2 プルリクエストレビュー指針

## プロジェクトの前提
- PHP 7.4〜8.5 対応のレガシー EC サイト構築フレームワーク
- MDB2 による DB 抽象化層を使用（MySQL, PostgreSQL, SQLite3）
- Smarty テンプレートエンジン使用
- PSR-4 オートロード未対応（独自クラスローダー）
- `symfony/polyfill-php80` を使用しているため、以下の PHP 8.0 関数は PHP 7.4 でも使用可能:
  - `str_contains()`, `str_starts_with()`, `str_ends_with()`
  - `get_debug_type()`, `get_resource_id()`, `fdiv()`, `preg_last_error_msg()`

## レビュー優先度（上から順に重視）

### 1. セキュリティ（最優先）
- SQLインジェクション: SC_Query のプレースホルダ使用を確認。直接文字列結合がないか
- XSS: Smarty テンプレートでの `|h` フィルタ適用漏れ
- CSRF: トランザクショントークンの検証漏れ
- ファイル操作: パストラバーサル、アップロード検証

### 2. DB 互換性
- MySQL / PostgreSQL / SQLite3 の3環境で動作するか
- DB 固有の SQL 構文（ILIKE, EXTRACT, CURRENT_TIMESTAMP 等）が
  SC_DB_DBFactory 経由で抽象化されているか
- 日付・数値型の扱いが DB 間で一貫しているか

### 3. 後方互換性
- public メソッドのシグネチャ変更がないか
- 既存プラグインに影響する変更がないか
- _ex クラス（class_extends）のオーバーライドパターンに従っているか

### 4. コード品質
- PHP CS Fixer（PSR-12）準拠
- エラーハンドリング: SC_Helper_HandleError 経由の適切な処理
- メモリ効率: 大量データ処理時のバッファリング考慮

### 5. テスト
- Common_TestCase を継承し、トランザクション内でテスト実行しているか
- DB 依存のテストが特定の DB に依存していないか
- Fixture データが tearDown で適切にロールバックされるか

## 出力形式
- 重大な問題は `🔴` 、改善推奨は `🟡`、軽微な提案は `🟢` で分類
- 修正が必要な箇所はインラインコメントで具体的なコード例を提示
- 日本語で回答
