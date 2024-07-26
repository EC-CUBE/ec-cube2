<?php

require __DIR__.'/common.php';

/**
 * この値は使われない。
 * @see https://github.com/EC-CUBE/ec-cube2/issues/922
 */
$_SESSION[TRANSACTION_ID_NAME] = 'on_session';

$url = '/redirect_url.php';
$arrQueryString = [];

$arrHeader = getallheaders();

if (($arrHeader['X-Test-function'] ?? '') === 'admin') {
    define('ADMIN_FUNCTION', true);
}
else {
    define('FRONT_FUNCTION', true);
}

if (strlen($arrHeader['X-Test-dst_mode'] ?? '') >= 1) {
    $url .= '?mode=' . $arrHeader['X-Test-dst_mode'];
}

if (strlen($arrHeader['X-Test-logic_transaction_id'] ?? '') >= 1) {
    $arrQueryString[TRANSACTION_ID_NAME] = $arrHeader['X-Test-logic_transaction_id'];
}

$inherit_query_string = ($arrHeader['X-Test-inherit_query_string'] ?? '') === '1';

SC_Response_Ex::sendRedirect($url, $arrQueryString, $inherit_query_string);
