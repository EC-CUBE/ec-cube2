import { test, expect } from '../../../fixtures/admin/register_product.fixture.ts';
import { ADMIN_DIR } from '../../../config/default.config';

const url = `${ADMIN_DIR}/products/index.php`;
test.describe('商品マスターのテストをします', () => {
  test('商品名で検索します', async ({ page, adminProductsProductPage }) => {
    await page.goto(url);
    await page.getByRole('row', { name: '商品名' }).getByRole('textbox').nth(1).fill(adminProductsProductPage.productName);
    await page.getByRole('link', { name: 'この条件で検索する' }).click();
    await expect(page.locator('table.list').getByText(adminProductsProductPage.productName)).toBeVisible();
  });
});
