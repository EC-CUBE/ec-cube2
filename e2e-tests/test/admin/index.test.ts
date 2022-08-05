import { test, expect, chromium, Page } from '@playwright/test';
import { ADMIN_DIR } from '../../config/default.config';

const url = `/${ADMIN_DIR}index.php`;

test.describe.serial('管理画面に正常にログインできるか確認します', () => {
  let page: Page;
  test.beforeAll(async () => {
    const browser = await chromium.launch();

    page = await browser.newPage();
    await page.goto(url);
  });

  test('ログイン画面を確認します', async () => {
    await expect(page.locator('#login-form')).toContainText(/LOGIN/);
  });

  test('ログインします', async () => {
    await page.fill('input[name=login_id]', 'admin');
    await page.fill('input[name=password]', 'password');
    await page.click('text=LOGIN');
  });

  test('ログインしたのを確認します', async () => {
    await expect(page.locator('#site-check')).toContainText('ログイン : 管理者 様');
  });
});
