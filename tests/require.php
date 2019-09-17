<?php
$loader = require __DIR__.'/../data/vendor/autoload.php';

if (!class_exists('PHPUnit_Framework_TestCase')) {
    class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
}

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
