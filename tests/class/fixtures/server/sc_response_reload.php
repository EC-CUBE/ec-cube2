<?php

require __DIR__.'/common.php';

$_SERVER['REQUEST_URI'] = HTTPS_URL.'index.php?debug='.urlencode('テスト');
$_SESSION[TRANSACTION_ID_NAME] = 'aaaa';

SC_Response_Ex::reload(['redirect' => 1]);
