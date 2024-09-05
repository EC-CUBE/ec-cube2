<?php

if (php_sapi_name() !== 'cli') {
    throw new \LogicException();
}

$rules = [
    '@Symfony' => true,
    '@Symfony:risky' => true,
    // '@PHP83Migration' => true,
    'psr_autoloading' => false,
    'single_quote' => false,
    'is_null' => false,
    'concat_space' => ['spacing' => 'one'],
    'native_constant_invocation' => false,
    'array_syntax' => false,
    'phpdoc_align' => false,
    'phpdoc_summary' => false,
    'phpdoc_scalar' => false,
    'phpdoc_annotation_without_dot' => false,
    'no_superfluous_phpdoc_tags' => false,
    'increment_style' => false,
    'yoda_style' => false,
    'ereg_to_preg' => true,
];

$finder = \PhpCsFixer\Finder::create()
    ->in(__DIR__.'/data/class')
    ->in(__DIR__.'/data/class_extends')
    ->in(__DIR__.'/data/module')
    ->name('*.php')
;
$config = new \PhpCsFixer\Config();
return $config
    ->setRules($rules)
    ->setFinder($finder)
    ;
