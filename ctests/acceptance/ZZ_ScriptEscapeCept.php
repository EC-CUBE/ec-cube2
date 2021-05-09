<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('script_escapeを確認する。');
copy(__DIR__.'/../../data/Smarty/templates/default/index.tpl', __DIR__.'/../../data/Smarty/templates/default/index.tpl.bak');

$example = file_get_contents('https://raw.githubusercontent.com/zaproxy/zap-extensions/master/addOns/ascanrulesAlpha/src/main/zapHomeFiles/txt/example-ascan-file.txt');
$patterns = explode("\n", $example);

foreach ($patterns as $pattern) {
    if (preg_match_all('/CONTENT="0/i', $pattern)) {
        $I->comment('meta content=0 はチェックできないためスキップします');
        continue;
    }
    $pattern = str_replace('\\', '\\\\', $pattern);
    $pattern = str_replace('"', '\"', $pattern);
    $pattern = str_replace('ha.ckers.org', '127.0.0.1', $pattern);
    $I->expect($pattern.' が無効化されて alert の出ないことを確認します');

    file_put_contents(__DIR__.'/../../data/Smarty/templates/default/index.tpl', '<!--{"'.trim($pattern).'"}-->');
    $I->amOnPage('/'.DIR_INDEX_FILE);

    $I->see('EC-CUBE発!世界中を旅して見つけた立方体グルメを立方隊長が直送！');
    $I->seeElement('#site_description');
    $I->expect('body の class 名が出力されている');
    $I->seeElement(['css' => 'body'], ['class' => 'LC_Page_Index']);

    $I->expect('システムエラーが出ていない');
    $I->dontSeeElement('.error');

    array_map('unlink', glob(__DIR__.'/../../data/Smarty/templates_c/default/*.tpl.php'));
}

$I->expect("nofilter で alert の出ることを確認します");
file_put_contents(__DIR__.'/../../data/Smarty/templates/default/index.tpl', '<!--{"<script>alert(1)</script>" nofilter}-->');
$I->amOnPage('/'.DIR_INDEX_FILE);
$I->acceptPopup();
$I->see('EC-CUBE発!世界中を旅して見つけた立方体グルメを立方隊長が直送！');

copy(__DIR__.'/../../data/Smarty/templates/default/index.tpl.bak', __DIR__.'/../../data/Smarty/templates/default/index.tpl');
