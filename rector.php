<?php

declare(strict_types=1);
require __DIR__ . '/data/vendor/autoload.php';
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;
use Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
           // EC-CUBEのPHPバージョンに合わせて設定
           ->withPhpVersion(PhpVersion::PHP_74)
           // Rectorが解析するパスを指定
           ->withPaths([
               __DIR__ . '/html',
               __DIR__ . '/data',
               // __DIR__ . '/tests',
           ])
           // スキップするパスやルールを指定
           ->withSkip([
               __DIR__ . '/data/vendor',
               __DIR__ . '/data/downloads',
               __DIR__ . '/data/Smarty',
               __DIR__ . '/html/install/temp',
           ])
           ->withRules([
               CompleteDynamicPropertiesRector::class,
               ThisCallOnStaticMethodToStaticCallRector::class,
               StaticCallOnNonStaticToInstanceCallRector::class,
           ])
           // uncomment to reach your current PHP version
           ->withSets([
               // SetList::DEAD_CODE,
               // LevelSetList::UP_TO_PHP_84, // PHPバージョンに合わせる
               PHPUnitSetList::PHPUNIT_CODE_QUALITY,
               PHPUnitSetList::PHPUNIT_90, // PHPUnitのバージョンに合わせる
           ]);
