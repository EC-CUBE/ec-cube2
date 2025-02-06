import { test, expect } from '../../../fixtures/admin/register_product.fixture.ts';

test.describe('商品登録画面のテストをします', () => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('商品登録画面のテストをします', async ({ page, adminProductsProductPage }) => {
    await test.step('register_product.fixture で商品を登録します', async () => {
      await expect(page.getByText('登録が完了致しました')).toBeVisible();
    });
  });
});
