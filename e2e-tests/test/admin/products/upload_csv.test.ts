import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import path from 'path';

const url = `${ADMIN_DIR}/products/upload_csv.php`;
test.describe('商品登録CSV画面のテストをします', () => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('商品登録CSV画面のテストをします', async ({ page, adminLoginPage }) => {
    await page.goto(url);
    const fileChooserPromise = page.waitForEvent('filechooser');
    await page.locator('input[name=csv_file]').click();
    const fileChooser = await fileChooserPromise;
    await fileChooser.setFiles(path.join(__dirname, '..', '..', '..', '..', 'tests', 'new_product.csv'));
    await page.getByRole('link', { name: 'この内容で登録する' }).click();
    await expect(page.getByText('CSV登録を実行しました。')).toBeVisible();
    await expect(page.getByText('CSVアップロード商品')).toBeVisible();
  });
});
