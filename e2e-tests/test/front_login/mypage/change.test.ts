import { test, expect } from '../../../fixtures/mypage_login.fixture';
import PlaywrightConfig from '../../../../playwright.config';

const url = `${ PlaywrightConfig.use?.baseURL ?? '' }/mypage/change.php`;
test.describe.serial('会員登録内容変更画面のテストをします', () => {
  test('会員登録内容変更画面のテストをします', async ( { mypageLoginPage, page }) => {
    await page.goto(url);
    await expect(page).toHaveTitle(/会員登録内容変更/);
  });

  test('LC_Page_Mypage_Change_Ex クラスのテストをします @extends', async ( { page }) => {
    await page.goto(url);
    await expect(page).toHaveTitle(/カスタマイズ/);
  });
});
