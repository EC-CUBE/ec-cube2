import PlaywrightConfig from '../../../../playwright.config';
import { Risk } from '../../../utils/ZapClient';
import { intervalRepeater } from '../../../utils/Progress';

// 商品をカートに入れて購入手続きへ進むフィクスチャ
import { test, expect } from '../../../fixtures/cartin.fixture';

const url = `${ PlaywrightConfig.use?.baseURL ?? '' }/cart/index.php`;
import { CartPage } from '../../../pages/cart.page';

// zap/patches/0009-cart_delete.patch を適用する必要があります
test.describe.serial('カートページのテストをします', () => {
  const detailURL = `${ PlaywrightConfig.use?.baseURL ?? '' }/products/detail.php?product_id=1`;
  test('カートの削除をテストします', async ( { cartLoginPage, page } ) => {
    await page.goto(detailURL);
    await expect(page.locator('#detailrightbloc > h2')).toContainText('アイスクリーム');

    // 商品をカートに入れます
    await page.selectOption('select[name=classcategory_id1]', { label: '抹茶' });
    await page.selectOption('select[name=classcategory_id2]', { label: 'S' });
    await page.fill('input[name=quantity]', '2');
    await page.click('[alt=カゴに入れる]');

    // カートの内容を確認します
    await expect(page.locator('h2.title')).toContainText('現在のカゴの中');
    await expect(page.locator('table[summary=商品情報] >> tr >> nth=1')).toContainText('アイスクリーム');

    // カートを削除します
    page.on('dialog', dialog => dialog.accept());
    await page.reload();
    await page.click('table[summary=商品情報] >> tr >> nth=1 >> td >> nth=0 >> text=削除');
  });

  test.describe('カート削除のテストを実行します[POST] @attack', () => {

    test('履歴を取得します', async ( { page } ) => {
      const cartPage = new CartPage(page);
      const zapClient = cartPage.getZapClient();
      const result = await zapClient.getMessages(url, await zapClient.getNumberOfMessages(url) - 1, 1);
      const message = result.pop();

      let scanId: number;

      // アクティブスキャンを実行します
      expect(message?.requestBody).toContain('mode=delete');
      scanId = await zapClient.activeScanAsUser(url, 2, 110, false, null, 'POST', message?.requestBody);
      await intervalRepeater(async () => await zapClient.getActiveScanStatus(scanId), 5000, page);

      // 結果を確認します
      await zapClient.getAlerts(url, 0, 1, Risk.High)
        .then(alerts => expect(alerts).toEqual([]));
    });
  });
});
