import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ ADMIN_DIR }/customer/edit.php`;
test.describe('会員登録画面のテストをします', () => {

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('会員登録画面のテストをします', async ( { adminLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/会員登録/);
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('LC_Page_Admin_Customer_Edit_Ex クラスのテストをします @extends', async ( { adminLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/カスタマイズ/);
  });
});
