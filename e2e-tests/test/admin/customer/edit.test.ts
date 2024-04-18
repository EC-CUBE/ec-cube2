import { test, expect } from '../../../fixtures/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ ADMIN_DIR }/customer/edit.php`;
test.describe('会員登録画面のテストをします', () => {

  test('会員登録画面のテストをします', async ( { loginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/会員登録/);
  });

  test('LC_Page_Admin_Customer_Edit_Ex クラスのテストをします @extends', async ( { loginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/カスタマイズ/);
  });
});
