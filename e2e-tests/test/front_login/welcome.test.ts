import { test, expect } from '../../fixtures/front_login/mypage_login.fixture';

const url = '/index.php';

test.describe('トップページのテストをします', () => {

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('TOPページが正常に見られているかを確認します', async ({ mypageLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('#site_description')).toHaveText('EC-CUBE発!世界中を旅して見つけた立方体グルメを立方隊長が直送！');
    await expect(page.locator('#main_image')).toBeVisible();
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('body の class 名出力を確認します', async ({ mypageLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('body')).toHaveAttribute('class', 'LC_Page_Index');
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('システムエラーが出ていないのを確認します', async ({ mypageLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('.error')).toBeHidden();
  });

  test('ログアウトします', async ({ mypageLoginPage, page }) => {
    await page.goto(url);
    await mypageLoginPage.logout();
    await expect(mypageLoginPage.loginButton).toBeVisible();
  });

  test('LC_Page_Index_Ex クラスのテストをします @extends', async ( { page }) => {
    await page.goto(url);
    await expect(page).toHaveTitle(/カスタマイズ/);
  });
});
