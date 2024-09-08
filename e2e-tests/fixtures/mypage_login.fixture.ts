import { test as base, expect } from "@playwright/test";
import { MypageLoginPage } from "../pages/mypage/login.page";
import { Mode, ContextType } from '../utils/ZapClient';
import { ECCUBE_DEFAULT_USER, ECCUBE_DEFAULT_PASS } from "../config/default.config";
import PlaywrightConfig from '../../playwright.config';

export const test = base.extend({
  page: async ({ page }, use) => {
    const loginPage = new MypageLoginPage(page);
    if (PlaywrightConfig.use?.proxy === undefined) {
      await loginPage.goto();
      await loginPage.login(ECCUBE_DEFAULT_USER, ECCUBE_DEFAULT_PASS);
    } else {
      const zapClient = loginPage.getZapClient();
      await zapClient.setMode(Mode.Protect);
      await zapClient.newSession('/zap/wrk/sessions/front_login', true);
      await zapClient.importContext(ContextType.FrontLogin);

      if (!await zapClient.isForcedUserModeEnabled()) {
        await zapClient.setForcedUserModeEnabled();
        expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy();
      }
      await page.goto(`/mypage/index.php`);
    }
    use(page);
  }
});

export { expect } from "@playwright/test";
