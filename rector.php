<?php

declare(strict_types=1);
require __DIR__ . '/data/vendor/autoload.php';

use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\ValueObject\PhpVersion;

// PHP 7.0 互換ルール
use Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;
use Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;

// PHP 8.1 前方互換: null を文字列関数に渡すと非推奨 (生成コードは PHP 7.4 互換)
use Rector\Php81\Rector\FuncCall\NullToStrictIntPregSlitFuncCallLimitArgRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;

// PHP 8.2 前方互換: "${var}" 形式の文字列補間が非推奨 → "{$var}" に変換 (PHP 7.4 互換)
use Rector\Php82\Rector\Encapsed\VariableInStringInterpolationFixerRector;
// PHP 8.2 前方互換: FilesystemIterator の SKIP_DOTS がデフォルトに
use Rector\Php82\Rector\New_\FilesystemIteratorSkipDotsRector;

// PHP 8.3 前方互換: get_class()/get_parent_class() の引数なし呼び出しが非推奨
use Rector\Php83\Rector\FuncCall\RemoveGetClassGetParentClassNoArgsRector;

// PHP 8.4 前方互換: 暗黙の nullable パラメータ宣言が非推奨 → 明示的に ?Type に (PHP 7.1+ 構文)
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
// PHP 8.4 前方互換: fputcsv() 等の escape 引数のデフォルトが変更
use Rector\Php84\Rector\FuncCall\AddEscapeArgumentRector;

// PHP 8.5 前方互換: array_key_exists() に null キーを渡すと非推奨 → '' に変換
use Rector\Php85\Rector\FuncCall\ArrayKeyExistsNullToEmptyStringRector;
// PHP 8.5 前方互換: finfo の context 引数が非推奨
use Rector\Php85\Rector\FuncCall\RemoveFinfoBufferContextArgRector;
// PHP 8.5 前方互換: switch case のセミコロン → コロン
use Rector\Php85\Rector\Switch_\ColonAfterSwitchCaseRector;
// PHP 8.5 前方互換: chr() に 256 以上の値を渡すと非推奨
use Rector\Php85\Rector\FuncCall\ChrArgModuloRector;

return RectorConfig::configure()
           // EC-CUBE 2 の最小 PHP バージョン
           ->withPhpVersion(PhpVersion::PHP_74)
           ->withPaths([
               __DIR__ . '/html',
               __DIR__ . '/data',
               // __DIR__ . '/tests',
           ])
           ->withSkip([
               __DIR__ . '/data/vendor',
               __DIR__ . '/data/downloads',
               __DIR__ . '/data/Smarty',
               __DIR__ . '/html/install/temp',
           ])
           // ============================================================
           // ホワイトリスト方式: 適用するルールを明示的に指定
           // PHP 7.4 互換のコードを生成するルールのみを列挙する。
           // Rector のバージョンアップで新ルールが追加されても、
           // ここに追記しない限り適用されない。
           // ============================================================
           ->withRules([
               // --- 既存ルール ---
               // 動的プロパティの明示的宣言 (PHP 8.2 で非推奨対策)
               CompleteDynamicPropertiesRector::class,
               // $this-> による静的メソッド呼び出しを static:: に変換
               ThisCallOnStaticMethodToStaticCallRector::class,
               // 非静的メソッドへの静的呼び出しをインスタンス経由に変換
               StaticCallOnNonStaticToInstanceCallRector::class,

               // --- PHP 8.1 前方互換 ---
               // null を文字列関数に渡すと非推奨 → (string) キャストを追加
               NullToStrictStringFuncCallArgRector::class,
               // preg_split() の limit 引数に null を渡すと非推奨 → -1 に変換
               NullToStrictIntPregSlitFuncCallLimitArgRector::class,

               // --- PHP 8.2 前方互換 ---
               // "${var}" → "{$var}" (非推奨の文字列補間形式を修正)
               VariableInStringInterpolationFixerRector::class,
               // FilesystemIterator の SKIP_DOTS フラグを明示
               FilesystemIteratorSkipDotsRector::class,

               // --- PHP 8.3 前方互換 ---
               // get_class() → get_class($this) (引数なし呼び出しが非推奨)
               RemoveGetClassGetParentClassNoArgsRector::class,

               // --- PHP 8.4 前方互換 ---
               // function foo(Type $x = null) → function foo(?Type $x = null)
               ExplicitNullableParamTypeRector::class,
               // fputcsv() 等に escape 引数を明示的に追加
               AddEscapeArgumentRector::class,

               // --- PHP 8.5 前方互換 ---
               // array_key_exists(null, $arr) → array_key_exists('', $arr)
               ArrayKeyExistsNullToEmptyStringRector::class,
               // finfo_buffer/finfo_file の context 引数を削除
               RemoveFinfoBufferContextArgRector::class,
               // switch case のセミコロン → コロンに統一
               ColonAfterSwitchCaseRector::class,
               // chr($n) → chr($n % 256) (256以上の値が非推奨)
               ChrArgModuloRector::class,
           ])
           ->withSets([
               PHPUnitSetList::PHPUNIT_CODE_QUALITY,
               PHPUnitSetList::PHPUNIT_90,
           ]);
