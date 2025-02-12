import { test, expect } from '../../../fixtures/front_login/mypage_login.fixture';
import PlaywrightConfig from '../../../../playwright.config';

const url = `${PlaywrightConfig.use?.baseURL ?? ''}/mypage/refusal.php`;
test.describe.serial('退会手続きのテストをします', () => {

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('退会手続きのテストをします', async ( { mypageLoginPage, page }) => {
    await page.goto(url);
    await page.getByAltText('会員退会を行う').click();
    await page.getByAltText('はい、退会します').click();
    await expect(page.getByRole('heading', { name: '退会手続き(完了ページ)' })).toBeVisible();

    await test.step('退会しログアウトしたことを確認します', async () => {
      await page.goto('/');
      await expect(page.locator('id=login_form').getByAltText('ログイン')).toBeVisible();
    });
  });
});
