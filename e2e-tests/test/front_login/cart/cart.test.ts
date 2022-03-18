import { test, expect, chromium, Page } from '@playwright/test';
import PlaywrightConfig from '../../../../playwright.config';
import { ZapClient, Mode, ContextType, Risk, HttpMessage } from '../../../utils/ZapClient';
import { intervalRepeater } from '../../../utils/Progress';
const zapClient = new ZapClient();

const url = `${PlaywrightConfig.use.baseURL}/cart/index.php`;

test.describe.serial('カートページのテストをします', () => {
  let page: Page;
  test.beforeAll(async () => {
    await zapClient.setMode(Mode.Protect);
    await zapClient.newSession('/zap/wrk/sessions/front_login_cart', true);
    await zapClient.importContext(ContextType.FrontLogin);

    if (!await zapClient.isForcedUserModeEnabled()) {
      await zapClient.setForcedUserModeEnabled();
      expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy();
    }
    const browser = await chromium.launch();
    page = await browser.newPage();
    await page.goto(url);
  });

  const detailURL = `${PlaywrightConfig.use.baseURL}/products/detail.php?product_id=1`;
  test('商品詳細ページを表示します', async () => {
    await page.goto(detailURL);
    await expect(page.locator('#detailrightbloc > h2')).toContainText('アイスクリーム');
  });

  test('商品をカートに入れます', async () => {
    await page.selectOption('select[name=classcategory_id1]', { label: '抹茶' });
    await page.selectOption('select[name=classcategory_id2]', { label: 'S' });
    await page.fill('input[name=quantity]', '2');
    await page.click('[alt=カゴに入れる]');
  });

  test('カートの内容を確認します', async () => {
    await expect(page.locator('h2.title')).toContainText('現在のカゴの中');
    await expect(page.locator('table[summary=商品情報] >> tr >> nth=1')).toContainText('アイスクリーム');
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

  test('カートの数量を加算します', async () => {
    await page.reload();
    const quantity = parseInt(await page.locator('table[summary=商品情報] >> tr >> nth=1 >> td >> nth=4').textContent());
    await page.click('table[summary=商品情報] >> tr >> nth=1 >> td >> nth=4 >> [alt="＋"]');
    await expect(page.locator('table[summary=商品情報] >> tr >> nth=1 >> td >> nth=4')).toContainText(String(quantity + 1));
  });

  test.describe('数量加算のテストを実行します[POST] @attack', () => {

    let message: HttpMessage;
    let requestBody: string;
    test('履歴を取得します', async () => {
      const result = await zapClient.getMessages(url, await zapClient.getNumberOfMessages(url) - 1, 1);
      message = result.pop();
      expect(message.requestBody).toContain('mode=up');
    });
    test('transactionid を取得し直します', async () => {
      await page.goto(url);
      const transactionid = await page.locator('input[name=transactionid]').first().inputValue();
      requestBody = message.requestBody.replace(/transactionid=[a-z0-9]+/, `transactionid=${transactionid}`);
    });

    let scanId: number;
    test('アクティブスキャンを実行します', async () => {
      expect(requestBody).toContain('mode=up');
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });

  test('カートの数量を減算します', async () => {
    await page.reload();
    const quantity = parseInt(await page.locator('table[summary=商品情報] >> tr >> nth=1 >> td >> nth=4').textContent());
    await page.click('table[summary=商品情報] >> tr >> nth=1 >> td >> nth=4 >> [alt="-"]');
    await expect(page.locator('table[summary=商品情報] >> tr >> nth=1 >> td >> nth=4')).toContainText(String(quantity - 1));
  });

  test.describe('数量減算のテストを実行します[POST] @attack', () => {

    let message: HttpMessage;
    let requestBody: string;
    test('履歴を取得します', async () => {
      const result = await zapClient.getMessages(url, await zapClient.getNumberOfMessages(url) - 1, 1);
      message = result.pop();
    });
    test('transactionid を取得し直します', async () => {
      await page.goto(url);
      const transactionid = await page.locator('input[name=transactionid]').first().inputValue();
      requestBody = message.requestBody.replace(/transactionid=[a-z0-9]+/, `transactionid=${transactionid}`);
    });
    let manuallyMessage: HttpMessage;
    test('数量減算の requestBody に書き換えて手動送信します', async () => {
      requestBody = requestBody.replace(/mode=down/, 'mode=down&mode_down=dummy');
      await zapClient.sendRequest(message.requestHeader + requestBody);
      manuallyMessage = await zapClient.getLastMessage(url);
    });
    let scanId: number;
    test('アクティブスキャンを実行します', async () => {
      expect(manuallyMessage.requestBody).toContain('mode=down');
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });

  test('購入手続きへ進みます', async () => {
    await page.goto(url);
    await page.click('input[name=confirm][alt=購入手続きへ]');
    await expect(page.locator('h2.title')).toContainText('お届け先の指定');
  });

  test.describe('購入手続きへ進むテストを実行します[POST] @attack', () => {
    let message: HttpMessage;
    test('履歴を取得します', async () => {
      message = await zapClient.getLastMessage(url);
      expect(message.requestHeader).toContain(`POST ${url}`);
      expect(message.responseHeader).toContain('HTTP/1.1 302 Found');
    });

    let scanId: number;
    test('アクティブスキャンを実行します', async () => {
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', message.requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });
});
