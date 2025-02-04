import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { AdminProductsProductPage } from '../../../pages/admin/products/product.page';
import { ADMIN_DIR } from '../../../config/default.config';

const url = `${ADMIN_DIR}/products/product.php`;
test.describe('商品登録画面のテストをします', () => {
  test('商品登録画面のテストをします', async ({ page, adminLoginPage }) => {
    const productPage = new AdminProductsProductPage(page);
    await productPage.goto();
    await productPage.fill();
    await productPage.fillSubComments();
    await productPage.fillRecommends();
    await productPage.gotoConfirm();
    await productPage.register();
    await expect(page.getByText('登録が完了致しました')).toBeVisible();
  });
});
