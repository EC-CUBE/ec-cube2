<?php

require __DIR__.'/common.php';

$arrHeader = getallheaders();

// X-Test-type にエラー種別の定数名を受け取り, エラー画面を表示する
$type = $arrHeader['X-Test-type'] ?? '';
if (!preg_match('/\A[A-Z_]+\z/', $type) || !defined($type)) {
    exit('invalid type');
}

define('FRONT_FUNCTION', true);

SC_Utils_Ex::sfDispSiteError(constant($type));
