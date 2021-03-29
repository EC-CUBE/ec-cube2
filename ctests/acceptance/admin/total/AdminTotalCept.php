<?php
$I = new AcceptanceTester($scenario);
if (!function_exists('ImageTTFText')) {
    $scenario->skip('Freetype not supported.');
}
$I->wantTo('売上集計画面を確認する');
$I->amOnPage('/'.ADMIN_DIR.'/'.DIR_INDEX_FILE);

$I->fillField('input[name=login_id]', 'admin');
$I->fillField('input[name=password]', 'password');
$I->click(['css' => '.btn-tool-format']);

$I->see('ログイン : 管理者 様');

$I->amGoingTo('売上集計＞期間別集計');
$I->amOnPage('/'.ADMIN_DIR.'/total/'.DIR_INDEX_FILE.'?page=term');

$I->expect('日付の初期値を確認する');
$I->seeInField('select[name=search_startyear_m]', date('Y'));
$I->seeInField('select[name=search_startmonth_m]', date('n'));

$I->seeInField('select[name=search_startyear]', date('Y'));
$I->seeInField('select[name=search_startmonth]', date('n'));
$I->seeInField('select[name=search_startday]', date('j'));

$I->seeInField('select[name=search_endyear]', date('Y'));
$I->seeInField('select[name=search_endmonth]', date('n'));
$I->seeInField('select[name=search_endday]', date('j'));

$I->amGoingTo('売上集計>期間別集計>月度集計');
$I->click('月度で集計する');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-term']);


$I->amGoingTo('売上集計>期間別集計>期間集計');
$I->selectOption('select[name=search_startyear]', date('Y', strtotime('-1 year')));
$I->click('期間で集計する');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-term']);


$I->amGoingTo('売上集計>期間別集計>期間集計>月別');
$I->click('月別');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-term']);


$I->amGoingTo('売上集計>期間別集計>期間集計>年別');
$I->click('年別');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-term']);


$I->amGoingTo('売上集計>期間別集計>期間集計>曜日別');
$I->click('曜日別');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-term']);

$I->amGoingTo('売上集計>期間別集計>期間集計>時間別');
$I->click('時間別');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-term']);

$I->click('CSVダウンロード');
$file = $I->getLastDownloadFile('/^total\d{12}\.csv$/');
$I->assertTrue(count(file($file)) >= 2, '2行以上のファイルがダウンロードされている');


$I->amGoingTo('売上集計＞商品別集計');
$I->amOnPage('/'.ADMIN_DIR.'/total/'.DIR_INDEX_FILE.'?page=products');

$I->expect('日付の初期値を確認する');
$I->seeInField('select[name=search_startyear_m]', date('Y'));
$I->seeInField('select[name=search_startmonth_m]', date('n'));

$I->seeInField('select[name=search_startyear]', date('Y'));
$I->seeInField('select[name=search_startmonth]', date('n'));
$I->seeInField('select[name=search_startday]', date('j'));

$I->seeInField('select[name=search_endyear]', date('Y'));
$I->seeInField('select[name=search_endmonth]', date('n'));
$I->seeInField('select[name=search_endday]', date('j'));

$I->amGoingTo('売上集計>商品別集計>月度集計');
$I->click('月度で集計する');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-products']);


$I->amGoingTo('売上集計>商品集計>期間集計');
$I->selectOption('select[name=search_startyear]', date('Y', strtotime('-1 year')));
$I->click('期間で集計する');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-products']);


$I->amGoingTo('売上集計>商品集計>期間集計>会員');
$I->click('会員');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-products']);


$I->amGoingTo('売上集計>商品集計>期間集計>非会員');
$I->click('非会員');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-products']);

$I->click('CSVダウンロード');
$file = $I->getLastDownloadFile('/^total\d{12}\.csv$/');
$I->assertTrue(count(file($file)) >= 2, '2行以上のファイルがダウンロードされている');


$I->amGoingTo('売上集計＞年代別集計');
$I->amOnPage('/'.ADMIN_DIR.'/total/'.DIR_INDEX_FILE.'?page=age');

$I->expect('日付の初期値を確認する');
$I->seeInField('select[name=search_startyear_m]', date('Y'));
$I->seeInField('select[name=search_startmonth_m]', date('n'));

$I->seeInField('select[name=search_startyear]', date('Y'));
$I->seeInField('select[name=search_startmonth]', date('n'));
$I->seeInField('select[name=search_startday]', date('j'));

$I->seeInField('select[name=search_endyear]', date('Y'));
$I->seeInField('select[name=search_endmonth]', date('n'));
$I->seeInField('select[name=search_endday]', date('j'));

$I->amGoingTo('売上集計>年代別集計>月度集計');
$I->click('月度で集計する');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-age']);


$I->amGoingTo('売上集計>年代別集計>期間集計');
$I->selectOption('select[name=search_startyear]', date('Y', strtotime('-1 year')));
$I->click('期間で集計する');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-age']);


$I->amGoingTo('売上集計>年代別集計>期間集計>会員');
$I->click('会員');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-age']);


$I->amGoingTo('売上集計>年代別集計>期間集計>非会員');
$I->click('非会員');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-age']);

$I->click('CSVダウンロード');
$file = $I->getLastDownloadFile('/^total\d{12}\.csv$/');
$I->assertTrue(count(file($file)) >= 2, '2行以上のファイルがダウンロードされている');


$I->amGoingTo('売上集計＞職業別集計');
$I->amOnPage('/'.ADMIN_DIR.'/total/'.DIR_INDEX_FILE.'?page=job');

$I->expect('日付の初期値を確認する');
$I->seeInField('select[name=search_startyear_m]', date('Y'));
$I->seeInField('select[name=search_startmonth_m]', date('n'));

$I->seeInField('select[name=search_startyear]', date('Y'));
$I->seeInField('select[name=search_startmonth]', date('n'));
$I->seeInField('select[name=search_startday]', date('j'));

$I->seeInField('select[name=search_endyear]', date('Y'));
$I->seeInField('select[name=search_endmonth]', date('n'));
$I->seeInField('select[name=search_endday]', date('j'));

$I->amGoingTo('売上集計>職業別集計>月度集計');
$I->click('月度で集計する');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-job']);


$I->amGoingTo('売上集計>職業別集計>期間集計');
$I->selectOption('select[name=search_startyear]', date('Y', strtotime('-1 year')));
$I->click('期間で集計する');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-job']);

$I->click('CSVダウンロード');
$file = $I->getLastDownloadFile('/^total\d{12}\.csv$/');
$I->assertTrue(count(file($file)) >= 2, '2行以上のファイルがダウンロードされている');


$I->amGoingTo('売上集計＞会員別集計');
$I->amOnPage('/'.ADMIN_DIR.'/total/'.DIR_INDEX_FILE.'?page=member');

$I->expect('日付の初期値を確認する');
$I->seeInField('select[name=search_startyear_m]', date('Y'));
$I->seeInField('select[name=search_startmonth_m]', date('n'));

$I->seeInField('select[name=search_startyear]', date('Y'));
$I->seeInField('select[name=search_startmonth]', date('n'));
$I->seeInField('select[name=search_startday]', date('j'));

$I->seeInField('select[name=search_endyear]', date('Y'));
$I->seeInField('select[name=search_endmonth]', date('n'));
$I->seeInField('select[name=search_endday]', date('j'));

$I->amGoingTo('売上集計>会員別集計>月度集計');
$I->click('月度で集計する');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-member']);


$I->amGoingTo('売上集計会員別集計>期間集計');
$I->selectOption('select[name=search_startyear]', date('Y', strtotime('-1 year')));
$I->click('期間で集計する');

$I->expect('表の表示を確認する');
$I->seeElement(['id' => 'total-member']);

$I->click('CSVダウンロード');
$file = $I->getLastDownloadFile('/^total\d{12}\.csv$/');
$I->assertTrue(count(file($file)) >= 2, '2行以上のファイルがダウンロードされている');
