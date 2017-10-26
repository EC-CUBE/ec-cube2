<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('商品一覧が正常に見られているかを確認する。');
$I->amOnPage('/products/list.php');
$I->see('EC-CUBE発!世界中を旅して見つけた立方体グルメを立方隊長が直送！');
$I->seeElement('#site_description');

$I->dontSeeElement('.error');

// 食品
$I->amOnPage('/products/list.php?category_id=3');

$I->dontSeeElement('.error');

// 異常系
$I->amOnPage('/products/list.php?category_id=a');
$I->dontSeeElement('.error');
