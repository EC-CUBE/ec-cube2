import { test, expect, chromium, Page } from '@playwright/test';
import * as faker from 'faker';

const baseURL = 'https://ec-cube';
const url = baseURL + '/install/';

test.describe.serial('インストーラのテストをします', () => {
  let page: Page;
  test.beforeAll(async () => {
    const browser = await chromium.launch();
    page = await browser.newPage();
    await page.goto(url);
  });

  test('インストーラを表示します', async () => {
    await expect(page).toHaveURL(url);
    await expect(page.locator('.message')).toContainText('EC-CUBEのインストールを開始します。');
    await page.click('text=次へ進む');
  });

  test('step0 - パーミッションをチェックします', async () => {
    await expect(page.locator('h2')).toHaveText('チェック結果');
    await expect(page.locator('textarea[name=disp_area]')).toHaveText('>> ○：アクセス権限は正常です。');
    await page.click('text=次へ進む');
  });

  test('step0_1 - 必要なファイルをコピーします', async () => {
    await expect(page.locator('h2')).toHaveText('必要なファイルのコピー');
    await expect(page.locator('textarea[name=disp_area]')).toHaveText(/ice130.jpg/);
    await page.click('text=次へ進む');
  });

  let adminDirectory: string;
  let user: string;
  let password: string;
  test('step1 - ECサイトの設定をします', async () => {
    await expect(page.locator('h2').first()).toHaveText('ECサイトの設定');
    adminDirectory = faker.datatype.uuid().substring(0, 8);
    user = faker.internet.userName();
    password = faker.fake('{{internet.password}}{{datatype.number}}');
    await page.fill('input[name=shop_name]', faker.company.companyName());
    await page.fill('input[name=admin_mail]', faker.internet.exampleEmail());
    await page.fill('input[name=login_id]', user);
    await page.fill('input[name=login_pass]', password);
    await page.fill('input[name=admin_dir]', adminDirectory);
    await page.check('text=SSLを強制する');
    await page.fill('input[name=normal_url]', `${baseURL}/`);
    await page.fill('input[name=secure_url]', `${baseURL}/`);
    await page.click('#options');
    await page.check('text=SMTP');
    await page.fill('input[name=smtp_host]', 'mailcatcher');
    await page.fill('input[name=smtp_port]', '1025');
    await page.click('text=次へ進む');
  });

  test('step2 - データベースの設定をします', async () => {
    await expect(page.locator('h2').first()).toHaveText('データベースの設定');
    const DB = process.env.DB_TYPE == 'mysql' ? 'MySQL' : 'PostgreSQL';
    let DB_SERVER = process.env.DB_SERVER;
    let DB_PORT = process.env.DB_PORT;
    let DB_NAME = process.env.DB_NAME || 'eccube_db';
    let DB_USER = process.env.DB_USER || 'eccube_db_user';
    let DB_PASSWORD = process.env.DB_PASSWORD || 'password';
    await page.selectOption('select[name=db_type]', { label: DB });
    await page.fill('input[name=db_server]', DB_SERVER ?? 'postgres');
    await page.fill('input[name=db_port]', DB_PORT ?? '5432');
    await page.fill('input[name=db_name]', DB_NAME);
    await page.fill('input[name=db_user]', DB_USER);
    await page.fill('input[name=db_password]', DB_PASSWORD);
    await page.click('text=次へ進む');
  });

  test('step3 - データベースの初期化をします', async () => {
    await expect(page.locator('h2').first()).toHaveText('データベースの初期化');
    await page.click('text=次へ進む');

    await expect(page.locator('.contents').first()).toHaveText(/○：テーブルの作成に成功しました。/);
    await expect(page.locator('.contents').first()).toHaveText(/○：シーケンスの作成に成功しました。/);
    await page.click('text=次へ進む');
  });

  test('step4 - サイト情報の送信を確認をします', async () => {
    await expect(page.locator('h2').first()).toHaveText('サイト情報について');
    await page.click('text=次へ進む');
  });

  test('インストール完了を確認をします', async () => {
    await expect(page.locator('h2').first()).toHaveText(/インストールが完了しました/);
    await page.click('text=管理画面へログインする');
  });

  test('管理画面ログインへの遷移を確認します', async () => {
    await expect(page).toHaveURL(new RegExp(adminDirectory));
    await page.fill('input[name=login_id]', user);
    await page.fill('input[name=password]', password);
    await page.click('text=LOGIN');
  });

  test('管理画面への遷移を確認します', async () => {
    await expect(page.locator('#site-check')).toContainText('ログイン : 管理者 様');
  });

  test('トップページを確認します', async () => {
    await page.goto(baseURL);
    await expect(page.locator('#errorHeader')).toContainText('インストール完了後に /install フォルダを削除してください。');
  });
});
