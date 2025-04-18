import { test, expect } from '../../fixtures/front_login/mypage_login.fixture';
import PlaywrightConfig from '../../../playwright.config';
import { Risk, HttpMessage } from '../../utils/ZapClient';
import { intervalRepeater } from '../../utils/Progress';
import { ContactPage } from '../../pages/contact.page';

const inputNames = [
  'name01', 'name02', 'kana01', 'kana02', 'zip01', 'zip02', 'addr01', 'addr02',
  'tel01', 'tel02', 'tel03'
] as const;

const url = `${PlaywrightConfig?.use?.baseURL ?? ''}/contact/index.php`;

test.describe.serial('お問い合わせページのテストをします', () => {

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('お問い合わせページを表示します', async ( { mypageLoginPage, page } ) => {
    const contactPage = new ContactPage(page);
    await contactPage.goto();
    await expect(page).toHaveTitle(/お問い合わせ/);
    await expect(page.locator('h2.title')).toContainText('お問い合わせ');
  });

  test.describe('テストを実行します[GET] @attack', () => {
    let scanId: number;
    test('アクティブスキャンを実行します', async ({ page }) => {
      const contactPage = new ContactPage(page);
      const zapClient = contactPage.getZapClient();
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'GET');
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);

      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });

  test('ログイン状態を確認します', async ( { mypageLoginPage, page } ) => {
    await page.goto(PlaywrightConfig.use?.baseURL ?? '/');       // ログアウトしてしまう場合があるので一旦トップへ遷移する
    await page.goto(url);
    await expect(page.locator('#header')).toContainText('ようこそ');
    inputNames.forEach(async (name) => expect(page.locator(`input[name=${name}]`)).not.toBeEmpty());
    await expect(page.locator('input[name=email]')).toHaveValue(mypageLoginPage.email);
    await expect(page.locator('input[name=email02]')).toHaveValue(mypageLoginPage.email);
  });

  let confirmMessage: HttpMessage;
  let completeMessage: HttpMessage;
  test('お問い合わせ内容を入力します', async ({ mypageLoginPage, page }) => {
    const contactPage = new ContactPage(page);
    await contactPage.goto();
    const zapClient = contactPage.getZapClient();
    await page.fill('textarea[name=contents]', 'お問い合わせ入力');
    await page.click('input[name=confirm]');

    if (zapClient.isAvailable()) {
      confirmMessage = await zapClient.getLastMessage(url);
    }

    // 入力内容を確認します
    await expect(page.locator('h2.title')).toContainText('お問い合わせ(確認ページ)');
    inputNames.forEach(async (name) => {
      await expect(page.locator(`input[name=${name}]`)).toBeHidden();
      await expect(page.locator(`input[name=${name}]`)).not.toBeEmpty();
    });
    await expect(page.locator('input[name=email]')).toBeHidden();
    await expect(page.locator('input[name=email]')).toHaveValue(mypageLoginPage.email);
    await expect(page.locator('input[name=contents]')).toBeHidden();
    await expect(page.locator('input[name=contents]')).toHaveValue('お問い合わせ入力');

    await expect(page.locator('#form1 >> tr:nth-child(1) > td')).toContainText(await page.locator('input[name=name01]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(1) > td')).toContainText(await page.locator('input[name=name02]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(2) > td')).toContainText(await page.locator('input[name=kana01]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(2) > td')).toContainText(await page.locator('input[name=kana02]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(3) > td')).toContainText(await page.locator('input[name=zip01]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(3) > td')).toContainText(await page.locator('input[name=zip02]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(4) > td')).toContainText(await page.locator('input[name=addr01]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(4) > td')).toContainText(await page.locator('input[name=addr02]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(5) > td')).toContainText(await page.locator('input[name=tel01]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(5) > td')).toContainText(await page.locator('input[name=tel02]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(5) > td')).toContainText(await page.locator('input[name=tel03]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(6) > td')).toContainText(mypageLoginPage.email);
    await expect(page.locator('#form1 >> tr:nth-child(7) > td')).toContainText('お問い合わせ入力');

    // お問い合わせ内容を送信します
    await page.click('#send');
    await expect(page.locator('h2.title')).toContainText('お問い合わせ(完了ページ)');
  });

  test.describe('テストを実行します[POST][入力→確認] @attack', () => {
    let requestBody: string;

    test('アクティブスキャンを実行します', async ({ page }) => {
      const contactPage = new ContactPage(page);
      const zapClient = contactPage.getZapClient();

      completeMessage = await zapClient.getLastMessage(url);

      // transactionid を取得し直します
      await page.goto(url);
      const transactionid = await page.locator('input[name=transactionid]').first().inputValue();
      requestBody = confirmMessage.requestBody.replace(/transactionid=[a-z0-9]+/, `transactionid=${transactionid}`);
      expect(requestBody).toContain('mode=confirm');
      const scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);

      // 結果を確認します
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });
  test.describe('テストを実行します[POST][確認→完了] @attack', () => {
    let requestBody: string;

    test('アクティブスキャンを実行します', async ({ page }) => {

      // transactionid を取得し直します
      await page.goto(url);
      const contactPage = new ContactPage(page);
      const zapClient = contactPage.getZapClient();
      const transactionid = await page.locator('input[name=transactionid]').first().inputValue();
      requestBody = completeMessage.requestBody.replace(/transactionid=[a-z0-9]+/, `transactionid=${transactionid}`);
      expect(completeMessage.responseHeader).toContain('HTTP/1.1 302 Found');
      expect(requestBody).toContain('mode=complete');
      const scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);

      // 結果を確認します
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });

  /**
   * https://github.com/EC-CUBE/ec-cube2/pull/536 のE2Eテスト
   */
  test('エラーメッセージの表示を確認します', async ({ page }) => {
    await page.goto(PlaywrightConfig?.use?.baseURL ?? '/');       // ログアウトしてしまう場合があるので一旦トップへ遷移する
    await page.goto(url);
    await page.click('input[name=confirm]');
    await expect(page.locator('table[summary="お問い合わせ"] span.attention >> nth=12')).toContainText('※ お問い合わせ内容が入力されていません。');
    await expect(page.locator('textarea[name=contents]')).toHaveAttribute('style', 'background-color:#ffe8e8; ime-mode: active;');
  });
});
