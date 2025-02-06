import { test, expect } from '../../../fixtures/admin/register_product.fixture.ts';
import { ADMIN_DIR } from '../../../config/default.config';

const url = `${ ADMIN_DIR }/products/product.php`;
test.describe('商品登録画面のテストをします', () => {
  test('商品登録画面のテストをします', async ({ page, adminProductsProductPage }) => {
    await test.step('register_product.fixture で商品を登録します', async () => {
      await expect(page.getByText('登録が完了致しました')).toBeVisible();
    });
  });
});
