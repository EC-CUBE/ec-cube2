<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('商品詳細が正常に見られているかを確認する。');
$I->amOnPage('/products/detail.php?product_id=3');
$I->see('EC-CUBE発!世界中を旅して見つけた立方体グルメを立方隊長が直送！');
$I->seeElement('#site_description');

$I->dontSeeElement('.error');

// 異常系
$I->amOnPage('/products/detail.php?product_id=a');
$I->SeeElement('.error');
$I->dontSee('システムエラー');
