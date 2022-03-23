import { Page } from '@playwright/test';
import PlaywrightConfig from '../../../../playwright.config';
import { ShoppingPaymentPage } from '../../../pages/shopping/payment.page';
import { ZapClient, ContextType, Risk } from '../../../utils/ZapClient';
import { intervalRepeater } from '../../../utils/Progress';
const zapClient = new ZapClient();

const url = `${PlaywrightConfig.use.baseURL}/shopping/payment.php`;

// お支払い方法・お届け時間の指定へ進むフィクスチャ
import { test, expect } from '../../../fixtures/shopping_deliv.fixture';

test.describe.serial('お支払方法・お届け時間等の指定画面のテストをします', () => {
  test.beforeAll(async () => {
    await zapClient.startSession(ContextType.FrontLogin, 'front_login_shopping_payment')
      .then(async () => expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy());
  });

  test('お支払方法・お届け時間等の指定画面へ遷移します', async ({ page }) => {
    await expect(page.locator('h2.title')).toContainText('お支払方法・お届け時間等の指定');
    await page.goto(url);
  });

  test.describe('テストを実行します[GET] @attack', () => {
    let scanId: number;
    test('アクティブスキャンを実行します', async ({ page }) => {
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'GET');
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });

  test('注文確認画面へ遷移します', async ({ page }) => {
    const paymentPage = new ShoppingPaymentPage(page);
    await paymentPage.goto();
    await paymentPage.fillOut();
    await paymentPage.gotoNext();
    await expect(page.locator('h2.title')).toContainText('入力内容のご確認');
  });

  /** 最新の transactionid で手動リクエストを送信し, HttpMessage を取得します */
  const getMessage = async (page: Page) => {
    const message = await zapClient.getLastMessage(url);
    const transactionid = await page.locator('input[name=transactionid]').first().inputValue();
    const requestBody = message.requestBody.replace(/transactionid=[a-z0-9]+/, `transactionid=${transactionid}`);
    await zapClient.sendRequest(`${message.requestHeader}${requestBody}&mode_confirm=dummy`);
    return await zapClient.getLastMessage(url);
  };

  test.describe('確認ページへ進むテストを実行します[POST] @attack', () => {
    let scanId: number;
    test('アクティブスキャンを実行します', async ({ page }) => {
      const paymentPage = new ShoppingPaymentPage(page);
      await paymentPage.goto();
      await paymentPage.fillOut();
      await paymentPage.gotoNext();
      await expect(page.locator('h2.title')).toContainText('入力内容のご確認');

      const message = await getMessage(page);
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
