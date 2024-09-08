import PlaywrightConfig from '../../../../playwright.config';
import { Risk } from '../../../utils/ZapClient';
import { intervalRepeater } from '../../../utils/Progress';

const url = `${ PlaywrightConfig.use?.baseURL ?? '' }/shopping/confirm.php`;
import { ShoppingPaymentPage } from '../../../pages/shopping/payment.page';

// ご注文確認画面へ進むフィクスチャ
import { test, expect } from '../../../fixtures/shopping_payment.fixture';

test.describe.serial('ご注文確認画面のテストをします', () => {

  test('ご注文確認画面へ遷移します', async ({ page }) => {
    await expect(page.locator('h2.title')).toContainText('入力内容のご確認');
    await expect(page).toHaveURL(/confirm\.php/);
  });

  test.describe('テストを実行します[GET] @attack', () => {
    let scanId: number;
    test('アクティブスキャンを実行します', async ({ page }) => {
      await page.goto(url);
      const paymentPage = new ShoppingPaymentPage(page);
      const zapClient = paymentPage.getZapClient();
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'GET');
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);

    // 結果を確認します
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
      const paymentPage = new ShoppingPaymentPage(page);
      const zapClient = paymentPage.getZapClient();
      const message = await zapClient.getLastMessage(url);
      expect(message.requestHeader).toContain(`POST ${ url }`);
      expect(message.responseHeader).toContain('HTTP/1.1 302 Found');
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', message.requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);

      // 結果を確認します
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });
});
