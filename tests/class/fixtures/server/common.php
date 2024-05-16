<?php

putenv('HTTP_URL=http://127.0.0.1:8053/');

require __DIR__.'/../../../require.php';

error_reporting(-1);
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
