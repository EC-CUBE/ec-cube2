import { test, expect } from '../../fixtures/admin_login.fixture';
import { Page } from '@playwright/test';

test.describe('管理画面に正常にログインできるか確認します', () => {
  let page: Page;

  test('ログインしたのを確認します', async ( { loginPage, page }) => {
    await expect(page.locator('#site-check')).toContainText('ログイン : 管理者 様');
  });
});
