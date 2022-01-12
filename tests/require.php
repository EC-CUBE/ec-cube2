<?php
$loader = require __DIR__.'/../data/vendor/autoload.php';

class_exists('FPDI'); // XXX PHPStan が FPDI を見つけてくれないのでロードしておく
class_exists('Smarty'); // XXX PHPStan が Smarty を見つけてくれないのでロードしておく

if (!class_exists('PHPUnit_Framework_TestCase')) {
    class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
}
defined('HTTP_URL') or define('HTTP_URL', getenv('HTTP_URL') ? getenv('HTTP_URL') : 'http://example.com/');
defined('HTTPS_URL') or define('HTTPS_URL', HTTP_URL);
defined('ROOT_URLPATH') or define('ROOT_URLPATH', getenv('ROOT_URLPATH') ? getenv('ROOT_URLPATH') : '/');
defined('ADMIN_DIR') or define('ADMIN_DIR', getenv('ADMIN_DIR') ? getenv('ADMIN_DIR') : '');
defined('HTML_REALDIR') or define('HTML_REALDIR', __DIR__.'/../html/');
require_once __DIR__.'/../html/define.php';
defined('DATA_REALDIR') or define('DATA_REALDIR', HTML_REALDIR . HTML2DATA_DIR);
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
        /** @var SplFileInfo $fileinfo */
        $map[(string)str_replace('.'.$fileinfo->getExtension(), '', $fileinfo->getFilename())] = $fileinfo->getPathname();
    }
    return $map;
};
$loader->add('_generated', __DIR__.'/../ctests/_support');
$loader->addClassMap($classMap(__DIR__.'/../ctests'));
$loader->addClassMap($classMap(__DIR__.'/class'));
return $loader;
