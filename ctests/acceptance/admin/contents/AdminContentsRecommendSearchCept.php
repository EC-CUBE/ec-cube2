<?php
/**
 * @group installer
 */
$I = new AcceptanceTester($scenario);
$I->wantTo('おすすめ商品管理を確認する');
$I->amOnPage('/'.ADMIN_DIR.'/'.DIR_INDEX_FILE);

$I->fillField('input[name=login_id]', 'admin');
$I->fillField('input[name=password]', 'password');
$I->click(['css' => '.btn-tool-format']);

$I->see('ログイン : 管理者 様');

$I->amGoingTo('コンテンツ管理＞おすすめ商品管理');
$I->amOnPage('/'.ADMIN_DIR.'/contents/recommend.php');

$I->expect('おすすめ商品の編集を確認する');
$I->click(['css' => '#admin-contents > div:nth-child(2) > table > tbody > tr:nth-child(2) > td:nth-child(3) > a']); // 編集ボタン

$I->expect('カテゴリ検索を確認する');
$I->switchToNewWindow();
$I->see('カテゴリ');
$I->selectOption(['css' => 'select[name=search_category_id]'], '>>>アイス');
$I->click('検索を開始');
$I->waitForText('アイスクリーム');

$I->click(['css' => '#recommend-search-results > tbody > tr:nth-child(2) > td:nth-child(4) > a']);
$I->switchToNewWindow();

$I->expect('おすすめ商品(1)が変更されている');
$I->see('アイスクリーム', '#admin-contents > div:nth-child(2) > table > tbody > tr:nth-child(2) > td:nth-child(2) > div > div.table-detail > div.detail-name');


$I->click(['css' => '#admin-contents > div:nth-child(2) > table > tbody > tr:nth-child(2) > td:nth-child(3) > a']); // 編集ボタン

$I->expect('商品コード検索を確認する');
$I->switchToNewWindow();
$I->see('カテゴリ');
$I->fillField('input[name=search_product_code]', 'recipe');
$I->click('検索を開始');
$I->waitForText('おなべレシピ');

$I->click(['css' => '#recommend-search-results > tbody > tr:nth-child(2) > td:nth-child(4) > a']);
$I->switchToNewWindow();

$I->expect('おすすめ商品(1)が変更されている');
$I->see('おなべレシピ', '#admin-contents > div:nth-child(2) > table > tbody > tr:nth-child(2) > td:nth-child(2) > div > div.table-detail > div.detail-name');
