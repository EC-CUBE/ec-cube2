<?php

// AUTH_TYPE = PLAIN 用のテストブートストラップ
// 標準の require.php より先に AUTH_TYPE を定義することで、PLAIN モードのテストを実行する
define('AUTH_TYPE', 'PLAIN');
require __DIR__.'/require.php';
