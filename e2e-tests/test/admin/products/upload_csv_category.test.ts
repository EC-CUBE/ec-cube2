import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import path from 'path';

const url = `${ADMIN_DIR}/products/upload_csv_category.php`;
test.describe('カテゴリ登録CSV画面のテストをします', () => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('カテゴリ登録CSV画面のテストをします', async ({ page, adminLoginPage }) => {
    await page.goto(url);
    const fileChooserPromise = page.waitForEvent('filechooser');
    await page.locator('input[name=csv_file]').click();
    const fileChooser = await fileChooserPromise;
    await fileChooser.setFiles(path.join(__dirname, '..', '..', '..', '..', 'tests', 'new_category.csv'));
    await page.getByRole('link', { name: 'この内容で登録する' }).click();
    await expect(page.getByText('CSV登録を実行しました。')).toBeVisible();
    await expect(page.getByText('CSVアップロードカテゴリ')).toBeVisible();

    await test.step('カテゴリを削除します', async () => {
      await page.goto(`${ADMIN_DIR}/products/category.php`);
      await page.locator('id=categoryTable').getByRole('row', { name: 'CSVアップロードカテゴリ' }).getByRole('link', { name: '削除' }).click();
    });
  });
});
