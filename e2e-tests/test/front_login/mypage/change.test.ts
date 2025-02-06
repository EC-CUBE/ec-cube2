import { test, expect } from '../../../fixtures/front_login/mypage_login.fixture';
import { EntryPage } from '../../../pages/entry/entry.page';
import PlaywrightConfig from '../../../../playwright.config';

const url = `${ PlaywrightConfig.use?.baseURL ?? '' }/mypage/change.php`;
test.describe.serial('会員登録内容変更画面のテストをします', () => {

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('会員登録内容変更画面のテストをします', async ( { mypageLoginPage, page }) => {
    await page.goto(url);
    await expect(page).toHaveTitle(/会員登録内容変更/);
    const entryPage = new EntryPage(page);
    await entryPage.fill();
    const name01 = await entryPage.personalInputPage.name01.inputValue();
    const name02 = await entryPage.personalInputPage.name02.inputValue();
    await entryPage.confirm();
    await page.locator('id=complete').click();
    await expect(page).toHaveTitle(/会員登録内容変更\(完了ページ\)/);
    await expect(page.locator('id=header_login_area'), '名前が変更されているのを確認').toContainText(`${ name01 } ${ name02 }`);
  });

  test('LC_Page_Mypage_Change_Ex クラスのテストをします @extends', async ( { page }) => {
    await page.goto(url);
    await expect(page).toHaveTitle(/カスタマイズ/);
  });
});
