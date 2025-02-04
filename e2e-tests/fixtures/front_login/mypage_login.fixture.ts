import { test as base, expect } from "@playwright/test";
import { MypageLoginPage } from "../../pages/mypage/login.page";
import { EntryPage } from "../../pages/entry/entry.page";
import { AdminLoginPage } from "../../pages/admin/login.page";
import { Mode, ContextType } from '../../utils/ZapClient';
import PlaywrightConfig from '../../../playwright.config';
import { ECCUBE_ADMIN_USER, ECCUBE_ADMIN_PASS, ADMIN_DIR } from "../../config/default.config";
import { faker } from '@faker-js/faker/locale/ja';
import { FakerUtils } from "../../utils/FakerUtils";

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

      // 購入フローのテストでポイント利用するため、ポイントを加算する
      const adminLoginPage = new AdminLoginPage(page);
      await page.goto(`/${ ADMIN_DIR }`);
      await adminLoginPage.login(ECCUBE_ADMIN_USER, ECCUBE_ADMIN_PASS);
      await page.goto(`/${ ADMIN_DIR }/customer/index.php`);
      await page.locator('input[name=search_email]').fill(email);
      await page.getByRole('link', { name: 'この条件で検索する' }).click();
      await page.getByRole('link', { name: '編集' }).click();
      await page.getByRole('row', { name: '所持ポイント' }).getByRole('textbox').fill(String(faker.number.int({ min: 0, max: 999999 })));
      await page.getByRole('link', { name: '確認ページへ' }).click();
      await page.getByRole('link', { name: 'この内容で登録する' }).click();

      await page.goto(`/`);
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
