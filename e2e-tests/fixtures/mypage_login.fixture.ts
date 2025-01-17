import { test as base, expect } from "@playwright/test";
import { MypageLoginPage } from "../pages/mypage/login.page";
import { EntryPage } from "../pages/entry/entry.page";
import { Mode, ContextType } from '../utils/ZapClient';
import { ECCUBE_DEFAULT_USER, ECCUBE_DEFAULT_PASS } from "../config/default.config";
import PlaywrightConfig from '../../playwright.config';
import { FakerUtils } from "../utils/FakerUtils";

type MypageLoginFixtures = {
  mypageLoginPage: MypageLoginPage;
};

export const test = base.extend<MypageLoginFixtures>({
  mypageLoginPage: async ({ page }, use) => {
    const email = FakerUtils.createEmail();
    const password = FakerUtils.createPassword();
    const loginPage = new MypageLoginPage(page, email, password);
    if (PlaywrightConfig.use?.proxy === undefined) {
      const entryPage = new EntryPage(page, email, password);
      await entryPage.goto();
      await entryPage.agree();
      await entryPage.fill();
      await entryPage.confirm();
      await entryPage.register();
      await loginPage.goto();
      await loginPage.logout();
      await loginPage.login();
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
    use(loginPage);
  }
});

export { expect } from "@playwright/test";
