import { test, expect, request, APIRequestContext } from '@playwright/test';
import PlaywrightConfig from '../../../playwright.config';
import { ProductsDetailPage } from '../../pages/products/detail.page';
import { CartPage } from '../../pages/cart.page';
import { PersonalInputPage } from '../../pages/personal_input.page';
import { ShoppingPaymentPage } from '../../pages/shopping/payment.page';
import { faker } from '@faker-js/faker/locale/ja';
import { FakerUtils } from '../../utils/FakerUtils';

const url = '/products/list.php?category_id=3';

test.describe.serial('購入フロー(ゲスト)のテストをします', () => {
  let mailcatcher: APIRequestContext;
  test.beforeAll(async () => {
    mailcatcher = await request.newContext({
      baseURL: PlaywrightConfig.use?.proxy ? 'http://mailcatcher:1080' : 'http://localhost:1080',
      proxy: PlaywrightConfig.use?.proxy
    });
    await mailcatcher.delete('/messages');
  });

  test('商品を購入します', async ({ page }) => {
    await page.goto(url);
    await test.step('商品一覧を表示します', async () => {
      await expect(page.locator('form[name=product_form1] >> h3')).toContainText('アイスクリーム');
    });

    await test.step('商品をカートに入れます', async () => {
      const productsDetailPage = new ProductsDetailPage(page);
      await productsDetailPage.cartIn(
        2,
        faker.helpers.arrayElement([ '抹茶', 'チョコ', 'バニラ' ]),
        faker.helpers.arrayElement([ 'S', 'M', 'L' ])
      );
    });

    await test.step('カートの内容を確認します', async () => {
      await expect(page.locator('h2.title')).toContainText('現在のカゴの中');
      await expect(page.locator('table[summary=商品情報] >> tr >> nth=1')).toContainText('アイスクリーム');
      const cartPage = new CartPage(page);
      await cartPage.gotoNext();
    });

    await test.step('購入手続きへ進みます', async () => {
      await expect(page).toHaveTitle(/ログイン/);
      await page.click('[alt=購入手続きへ]');
    });

    const email = FakerUtils.createEmail();
    await test.step('お客様情報を入力します', async () => {
      const personalInputPage = new PersonalInputPage(page, email, url, 'order_');
      await personalInputPage.fillName();
      await personalInputPage.fillCompany();
      await personalInputPage.fillAddress();
      await personalInputPage.fillTel();
      await personalInputPage.fillFax();

      await personalInputPage.fillEmail();
      await personalInputPage.fillPersonalInfo();

      await page.click('text=お届け先を指定');

      const shoppingInputPage = new PersonalInputPage(page, email, url, 'shipping_');
      await shoppingInputPage.fillName();
      await shoppingInputPage.fillCompany();
      await shoppingInputPage.fillAddress();
      await shoppingInputPage.fillTel();
      await shoppingInputPage.fillFax();

      await page.click('[alt=上記のお届け先のみに送る]');
    });

    await test.step('お支払い方法・お届け時間の指定をします', async () => {
      const shoppingPaymentPage = new ShoppingPaymentPage(page);
      await shoppingPaymentPage.selectPaymentMethod(faker.helpers.arrayElement([ '郵便振替', '現金書留', '銀行振込', '代金引換' ]));
      await shoppingPaymentPage.selectDeliveryDate(faker.number.int({ min: 0, max: 5 }));
      await shoppingPaymentPage.selectDeliveryTime(faker.number.int({ min: 0, max: 2 }));
      await shoppingPaymentPage.fillMessage(faker.lorem.sentence());
      await page.fill('textarea[name=message]', 'お問い合わせ');
      await shoppingPaymentPage.gotoNext();
    });

    await test.step('入力内容の確認をします', async () => {
      await expect(page.locator('h2.title')).toContainText('入力内容のご確認');
      await page.click('[alt=ご注文完了ページへ]');
    });

    await test.step('注文完了を確認します', async () => {
      await expect(page.locator('h2.title')).toContainText('ご注文完了');

      const messages = await mailcatcher.get('/messages');
      expect((await messages.json()).length).toBe(1);
      expect(await messages.json()).toContainEqual(expect.objectContaining(
        {
          subject: expect.stringContaining('ご注文ありがとうございます'),
          recipients: expect.arrayContaining([ `<${ email }>` ])
        }
      ));
    });
  });
});
