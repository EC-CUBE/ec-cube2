import { test, expect } from '../../fixtures/admin/admin_login.fixture';

test.describe('管理画面に正常にログインできるか確認します', () => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('ログインしたのを確認します', async ( { adminLoginPage, page } ) => {
    await expect(page.locator('#site-check')).toContainText('ログイン : 管理者 様');
  });
});
