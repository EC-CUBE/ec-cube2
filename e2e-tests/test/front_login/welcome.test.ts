import { test, expect } from '../../fixtures/front_login/mypage_login.fixture';

const url = '/index.php';

const gotoWithRetry = async (page: any, url: string) => {
  try {
    await page.goto(url, { waitUntil: 'commit' });
  } catch (error) {
    // ERR_ABORTED エラーが発生しても、ページが実際にレンダリングされていれば続行
    if (error instanceof Error && error.message.includes('ERR_ABORTED')) {
      // ページが読み込まれているか確認するため、短時間待機
      await page.waitForLoadState('domcontentloaded', { timeout: 5000 }).catch(() => {});
    } else {
      throw error;
    }
  }
};

test.describe('トップページのテストをします', () => {

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('TOPページが正常に見られているかを確認します', async ({ mypageLoginPage, page }) => {
    await gotoWithRetry(page, url);
    await expect(page.locator('#site_description')).toHaveText('EC-CUBE発!世界中を旅して見つけた立方体グルメを立方隊長が直送！');
    await expect(page.locator('#main_image')).toBeVisible();
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('body の class 名出力を確認します', async ({ mypageLoginPage, page }) => {
    await gotoWithRetry(page, url);
    await expect(page.locator('body')).toHaveAttribute('class', 'LC_Page_Index');
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('システムエラーが出ていないのを確認します', async ({ mypageLoginPage, page }) => {
    await gotoWithRetry(page, url);
    await expect(page.locator('.error')).toBeHidden();
  });

  test('ログアウトします', async ({ mypageLoginPage, page }) => {
    await gotoWithRetry(page, url);
    await mypageLoginPage.logout();
    await expect(mypageLoginPage.loginButton).toBeVisible();
  });

  test('LC_Page_Index_Ex クラスのテストをします @extends', async ( { page }) => {
    await gotoWithRetry(page, url);
    await expect(page).toHaveTitle(/カスタマイズ/);
  });
});
