import { test, expect } from '../../fixtures/front_login/mypage_login.fixture';
import { request, APIRequestContext } from '@playwright/test';
import PlaywrightConfig from '../../../playwright.config';
import { ProductsDetailPage } from '../../pages/products/detail.page';
import { CartPage } from '../../pages/cart.page';
import { ShoppingDelivPage } from '../../pages/shopping/deliv.page';
import { ShoppingMultiplePage } from '../../pages/shopping/multiple.page';
import { ShoppingPaymentPage } from '../../pages/shopping/payment.page';
import { MypageDeliveryAddrPage } from '../../pages/mypage/delivery_addr.page';
import { faker } from '@faker-js/faker/locale/ja';

const url = '/products/detail.php?product_id=1';

test.describe.serial('購入フロー(ログイン)のテストをします', () => {
  let mailcatcher: APIRequestContext;
  test.beforeAll(async () => {
    mailcatcher = await request.newContext({
      baseURL: PlaywrightConfig.use?.proxy ? 'http://mailcatcher:1080' : 'http://localhost:1080',
      proxy: PlaywrightConfig.use?.proxy
    });
    await mailcatcher.delete('/messages');
  });

  test('商品を購入します', async ({ mypageLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('#detailrightbloc > h2')).toContainText('アイスクリーム');

    await test.step('商品をカートに入れます', async () => {
      const productsDetailPage = new ProductsDetailPage(page);
      await productsDetailPage.cartIn(
        2,
        faker.helpers.arrayElement(['抹茶', 'チョコ', 'バニラ']),
        faker.helpers.arrayElement(['S', 'M', 'L'])
      );
    });

    await test.step('カートの中身を確認します', async () => {
      await expect(page.locator('h2.title')).toContainText('現在のカゴの中');
      await expect(page.locator('table[summary=商品情報] >> tr >> nth=1')).toContainText('アイスクリーム');
      const cartPage = new CartPage(page);
      await cartPage.gotoNext();
    });

    await test.step('お届け先の指定をします', async () => {
      await expect(page.locator('h2.title')).toContainText('お届け先の指定');
      const shoppingDelivPage = new ShoppingDelivPage(page);
      const popupPromise = page.waitForEvent('popup');
      await shoppingDelivPage.gotoAddNewDeliveryAddress();
      const popup = await popupPromise;
      const mypageDeliveryAddrPage = new MypageDeliveryAddrPage(popup);
      await mypageDeliveryAddrPage.fill();
      await mypageDeliveryAddrPage.register();

      await shoppingDelivPage.gotoSendToMultiple();
      const shoppingMultiplePage = new ShoppingMultiplePage(page);
      await shoppingMultiplePage.assignDeliveryAddress(0, 1);
      await shoppingMultiplePage.assignDeliveryAddress(1, 2);
      await shoppingMultiplePage.gotoNext();
    });

    await test.step('お支払い方法・お届け時間の指定をします', async () => {
      const shoppingPaymentPage = new ShoppingPaymentPage(page);
      await shoppingPaymentPage.selectPaymentMethod(faker.helpers.arrayElement(['郵便振替', '現金書留', '銀行振込', '代金引換']));
      await shoppingPaymentPage.selectDeliveryDate(faker.number.int({ min: 0, max: 5 }));
      await shoppingPaymentPage.selectDeliveryTime(faker.number.int({ min: 0, max: 2 }));
      await shoppingPaymentPage.chooseToUsePoint();
      await shoppingPaymentPage.fillUsePoint(1);
      await shoppingPaymentPage.fillMessage(faker.lorem.sentence());
      await shoppingPaymentPage.gotoNext();
    });

    await test.step('入力内容の確認をします', async () => {
      await expect(page.locator('h2.title')).toContainText('入力内容のご確認');
      await page.click('[alt=ご注文完了ページへ]');
    });

    await test.step('ご注文完了画面を確認します', async () => {
      await expect(page.locator('h2.title')).toContainText('ご注文完了');
    });

    await test.step('メールが送信されていることを確認します', async () => {
      const messages = await mailcatcher.get('/messages');
      expect(await messages.json()).toContainEqual(expect.objectContaining(
        {
          subject: expect.stringContaining('ご注文ありがとうございます'),
          recipients: expect.arrayContaining([`<${mypageLoginPage.email}>`])
        }
      ));
    });
  });
});
