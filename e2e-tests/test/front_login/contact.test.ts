import { test, expect, chromium, Page } from '@playwright/test';
import PlaywrightConfig from '../../../playwright.config';
import { ZapClient, Mode, ContextType, Risk, HttpMessage } from '../../utils/ZapClient';
import { intervalRepeater } from '../../utils/Progress';
const zapClient = new ZapClient();

const inputNames = [
  'name01', 'name02', 'kana01', 'kana02', 'zip01', 'zip02', 'addr01', 'addr02',
  'tel01', 'tel02', 'tel03'
] as const;

const url = `${PlaywrightConfig.use.baseURL}/contact/index.php`;

test.describe.serial('お問い合わせページのテストをします', () => {
  let page: Page;
  test.beforeAll(async () => {
    await zapClient.setMode(Mode.Protect);
    await zapClient.newSession('/zap/wrk/sessions/front_login_contact', true);
    await zapClient.importContext(ContextType.FrontLogin);

    if (!await zapClient.isForcedUserModeEnabled()) {
      await zapClient.setForcedUserModeEnabled();
      expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy();
    }
    const browser = await chromium.launch();
    page = await browser.newPage();
    await page.goto(url);
  });

  test('お問い合わせページを表示します', async () => {
    await expect(page).toHaveTitle(/お問い合わせ/);
    await expect(page.locator('h2.title')).toContainText('お問い合わせ');
  });

  test.describe('テストを実行します[GET] @attack', () => {
    let scanId: number;
    test('アクティブスキャンを実行します', async () => {
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'GET');
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });

  test('ログイン状態を確認します', async () => {
    await page.goto(PlaywrightConfig.use.baseURL);       // ログアウトしてしまう場合があるので一旦トップへ遷移する
    await page.goto(url);
    await expect(page.locator('#header')).toContainText('ようこそ');
    inputNames.forEach(async (name) => expect(page.locator(`input[name=${name}]`)).not.toBeEmpty());
    await expect(page.locator('input[name=email]')).toHaveValue('zap_user@example.com');
    await expect(page.locator('input[name=email02]')).toHaveValue('zap_user@example.com');
  });

  let confirmMessage: HttpMessage;
  test('お問い合わせ内容を入力します', async () => {
    await page.fill('textarea[name=contents]', 'お問い合わせ入力');
    await page.click('input[name=confirm]');
    confirmMessage = await zapClient.getLastMessage(url);
  });

  test('入力内容を確認します', async () => {
    await expect(page.locator('h2.title')).toContainText('お問い合わせ(確認ページ)');
    inputNames.forEach(async (name) => {
      await expect(page.locator(`input[name=${name}]`)).toBeHidden();
      await expect(page.locator(`input[name=${name}]`)).not.toBeEmpty();
    });
    await expect(page.locator('input[name=email]')).toBeHidden();
    await expect(page.locator('input[name=email]')).toHaveValue('zap_user@example.com');
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
    await expect(page.locator('#form1 >> tr:nth-child(6) > td')).toContainText('zap_user@example.com');
    await expect(page.locator('#form1 >> tr:nth-child(7) > td')).toContainText('お問い合わせ入力');
  });

  let completeMessage: HttpMessage;
  test('お問い合わせ内容を送信します', async () => {
    await page.click('#send');
    await expect(page.locator('h2.title')).toContainText('お問い合わせ(完了ページ)');
    completeMessage = await zapClient.getLastMessage(url);
  });

  test.describe('テストを実行します[POST][入力→確認] @attack', () => {
    let requestBody: string;
    test('transactionid を取得し直します', async () => {
      await page.goto(url);
      const transactionid = await page.locator('input[name=transactionid]').first().inputValue();
      requestBody = confirmMessage.requestBody.replace(/transactionid=[a-z0-9]+/, `transactionid=${transactionid}`);
    });

    test('アクティブスキャンを実行します', async () => {
      expect(requestBody).toContain('mode=confirm');
      const scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });

  test.describe('テストを実行します[POST][確認→完了] @attack', () => {
    let requestBody: string;
    test('transactionid を取得し直します', async () => {
      await page.goto(url);
      const transactionid = await page.locator('input[name=transactionid]').first().inputValue();
      requestBody = completeMessage.requestBody.replace(/transactionid=[a-z0-9]+/, `transactionid=${transactionid}`);
    });

    test('アクティブスキャンを実行します', async () => {
      expect(completeMessage.responseHeader).toContain('HTTP/1.1 302 Found');
      expect(requestBody).toContain('mode=complete');
      const scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });

  /**
   * https://github.com/EC-CUBE/ec-cube2/pull/536 のE2Eテスト
   */
  test('ラーメッセージの表示を確認します', async () => {
    await page.goto(PlaywrightConfig.use.baseURL);       // ログアウトしてしまう場合があるので一旦トップへ遷移する
    await page.goto(url);
    await page.click('input[name=confirm]');
    await page.pause();
    await expect(page.locator('span.attention >> nth=13')).toContainText('※ お問い合わせ内容が入力されていません。');
    await expect(page.locator('textarea[name=contents]')).toHaveAttribute('style', 'background-color:#ffe8e8; ime-mode: active;');
  });
});
