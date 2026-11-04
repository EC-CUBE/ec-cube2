<?php

// AUTH_TYPE は定数のため, 一度定義されるとテスト内で切り替えられない.
// PLAIN 前提のテスト (@group auth_type_plain) は @runTestsInSeparateProcesses で子プロセスを起動し,
// 環境変数 ECCUBE_TEST_AUTH_TYPE 経由でこの bootstrap の時点で AUTH_TYPE を定義する.
// see tests/class/util/SC_Utils/SC_Utils_AuthTypePlain_TestBase.php
if (!defined('AUTH_TYPE') && getenv('ECCUBE_TEST_AUTH_TYPE') !== false) {
    define('AUTH_TYPE', getenv('ECCUBE_TEST_AUTH_TYPE'));
}

$loader = require __DIR__.'/../data/vendor/autoload.php';

/* テスト中 */
define('TEST_FUNCTION', true);

if (str_contains($_SERVER['SCRIPT_FILENAME'], 'phpunit') && !class_exists('\Eccube2\Tests\Fixture\Generator')) {
    echo 'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'composer require nanasess/eccube2-fixture-generator --dev --ignore-platform-req=php'.PHP_EOL;
    exit(1);
}

// XXX PHPStan が見つけてくれないライブラリをロードしておく
class_exists('FPDI');
class_exists('Smarty');
class_exists('MDB2');

if (!class_exists('PHPUnit_Framework_TestCase')) {
    class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
}
defined('HTTP_URL') || define('HTTP_URL', getenv('HTTP_URL') ? getenv('HTTP_URL') : 'http://example.com/');
defined('HTTPS_URL') || define('HTTPS_URL', HTTP_URL);
defined('ROOT_URLPATH') || define('ROOT_URLPATH', getenv('ROOT_URLPATH') ? getenv('ROOT_URLPATH') : '/');
defined('ADMIN_DIR') || define('ADMIN_DIR', getenv('ADMIN_DIR') ? getenv('ADMIN_DIR') : '');
defined('TEST_MAILCATCHER_URL') || define('TEST_MAILCATCHER_URL', getenv('TEST_MAILCATCHER_URL') ? getenv('TEST_MAILCATCHER_URL') : 'http://localhost:1080');
defined('HTML_REALDIR') || define('HTML_REALDIR', __DIR__.'/../html/');
require_once __DIR__.'/../html/define.php';
defined('DATA_REALDIR') || define('DATA_REALDIR', HTML_REALDIR.HTML2DATA_DIR);
require_once __DIR__.'/../data/app_initial.php';

$classMap = function ($dir) {
    $map = [];
    $iterator = new RegexIterator(
        new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        ),
        '/^(?!.+_ex\.php).+\.php$/i',
        RecursiveRegexIterator::MATCH
    );
    foreach ($iterator as $fileinfo) {
        /* @var SplFileInfo $fileinfo */
        $map[(string) str_replace('.'.$fileinfo->getExtension(), '', $fileinfo->getFilename())] = $fileinfo->getPathname();
    }

    return $map;
};
$loader->addClassMap($classMap(__DIR__.'/class'));

return $loader;
