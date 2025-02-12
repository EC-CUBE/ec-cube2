import { test, expect } from '../../../fixtures/front_login/mypage_login.fixture';
import PlaywrightConfig from '../../../../playwright.config';
import { ProductsDetailPage } from '../../../pages/products/detail.page';
import { CartPage } from '../../../pages/cart.page';
import { ShoppingDelivPage } from '../../../pages/shopping/deliv.page';
import { ShoppingPaymentPage } from '../../../pages/shopping/payment.page';
import { faker } from '@faker-js/faker/locale/ja';

const url = `${PlaywrightConfig.use?.baseURL ?? ''}/mypage/index.php`;
test.describe.serial('購入履歴のテストをします', () => {

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('購入履歴のテストをします', async ( { mypageLoginPage, page }) => {
    await page.goto(url);
    await expect(page).toHaveTitle(/購入履歴一覧/);
    await expect(page.getByText('購入履歴はありません')).toBeVisible();

    await test.step('購入履歴を作成します', async () => {
      const productsDetailPage = new ProductsDetailPage(page);
      await productsDetailPage.goto(2);
      await productsDetailPage.cartIn(2);
      const cartPage = new CartPage(page);
      await cartPage.gotoNext();
      const shoppingDelivPage = new ShoppingDelivPage(page);
      await shoppingDelivPage.gotoNext();
      const shoppingPaymentPage = new ShoppingPaymentPage(page);
      await shoppingPaymentPage.selectPaymentMethod(faker.helpers.arrayElement(['郵便振替', '現金書留', '銀行振込', '代金引換']));
      await shoppingPaymentPage.selectDeliveryDate(faker.number.int({ min: 0, max: 5 }));
      await shoppingPaymentPage.selectDeliveryTime(faker.number.int({ min: 0, max: 2 }));
      await shoppingPaymentPage.fillMessage(faker.lorem.sentence());
      await shoppingPaymentPage.gotoNext();
      await page.click('[alt=ご注文完了ページへ]');
    });

    await test.step('購入履歴を確認します', async () => {
      await page.goto(url);
      await expect(page).toHaveTitle(/購入履歴一覧/);
      await expect(page.getByText('1件の購入履歴があります。')).toBeVisible();
      await page.getByRole('row').nth(1).getByText('詳細').click();
      await expect(page).toHaveTitle(/購入履歴詳細/);
    });

    await test.step('メールの送信履歴を確認します', async () => {
      const popupPromise = page.waitForEvent('popup');
      await page.getByRole('link', { name: 'ご注文ありがとうございます' }).click();
      const popup = await popupPromise;
      await expect(popup.getByText('ご注文ありがとうございます')).toBeVisible();
    });

    await test.step('再注文します', async () => {
      await page.getByAltText('この購入内容で再注文する').click();
      await expect(page).toHaveTitle(/現在のカゴの中/);
      await expect(page.locator('table[summary=商品情報]').getByText('おなべ')).toBeVisible();
    });
  });
});
