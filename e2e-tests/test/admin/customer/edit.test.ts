import { test, expect } from '@playwright/test';
import PlaywrightConfig from '../../../../playwright.config';
import { ZapClient, ContextType } from '../../../utils/ZapClient';
const zapClient = new ZapClient();

const url = `${PlaywrightConfig.use.baseURL}/admin/customer/edit.php`;
test.describe.serial('会員登録画面のテストをします', () => {
  test.beforeAll(async () => {
    await zapClient.startSession(ContextType.Admin, 'admin_customer_edit')
      .then(async () => expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy());
  });

  test('会員登録画面のテストをします', async ( { page }) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/会員登録/);
  });

  test('LC_Page_Admin_Customer_Edit_Ex クラスのテストをします @extends', async ( { page }) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/カスタマイズ/);
  });
});
