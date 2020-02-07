<?php
/**
 * @group installer
 */
$I = new AcceptanceTester($scenario);
$faker = Codeception\Util\Fixtures::get('faker');
$I->wantTo('正常にインストール可能か検証する');
// $I->amOnPage('/');
$I->amOnPage('/install/');
$I->seeInCurrentUrl('/install/');
$I->see('EC-CUBEのインストールを開始します。');
$I->click('次へ進む');

$I->expect('パーミッションのチェックをします');
$I->see('チェック結果');
$I->see('>> ○：アクセス権限は正常です。', ['css' => 'textarea[name=disp_area]']);
$I->click('次へ進む');

$I->expect('必要なファイルのコピーをします');
$I->see('ice130.jpg', ['css' => 'textarea[name=disp_area]']);
$I->click('次へ進む');

$I->see('ECサイトの設定');
$I->expect('STEP1');
$admindirectory = $faker->regexify('[A-Za-z0-9]{8,10}');
$user = $faker->userName;
$password = $faker->regexify('[A-Za-z]{8,10}').$faker->regexify('[0-9]{3,5}');
$I->fillField('input[name=shop_name]', $faker->name);
$I->fillField('input[name=admin_mail]', $faker->safeEmail);
$I->fillField('input[name=login_id]', $user);
$I->fillField('input[name=login_pass]', $password);
$I->fillField('input[name=admin_dir]', $admindirectory);

$I->click('>> オプション設定');
$I->selectOption('input[name=mail_backend]', 'smtp');
$I->fillField('input[name=smtp_host]', '127.0.0.1');
$I->fillField('input[name=smtp_port]', '1025');
$I->click('次へ進む');

$I->expect('STEP2');
defined('DB_TYPE') or define('DB_TYPE', getenv('DB') == 'mysql' ? 'mysqli' : getenv('DB'));
defined('DB_USER') or define('DB_USER', getenv('DBUSER'));
defined('DB_NAME') or define('DB_NAME', getenv('DBNAME'));
defined('DB_PASSWORD') or define('DB_PASSWORD', getenv('DBPASS') );
defined('DB_PORT') or define('DB_PORT', getenv('DBPORT'));
defined('DB_SERVER') or define('DB_SERVER', getenv('DBSERVER'));

$I->selectOption('select[name=db_type]', DB_TYPE);
$I->fillField('input[name=db_server]', DB_SERVER);
$I->fillField('input[name=db_port]', DB_PORT);
$I->fillField('input[name=db_name]', DB_NAME);
$I->fillField('input[name=db_user]', DB_USER);
$I->fillField('input[name=db_password]', DB_PASSWORD);
$I->click('次へ進む');

$I->expect('STEP3');
$I->see('データベースの初期化');
$I->click('次へ進む');

$I->see('データベースの初期化');
$I->dontSee('×：テーブルの作成に失敗しました。');
$I->waitForText('○：テーブルの作成に成功しました。', 60);
$I->waitForText('○：シーケンスの作成に成功しました。', 60);
$I->click('次へ進む');

$I->expect('STEP4');
$I->see('サイト情報について');
$I->click('次へ進む');

$I->see('インストールが完了しました。');
$I->seeInDatabase('dtb_member', ['login_id' => $user]);
$I->click('管理画面へログインする');

$I->seeInCurrentUrl('/'.$admindirectory);
$I->fillField('input[name=login_id]', $user);
$I->fillField('input[name=password]', $password);
$I->click(['css' => '.btn-tool-format']);

$I->see('ログイン : 管理者 様');

$I->expect('TOPページを確認します');
$I->click(['id' => 'logo']);
$I->see('インストール完了後に /install フォルダを削除してください。');

$I->expect('/install/index.php を削除します');
$install_dir = __DIR__.'/../../html/install';
unlink($install_dir.'/'.DIR_INDEX_FILE);
$I->click(['id' => 'logo']);
$I->see('インストール完了後に /install フォルダを削除してください。');

$I->expect('/install を削除します');
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($install_dir, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::CHILD_FIRST
);
foreach ($files as $file) {
    /** @var SplFileInfo $file */
    $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getRealPath());
}
rmdir($install_dir);

$I->expect('/install が削除されていることを確認します');
$I->click(['id' => 'logo']);
$I->dontSee('インストール完了後に /install フォルダを削除してください。');
