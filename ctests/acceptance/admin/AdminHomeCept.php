<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('管理画面に正常にログインできるかを確認する');
$I->amOnPage('/admin');

$I->fillField('input[name=login_id]', 'admin');
$I->fillField('input[name=password]', 'password');
$I->click(['css' => '.btn-tool-format']);

$I->see('ログイン : 管理者 様');
