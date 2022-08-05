import { test, expect } from '@playwright/test';
import PlaywrightConfig from '../../../../playwright.config';
import { ZapClient, ContextType } from '../../../utils/ZapClient';
const zapClient = new ZapClient();

const url = `${PlaywrightConfig.use.baseURL}/mypage/change.php`;
test.describe.serial('会員登録内容変更画面のテストをします', () => {
  test.beforeAll(async () => {
    await zapClient.startSession(ContextType.FrontLogin, 'front_login_mypage_change')
      .then(async () => expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy());
  });

  test('会員登録内容変更画面のテストをします', async ( { page }) => {
    await page.goto(url);
    await expect(page).toHaveTitle(/会員登録内容変更/);
  });

  test('LC_Page_Mypage_Change_Ex クラスのテストをします @extends', async ( { page }) => {
    await page.goto(url);
    await expect(page).toHaveTitle(/カスタマイズ/);
  });
});
