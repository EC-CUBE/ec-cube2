import { Builder, By, until } from 'selenium-webdriver'
import { ZapClient, Mode, ContextType, Risk } from '../../utils/ZapClient';
import { intervalRepeater } from '../../utils/Progress';
import { SeleniumCapabilities } from '../../utils/SeleniumCapabilities';
const zapClient = new ZapClient();


jest.setTimeout(6000000);

const inputNames = [
  'name01', 'name02', 'kana01', 'kana02', 'zip01', 'zip02', 'addr01', 'addr02',
  'tel01', 'tel02', 'tel03'
] as const;
type InputName = {
  [key in typeof inputNames[number]]?: string
};

const baseURL = 'https://ec-cube';
const url = baseURL + '/contact/';

beforeAll(async () => {
  await zapClient.setMode(Mode.Protect);
  await zapClient.newSession('/zap/wrk/sessions/front_login_contact', true);
  await zapClient.importContext(ContextType.FrontLogin);

  if (!await zapClient.isForcedUserModeEnabled()) {
    await zapClient.setForcedUserModeEnabled();
    expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy();
  }
});

describe('お問い合わせページを表示する', () => {
  test('[E2E] お問い合わせページを表示し、ログイン状態を確認する', async () => {
    const driver = await new Builder()
      .withCapabilities(SeleniumCapabilities)
      .build();
    try {
      expect.assertions(15);
      await driver.get(url);
      await driver.wait(
        until.elementLocated(By.css('h2.title')), 10000)
        .getText().then(title => expect(title).toBe('お問い合わせ(入力ページ)'));

      inputNames.forEach(
        async (name) => await driver.findElement(By.name(name)).getAttribute('value')
          .then(value => expect(value).toEqual(expect.anything()))
      );
      await driver.findElement(By.name('email')).getAttribute('value').then(value => expect(value).toBe('zap_user@example.com'));
      await driver.findElement(By.name('email02')).getAttribute('value').then(value => expect(value).toBe('zap_user@example.com'));

    } finally {
      driver && await driver.quit();
    }
  });

  describe('[ATTACK] お問い合わせページの表示をスキャンする', () => {
    test('GET でお問い合わせページをスキャンする', async () => {
      const scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'GET');

      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000);

      const alerts = await zapClient.getAlerts(url, 0, 1, Risk.High);
      alerts.forEach(alert => {
        throw new Error(alert.name);
      });
      expect(alerts).toHaveLength(0);
    });
  });
});

describe('お問い合わせ確認ページを表示する', () => {
  test('[E2E] お問い合わせページに入力し、確認画面に進む', async () => {
    const driver = await new Builder()
      .withCapabilities(SeleniumCapabilities)
      .build();
    try {
      await driver.get(url);
      await driver.wait(
        until.elementLocated(By.css('h2.title')), 10000)
        .getText().then(title => expect(title).toBe('お問い合わせ(入力ページ)'));

      // 入力値を代入しておく
      let inputField: InputName = {};
      inputNames.forEach(
        async (name) => await driver.findElement(By.name(name)).getAttribute('value')
          .then(value => inputField[name] = value)
      );

      await driver.findElement(By.name('contents')).sendKeys('お問い合わせ内容入力');
      await driver.findElement(By.name('confirm')).click();

      await driver.wait(
        until.elementLocated(By.css('h2.title')), 10000)
        .getText().then(title => expect(title).toBe('お問い合わせ(確認ページ)'));

      // hidden に入力されているかどうか
      inputNames.forEach(
        async (name) => await driver.findElement(By.name(name)).getAttribute('value')
          .then(value => expect(value).toBe(inputField[name]))
      );
      // 確認画面に表示されているかどうか
      await driver.findElement(By.xpath('//*[@id="form1"]/table/tbody/tr[1]/td')).getText()
        .then(value => expect(value).toBe(`${inputField.name01}　${inputField.name02}`));
      await driver.findElement(By.xpath('//*[@id="form1"]/table/tbody/tr[2]/td')).getText()
        .then(value => expect(value).toBe(`${inputField.kana01}　${inputField.kana02}`));
      await driver.findElement(By.xpath('//*[@id="form1"]/table/tbody/tr[3]/td')).getText()
        .then(value => expect(value).toBe(`〒${inputField.zip01}-${inputField.zip02}`));
      await driver.findElement(By.xpath('//*[@id="form1"]/table/tbody/tr[4]/td')).getText()
        .then(value => expect(value).toContain(`${inputField.addr01}${inputField.addr02}`));
      await driver.findElement(By.xpath('//*[@id="form1"]/table/tbody/tr[5]/td')).getText()
        .then(value => expect(value).toBe(`${inputField.tel01}-${inputField.tel02}-${inputField.tel03}`));
      await driver.findElement(By.xpath('//*[@id="form1"]/table/tbody/tr[6]/td')).getText()
        .then(value => expect(value).toBe('zap_user@example.com'));
      await driver.findElement(By.xpath('//*[@id="form1"]/table/tbody/tr[7]/td')).getText()
        .then(value => expect(value).toBe('お問い合わせ内容入力'));
    } finally {
      driver && await driver.quit();
    }
  });

  describe('[ATTACK] お問い合わせ(確認ページ)をスキャンする', () => {
    test('POST でお問い合わせ(確認ページ)をスキャンする', async () => {

      const message = await zapClient.getLastMessage(url);
      const scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'GET', message.requestBody); // XXX なぜか  method=POST にすると url_not_found のエラーになる. GET にしていても POST でスキャンされる

      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000);

      const alerts = await zapClient.getAlerts(url, 0, 1, Risk.High);
      alerts.forEach(alert => {
        throw new Error(alert.name);
      });
      expect(alerts).toHaveLength(0);
    });
  });
});
