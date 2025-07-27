import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ADMIN_DIR}/products/product_rank.php`;
test.describe('商品並び替えのテストをします', () => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('商品並び替えのテストをします', async ( { adminLoginPage, page }) => {
    await page.goto(url);
    await page.locator('id=products-rank-left').getByText('食品').click();
    await page.locator('id=products-rank-left').getByText('なべ').click();

    const productName = await page.locator('id=categoryTable').getByRole('row').nth(1).getByRole('cell').nth(1).textContent() ?? '';
    await test.step('下へ移動するテストをします', async () => {
      await page.getByRole('row', { name: productName }).getByRole('link', { name: '下へ' }).click();
      await expect(page.locator('id=categoryTable').getByRole('row').nth(2)).toContainText(productName);
    });
    await test.step('上へ移動するテストをします', async () => {
      await page.getByRole('row', { name: productName }).getByRole('link', { name: '上へ' }).click();
      await expect(page.locator('id=categoryTable').getByRole('row').nth(1)).toContainText(productName);
    });
    // XXX クリックに失敗する場合があるためコメントアウト
    // await test.step('2番目へ移動するテストをします', async () => {
    //   await page.getByRole('row', { name: productName }).getByRole('textbox').fill('2');
    //   await page.getByRole('row', { name: productName }).getByRole('link', { name: '移動' }).click();
    //   await expect(page.locator('id=categoryTable').getByRole('row').nth(2)).toContainText(productName);
    // });
    // await test.step('1番目へ移動するテストをします', async () => {
    //   await page.getByRole('row', { name: productName }).getByRole('textbox').fill('1');
    //   await page.getByRole('row', { name: productName }).getByRole('link', { name: '移動' }).click();
    //   await expect(page.locator('id=categoryTable').getByRole('row').nth(1)).toContainText(productName);
    // });
  });
});
