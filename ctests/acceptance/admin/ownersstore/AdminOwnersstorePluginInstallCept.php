<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('プラグインの prefilterTransform が正常に動作するかを確認する');
$I->amOnPage('/admin/'.DIR_INDEX_FILE);

$I->fillField('input[name=login_id]', 'admin');
$I->fillField('input[name=password]', 'password');
$I->click(['css' => '.btn-tool-format']);

$I->see('ログイン : 管理者 様');

$I->amGoingTo('オーナーズストア＞プラグイン管理');
$I->amOnPage('/admin/ownersstore/'.DIR_INDEX_FILE);

$I->expect('プラグインを圧縮します');

$file = 'PrefilterTransformPlugin.tar.gz';
$dir = __DIR__.'/../../../../tests/class/fixtures/plugin/PrefilterTransformPlugin';
chdir($dir);
$tar = new Archive_Tar($file, true);
if ($tar->create(['PrefilterTransformPlugin.php', 'plugin_info.php'])) {
    rename($dir.'/'.$file, __DIR__.'/../../../_data/'.$file);
    $I->attachFile(['css' => '#system > table > tbody > tr > td > input'], $file);
}
$I->expect('プラグインをインストールします');
$I->click(['css' => '#system > table > tbody > tr > td > a']); // インストールボタン

$I->wait(1);
$I->seeInPopup('プラグインをインストールしても宜しいでしょうか？');
$I->acceptPopup();

$I->wait(1);
$I->seeInPopup('プラグインをインストールしました');
$I->acceptPopup();

$I->amOnPage('/admin/ownersstore/'.DIR_INDEX_FILE);

$I->expect('プラグインを有効化します');
$I->click(['css' => '#system > table.system-plugin > tbody > tr:nth-child(2) > td.plugin_info > div > label > input[type=checkbox]']);  // 有効化ボタン

$I->wait(1);
$I->seeInPopup('プラグインを有効にしても宜しいですか？');
$I->acceptPopup();

$I->wait(1);
$I->seeInPopup('有効にしました。');
$I->acceptPopup();

$I->expect('prefilterTransform の動作を確認します');
$I->amOnPage('/products/list.php');
$I->seeInSource('<p>プラグイン仕様書の記述方法</p>');
$I->seeInSource('<p>一部のプラグインは完全一致が使用されている</p>');

$I->amOnPage('/admin/ownersstore/'.DIR_INDEX_FILE);
$I->expect('プラグインを無効化します');
$I->click(['css' => '#system > table.system-plugin > tbody > tr:nth-child(2) > td.plugin_info > div > label > input[type=checkbox]']);  // 無効化ボタン

$I->wait(1);
$I->seeInPopup('プラグインを無効にしても宜しいですか？');
$I->acceptPopup();

$I->wait(1);
$I->seeInPopup('無効にしました。');
$I->acceptPopup();

$I->expect('プラグインを削除します');
$I->click(['css' => '#system > table.system-plugin > tbody > tr:nth-child(2) > td.plugin_info > div > a:nth-child(6)']); // 削除ボタン

$I->wait(1);
$I->seeInPopup('プラグインを削除しても宜しいですか？');
$I->acceptPopup();

$I->wait(1);
$I->seeInPopup('削除しました');
$I->acceptPopup();
