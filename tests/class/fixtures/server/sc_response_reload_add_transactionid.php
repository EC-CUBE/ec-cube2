<?php

require __DIR__.'/common.php';

/**
 * この値は使われない。
 * @see https://github.com/EC-CUBE/ec-cube2/issues/922
 */
$_SESSION[TRANSACTION_ID_NAME] = 'on_session';

SC_Response_Ex::reload(['redirect' => 1, TRANSACTION_ID_NAME => 'on_logic']);
