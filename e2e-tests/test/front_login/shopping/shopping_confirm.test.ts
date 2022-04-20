import PlaywrightConfig from '../../../../playwright.config';
import { ZapClient, ContextType, Risk } from '../../../utils/ZapClient';
import { intervalRepeater } from '../../../utils/Progress';
const zapClient = new ZapClient();

const url = `${PlaywrightConfig.use.baseURL}/shopping/confirm.php`;

// ご注文確認画面へ進むフィクスチャ
import { test, expect } from '../../../fixtures/shopping_payment.fixture';

test.describe.serial('ご注文確認画面のテストをします', () => {
  test.beforeAll(async () => {
    await zapClient.startSession(ContextType.FrontLogin, 'front_login_shopping_confirm')
      .then(async () => expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy());
  });

  test('ご注文確認画面へ遷移します', async ({ page }) => {
    await expect(page.locator('h2.title')).toContainText('入力内容のご確認');
    await expect(page).toHaveURL(/confirm\.php/);
  });

  test.describe('テストを実行します[GET] @attack', () => {
    let scanId: number;
    test('アクティブスキャンを実行します', async ({ page }) => {
      await page.goto(url);
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'GET');
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });

  test('注文完了ページへ遷移します', async ({ page }) => {
    await page.click('[alt=ご注文完了ページへ]');
    await expect(page.locator('h2.title')).toContainText('ご注文完了');
  });

  test.describe('注文完了ページへ進むテストを実行します[POST] @attack', () => {
    let scanId: number;
    test('アクティブスキャンを実行します', async ({ page }) => {
      await page.click('[alt=ご注文完了ページへ]');
      const message = await zapClient.getLastMessage(url);
      expect(message.requestHeader).toContain(`POST ${url}`);
      expect(message.responseHeader).toContain('HTTP/1.1 302 Found');
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', message.requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });
});
