<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('TOPページが正常に見られているかを確認する。');
$I->amOnPage('/');
$I->see('EC-CUBE発!世界中を旅して見つけた立方体グルメを立方隊長が直送！');
$I->seeElement('#site_description');

$I->seeElement('#main_image');

$I->expect('body の class 名が出力されている');
$I->seeElement(['css' => 'body'], ['class' => 'LC_Page_Index']);

$I->expect('システムエラーが出ていない');
$I->dontSeeElement('.error');
