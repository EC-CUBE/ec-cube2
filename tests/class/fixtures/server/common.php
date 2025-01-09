<?php

putenv('HTTP_URL=http://127.0.0.1:8085/');

require __DIR__.'/../../../require.php';

if (PHP_VERSION_ID >= 80400) {
    // XXX PHP8.4.0+で MobileDetect 3.74.x が E_DEPRECATED を発生させるため
    error_reporting(E_ALL & ~E_DEPRECATED);
} else {
    error_reporting(E_ALL);
}
ini_set('display_errors', '1');

header_remove('X-Powered-By');
header('Content-Type: text/plain; charset=utf-8');

register_shutdown_function(function () {
    echo "\n";
    session_write_close();
    print_r(headers_list());
    echo "shutdown\n";
});
ob_start();
