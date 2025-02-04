import { test as base, expect } from "@playwright/test";
import { AdminLoginPage } from "../../pages/admin/login.page";
import { Mode, ContextType } from '../../utils/ZapClient';
import { ECCUBE_ADMIN_USER, ECCUBE_ADMIN_PASS, ADMIN_DIR } from "../../config/default.config";
import PlaywrightConfig from '../../../playwright.config';

type AdminLoginFixtures = {
  adminLoginPage: AdminLoginPage;
};

export const test = base.extend<AdminLoginFixtures>({
  adminLoginPage: async ({ page }, use) => {
    const loginPage = new AdminLoginPage(page);
    if (PlaywrightConfig.use?.proxy === undefined) {
      await page.goto(`/${ ADMIN_DIR }`);
      await loginPage.login(ECCUBE_ADMIN_USER, ECCUBE_ADMIN_PASS);
    } else {
      const zapClient = loginPage.getZapClient();
      await zapClient.setMode(Mode.Protect);
      await zapClient.newSession('/zap/wrk/sessions/admin', true);
      await zapClient.importContext(ContextType.Admin);

      if (!await zapClient.isForcedUserModeEnabled()) {
        await zapClient.setForcedUserModeEnabled();
        expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy();
      }
      await page.goto(`/${ ADMIN_DIR }home.php`);
    }
    await use(loginPage);
  }
});

export { expect } from "@playwright/test";
