import { test, expect, chromium, Page } from '@playwright/test';
import { ADMIN_DIR } from '../../config/default.config';
import { ZapClient, ContextType } from '../../utils/ZapClient';
const zapClient = new ZapClient();

const url = `/${ADMIN_DIR}/home.php`;

test.describe.serial('管理画面Homeの確認をします', () => {
  let page: Page;
  test.beforeAll(async () => {
    await zapClient.startSession(ContextType.Admin, 'admin_home')
      .then(async () => expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy());
    const browser = await chromium.launch();

    page = await browser.newPage();
    await page.goto(url);
  });

  test('システム情報を確認します', async ({ page }) => {
    await page.goto(url);
    await expect(page.locator('.shop-info >> nth=0 >> tr >> nth=0 >> td')).toContainText('2.17');
  });

  test('LC_Page_Admin_Home_Ex クラスのテストをします @extends', async ({ page }) => {
    await page.goto(url);
    await expect(page.locator('.shop-info >> nth=0 >> tr >> nth=1 >> td')).toContainText('PHP_VERSION_ID');
  });
});
