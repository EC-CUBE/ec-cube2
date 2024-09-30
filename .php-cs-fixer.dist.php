<?php

if (php_sapi_name() !== 'cli') {
    throw new \LogicException();
}

$rules = [
    '@Symfony' => true,
    '@Symfony:risky' => true,
    // '@PHP83Migration' => true,

    // @Symfony のうち、以下のルールを無効化
    'phpdoc_align' => false, // phpdoc の内容が削除されてしまう場合がある
    'phpdoc_summary' => false, // 日本語なので不要
    'phpdoc_annotation_without_dot' => false, // 日本語なので不要
    'no_superfluous_phpdoc_tags' => false, // 副作用があるため
    'increment_style' => false, // 強制しなくて良い
    'yoda_style' => false, // 強制しなくて良い
    'trailing_comma_in_multiline' => false, // `, ]` となるケースを防ぐため

    // @Symfony:risky のうち、以下のルールを無効化
    'psr_autoloading' => false, // PSR-4 に準拠していないため
    'is_null' => false, // 副作用があるため
    'native_constant_invocation' => false, // namespace を使用していないため不要
    'string_length_to_empty' => false, // 副作用があるため
    'ternary_to_elvis_operator' => false, // 副作用があるため
];

$finder = \PhpCsFixer\Finder::create()
    ->in(__DIR__.'/data/class')
    ->in(__DIR__.'/data/class_extends')
    ->in(__DIR__.'/data/module')
    ->in(__DIR__.'/data/smarty_extends')
    ->in(__DIR__.'/tests')
    ->name('*.php')
;
$config = new \PhpCsFixer\Config();
return $config
    ->setRules($rules)
    ->setFinder($finder)
    ;
