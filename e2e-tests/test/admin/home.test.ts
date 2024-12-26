import { test, expect } from '../../fixtures/admin_login.fixture';
import { Page } from '@playwright/test';
import { ADMIN_DIR } from '../../config/default.config';

const url = `/${ ADMIN_DIR }/home.php`;

test.describe('管理画面Homeの確認をします', () => {
  let page: Page;

  test('システム情報を確認します', async ({ adminLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('.shop-info >> nth=0 >> tr >> nth=0 >> td')).toContainText('2.17');
  });

  test('LC_Page_Admin_Home_Ex クラスのテストをします @extends', async ({ adminLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('.shop-info >> nth=0 >> tr >> nth=1 >> td')).toContainText('PHP_VERSION_ID');
  });
});
