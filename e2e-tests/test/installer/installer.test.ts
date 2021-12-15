import { Builder, By, until } from 'selenium-webdriver'
import { SeleniumCapabilities } from '../../utils/SeleniumCapabilities';
import * as faker from 'faker';

jest.setTimeout(6000000);

const baseURL = 'https://ec-cube';
const url = baseURL + '/install/';

test('[E2E] インストールを実行する', async () => {
  const driver = new Builder()
    .withCapabilities(SeleniumCapabilities)
    .build();
  try {
    driver.get(url);
    await driver.wait(until.elementLocated(By.className('message')), 10000)
        .getText().then(title => expect(title).toBe('EC-CUBEのインストールを開始します。'));
    await driver.findElement(By.linkText('次へ進む')).click();

    await driver.wait(until.elementLocated(By.css('h2')), 10000)
      .getText().then(title => expect(title).toBe('チェック結果'));
    await driver.findElement(By.css('textarea[name=disp_area]')).getText()
      .then(value => expect(value).toBe('>> ○：アクセス権限は正常です。'));
    await driver.findElement(By.linkText('次へ進む')).click();

    await driver.wait(until.elementLocated(By.css('textarea[name=disp_area]')), 10000)
      .getText().then(title => expect(title).toContain('ice130.jpg'));
    await driver.findElement(By.linkText('次へ進む')).click();

    await driver.wait(until.elementLocated(By.css('h2')), 10000)
      .getText().then(title => expect(title).toBe('ECサイトの設定'));
    const adminDirectory = faker.datatype.uuid().substring(0, 8);
    const user = faker.internet.userName();
    const password = faker.fake('{{internet.password}}{{datatype.number}}');

    await driver.findElement(By.name('shop_name')).sendKeys(faker.company.companyName());
    await driver.findElement(By.name('admin_mail')).sendKeys(faker.internet.exampleEmail());
    await driver.findElement(By.name('login_id')).sendKeys(user);
    await driver.findElement(By.name('login_pass')).sendKeys(password);
    await driver.findElement(By.name('admin_dir')).sendKeys(adminDirectory);

    await driver.findElement(By.linkText('>> オプション設定')).click();
    await driver.wait(until.elementLocated(By.css('div.option > h2')), 10000)
      .getText().then(title => expect(title).toBe('メールサーバーの設定(オプション)'));
    await driver.findElement(By.css('div.option input[name=mail_backend][value=smtp]'))
      .then(async (element) => {
        await driver.executeScript("arguments[0].scrollIntoView()", element);
        await driver.sleep(300);
        await element.click();
      });

    await driver.findElement(By.name('smtp_host'))
      .then(element => {
        element.clear();
        element.sendKeys('127.0.0.1');
      });
    await driver.findElement(By.name('smtp_port'))
      .then(element => {
        element.clear();
        element.sendKeys('1025');
      });
    await driver.findElement(By.linkText('次へ進む')).click();

    await driver.wait(until.elementLocated(By.css('h2')), 10000)
      .getText().then(title => expect(title).toBe('データベースの設定'));
    const DB_TYPE = process.env.DB_TYPE == 'mysql' ? 'mysqli' : process.env.DB_TYPE || 'pgsql';
    const DB_SERVER = process.env.DB_SERVER || DB_TYPE == 'mysqli' ? 'mysql' : 'postgres';
    const DB_PORT = process.env.DB_PORT || DB_TYPE == 'mysqli' ? '3306' : '5432';
    const DB_NAME = process.env.DB_NAME || 'eccube_db';
    const DB_USER = process.env.DB_USER || 'eccube_db_user';
    const DB_PASSWORD = process.env.DB_PASSWORD || 'password';
    await driver.findElement(By.css(`select[name=db_type] > option[value=${DB_TYPE}]`)).click();
    await driver.findElement(By.name('db_server'))
      .then(element => {
        element.clear();
        element.sendKeys(DB_SERVER)
      });
    await driver.findElement(By.name('db_port'))
      .then(element => {
        element.clear();
        element.sendKeys(DB_PORT);
      });
    await driver.findElement(By.name('db_name'))
      .then(element => {
        element.clear();
        element.sendKeys(DB_NAME);
      });
    await driver.findElement(By.name('db_user'))
      .then(element => {
        element.clear();
        element.sendKeys(DB_USER)
      });
    await driver.findElement(By.name('db_password')).sendKeys(DB_PASSWORD);
    await driver.findElement(By.linkText('次へ進む')).click();

    await driver.wait(until.elementLocated(By.css('h2')), 10000)
      .getText().then(title => expect(title).toBe('データベースの初期化'));
    await driver.findElement(By.linkText('次へ進む')).click();

    await driver.wait(until.elementLocated(By.className('contents')), 60000)
      .getText().then(title => expect(title).toContain('○：テーブルの作成に成功しました。'));
    await driver.wait(until.elementLocated(By.className('contents')), 60000)
      .getText().then(title => expect(title).toContain('○：シーケンスの作成に成功しました。'));
    await driver.findElement(By.linkText('次へ進む')).click();

    await driver.wait(until.elementLocated(By.css('h2')), 10000)
      .getText().then(title => expect(title).toBe('サイト情報について'));
    await driver.findElement(By.linkText('次へ進む')).click();

    await driver.wait(until.elementLocated(By.css('h2')), 10000)
      .getText().then(title => expect(title).toContain('インストールが完了しました。'));
    await driver.findElement(By.linkText('管理画面へログインする')).click();

    await driver.wait(until.elementLocated(By.className('btn-tool-format')), 10000)
      .getText().then(value => expect(value).toBe('LOGIN'));
    await driver.getCurrentUrl()
      .then(url => expect(url).toContain(adminDirectory));
    await driver.findElement(By.name('login_id')).sendKeys(user);
    await driver.findElement(By.name('password')).sendKeys(password);
    await driver.findElement(By.className('btn-tool-format')).click();

    await driver.wait(until.elementLocated(By.id('site-check')), 10000)
      .getText().then(value => expect(value).toContain('ログイン : 管理者 様'));

    await driver.findElement(By.id('logo')).click();
    await driver.wait(until.elementLocated(By.id('errorHeader')), 10000)
      .getText().then(value => expect(value).toContain('インストール完了後に /install フォルダを削除してください。'));
  } finally {
    driver && driver.quit();
  }
});
