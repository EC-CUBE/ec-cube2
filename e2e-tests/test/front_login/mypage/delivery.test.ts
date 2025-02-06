import { test, expect } from '../../../fixtures/front_login/mypage_login.fixture';
import PlaywrightConfig from '../../../../playwright.config';
import { MypageDeliveryAddrPage } from '../../../pages/mypage/delivery_addr.page';

const url = `${ PlaywrightConfig.use?.baseURL ?? '' }/mypage/delivery.php`;
const DELIV_ADDR_MAX = 20;
test.describe.serial('お届け先追加のテストをします', () => {
  test('お届け先追加のテストをします', async ( { mypageLoginPage, page }) => {
    await page.goto(url);
    await expect(page).toHaveTitle(/お届け先追加･変更/);
    await expect(page.getByText('新しいお届け先はありません。')).toBeVisible();

    for (let i = 1; i <= DELIV_ADDR_MAX; i++) {
      await test.step('お届け先を追加します', async () => {
        const popupPromise = page.waitForEvent('popup');
        await page.getByAltText('新しいお届け先を追加').click();
        const popup = await popupPromise;
        const mypageDeliveryAddrPage = new MypageDeliveryAddrPage(popup);
        await mypageDeliveryAddrPage.fill();
        const name01 = await mypageDeliveryAddrPage.personalInputPage.name01.inputValue();
        const name02 = await mypageDeliveryAddrPage.personalInputPage.name02.inputValue();
        await mypageDeliveryAddrPage.register();
        await expect(page.getByRole('row', { name: `${ name01 } ${ name02 }` })).toBeVisible();
      });
    }

    await test.step('お届け先の最大件数を確認します', async () => {
      await expect(page.getByAltText('新しいお届け先を追加')).not.toBeVisible();
    });

    await test.step('お届け先の変更を確認します', async () => {
      const popupPromise = page.waitForEvent('popup');
      await page.locator('table[summary=お届け先]').getByRole('row').nth(1).getByRole('link', { name: '変更' }).click();
      const popup = await popupPromise;
      const mypageDeliveryAddrPage = new MypageDeliveryAddrPage(popup);
      await mypageDeliveryAddrPage.personalInputPage.fillName();
      const name01 = await mypageDeliveryAddrPage.personalInputPage.name01.inputValue();
      const name02 = await mypageDeliveryAddrPage.personalInputPage.name02.inputValue();
      await mypageDeliveryAddrPage.register();
      await expect(page.getByRole('row', { name: `${ name01 } ${ name02 }` })).toBeVisible();
    });

    page.on('dialog', dialog => dialog.accept());
    await test.step('お届け先の削除を確認します', async () => {
      const name = await page.locator('table[summary=お届け先]').getByRole('row').nth(1).getByRole('cell').nth(2).textContent() ?? '';
      await page.locator('table[summary=お届け先]').getByRole('row').nth(1).getByRole('link', { name: '削除' }).click();
      await expect(page.locator('table[summary=お届け先]').getByRole('row', { name: name })).not.toBeVisible();
    });
  });
});
