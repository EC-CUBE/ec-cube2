import { Page } from '@playwright/test';
import PlaywrightConfig from '../../../../playwright.config';
import { ZapClient, ContextType, Risk } from '../../../utils/ZapClient';
import { intervalRepeater } from '../../../utils/Progress';
const zapClient = new ZapClient();

const url = `${PlaywrightConfig.use.baseURL}/cart/index.php`;
import { CartPage } from '../../../pages/cart.page';

// 商品をカートに入れて購入手続きへ進むフィクスチャ
import { test, expect } from '../../../fixtures/cartin.fixture';

test.describe.serial('カートページのテストをします', () => {
  test.beforeAll(async () => {
    await zapClient.startSession(ContextType.FrontLogin, 'front_login_cart');
  });

  test('カートの内容を確認します', async ( { page } ) => {
    const cartPage = new CartPage(page);
    await cartPage.goto();
    await expect(page.locator('h2.title')).toContainText('現在のカゴの中');
    await expect(page.locator('table[summary=商品情報] >> tr >> nth=1')).toContainText('アイスクリーム');
  });

  test.describe('テストを実行します[GET] @attack', () => {
    let scanId: number;
    test('アクティブスキャンを実行します', async ( { page } ) => {
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'GET');
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });

  const getMessage = async (page: Page, additionParams: string) => {
    const result = await zapClient.getMessages(url, await zapClient.getNumberOfMessages(url) - 1, 1);
    const message = result.pop();
    const transactionid = await page.locator('input[name=transactionid]').first().inputValue();
    const requestBody = message.requestBody.replace(/transactionid=[a-z0-9]+/, `transactionid=${transactionid}`);
    await zapClient.sendRequest(`${message.requestHeader}${requestBody}${additionParams}`);
    return await zapClient.getLastMessage(url);
  };

  test('カートの数量を加算します', async ( { page } ) => {
    const cartPage = new CartPage(page);
    await cartPage.goto();
    const quantity = parseInt(await cartPage.getQuantity().textContent());
    await cartPage.addition();
    await expect(cartPage.getQuantity()).toContainText(String(quantity + 1));
  });

  test.describe('数量加算のテストを実行します[POST] @attack', () => {
    let scanId: number;
    test('アクティブスキャンを実行します', async ( { page } ) => {
      const cartPage = new CartPage(page);
      await cartPage.goto();
      await cartPage.addition();
      const requestBody = await getMessage(page, '&mode_up=dummy').then(httpMessage => httpMessage.requestBody);
      expect(requestBody).toContain('mode=up');
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });

  test('カートの数量を減算します', async ( { page } ) => {
    const cartPage = new CartPage(page);
    await cartPage.goto();
    const quantity = parseInt(await cartPage.getQuantity().textContent());
    await cartPage.subtruction();
    await expect(cartPage.getQuantity()).toContainText(String(quantity - 1));
  });

  test.describe('数量減算のテストを実行します[POST] @attack', () => {
    let scanId: number;
    test('アクティブスキャンを実行します', async ( { page } ) => {
      const cartPage = new CartPage(page);
      await cartPage.goto();
      const transactionid = await page.locator('input[name=transactionid]').first().inputValue();
      // JavaScript でカートの数量を増やしておく
      await page.evaluate(async (transactionid: string) => {
        await (async () => {
          const searchParams = data => {
            const params = new URLSearchParams();
            Object.keys(data).forEach(key => params.append(key, data[key]));
            return params;
          };
          const response = await fetch('/cart/index.php', {
            method: 'POST',
            cache: 'no-cache',
            headers: {
              'ContentType': 'application/x-www-form-urlencoded'
            },
            body: searchParams({
              transactionid: transactionid,
              mode: 'setQuantity',
              quantity: '1000',
              cart_no: '1',
              cartKey: '1'
            })
          });

          return response.body
        })();
      }, transactionid);

      await cartPage.subtruction();
      const requestBody = await getMessage(page, '&mode_down=dummy').then(httpMessage => httpMessage.requestBody);
      expect(requestBody).toContain('mode=down');
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });
});
