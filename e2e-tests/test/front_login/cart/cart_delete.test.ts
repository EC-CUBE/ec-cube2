import { test, expect, chromium, Page } from '@playwright/test';
import PlaywrightConfig from '../../../../playwright.config';
import { ZapClient, Mode, ContextType, Risk, HttpMessage } from '../../../utils/ZapClient';
import { intervalRepeater } from '../../../utils/Progress';
const zapClient = new ZapClient();

const url = `${PlaywrightConfig.use.baseURL}/cart/index.php`;

// zap/patches/0009-cart_delete.patch を適用する必要があります
test.describe.serial('カートページのテストをします', () => {
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

  test('カートを削除します', async () => {
    page.on('dialog', dialog => dialog.accept());
    await page.reload();
    await page.click('table[summary=商品情報] >> tr >> nth=1 >> td >> nth=0 >> text=削除');
  });

  test.describe('カート削除のテストを実行します[POST] @attack', () => {

    let message: HttpMessage;
    test('履歴を取得します', async () => {
      const result = await zapClient.getMessages(url, await zapClient.getNumberOfMessages(url) - 1, 1);
      message = result.pop();
    });

    let scanId: number;
    test('アクティブスキャンを実行します', async () => {
      expect(message.requestBody).toContain('mode=delete');
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', message.requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);
    });

    test('結果を確認します', async () => {
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });
});
