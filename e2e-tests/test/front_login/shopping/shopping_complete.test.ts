import PlaywrightConfig from '../../../../playwright.config';
import { Risk } from '../../../utils/ZapClient';
import { intervalRepeater } from '../../../utils/Progress';

const url = `${ PlaywrightConfig.use?.baseURL ?? '' }/shopping/complete.php`;
import { ShoppingPaymentPage } from '../../../pages/shopping/payment.page';

// ご注文確認画面へ進むフィクスチャ
import { test, expect } from '../../../fixtures/shopping_payment.fixture';

test.describe.serial('ご注文完了画面のテストをします', () => {

  test('ご注文完了画面へ遷移します', async ({ shoppingPaymentLoginPage, page }) => {
    await page.click('[alt=ご注文完了ページへ]');
    await expect(page.locator('h2.title')).toContainText('ご注文完了');
  });

  test.describe('テストを実行します[GET] @attack', () => {
    let scanId: number;
    test('アクティブスキャンを実行します', async ({ page }) => {
      const paymentPage = new ShoppingPaymentPage(page);
      const zapClient = paymentPage.getZapClient();
      await page.goto(url);
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'GET');
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);

      // 結果を確認します
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });
});
