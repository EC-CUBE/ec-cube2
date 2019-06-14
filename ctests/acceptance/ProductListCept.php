<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('商品一覧が正常に見られているかを確認する。');
$I->amOnPage('/products/list.php');
$I->see('EC-CUBE発!世界中を旅して見つけた立方体グルメを立方隊長が直送！');
$I->seeElement('#site_description');

$I->expect('50件まで一覧表示する');
$I->selectOption(['css' => '#page_navi_top > div > div.change > select'], '50件');
$all_products = $I->grabMultiple(['css' => '#undercolumn > form > div > div.listrightbloc > h3 > a']);
if (count($all_products) <= 50) {
    $I->see(count($all_products).'件', ['css' => '#undercolumn > div > span.attention']);
} else {
    $I->comment('50件超過の商品が存在します');
    $I->dontSee(count($all_products).'件', ['css' => '#undercolumn > div > span.attention']);
}

$I->dontSeeElement('.error');

// 食品
$I->amOnPage('/products/list.php?category_id=3');
$I->expect('50件まで一覧表示する');
$I->selectOption(['css' => '#page_navi_top > div > div.change > select'], '50件');

$I->expect('カテゴリにアクセスすると商品が絞り込まれる');
$I->comment('see https://github.com/EC-CUBE/eccube-2_13/pull/273');
$products_in_category = $I->grabMultiple(['css' => '#undercolumn > form > div > div.listrightbloc > h3 > a']);

if (count($products_in_category) <= 50) {
    $I->see(count($products_in_category).'件', ['css' => '#undercolumn > div > span.attention']);
} else {
    $I->comment('50件超過の商品が存在します');
    $I->dontSee(count($products_in_category).'件', ['css' => '#undercolumn > div > span.attention']);
}

$I->dontSeeElement('.error');

// 異常系
$I->amOnPage('/products/list.php?category_id=a');
$I->dontSeeElement('.error');
