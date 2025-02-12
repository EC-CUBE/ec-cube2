import { test } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';

const url = `/${ADMIN_DIR}/basis/point.php`;
test.describe('ポイント設定のテストをします', () => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('ポイント設定のテストをします', async ( { adminLoginPage, page }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    const pointRate = await page.getByRole('row', { name: 'ポイント付与率' }).getByRole('textbox').inputValue();
    const welcomePoint = await page.getByRole('row', { name: '会員登録時付与ポイント' }).getByRole('textbox').inputValue();

    await page.getByRole('row', { name: 'ポイント付与率' }).getByRole('textbox').fill(String(faker.number.int({ min: 0, max: 999 })));
    await page.getByRole('row', { name: '会員登録時付与ポイント' }).getByRole('textbox').fill(String(faker.number.int({ min: 0, max: 99999999 })));

    await page.getByRole('link', { name: 'この内容で登録する' }).click();

    await test.step('後続のテストのために設定を戻します', async () => {
      await page.getByRole('row', { name: 'ポイント付与率' }).getByRole('textbox').fill(pointRate);
      await page.getByRole('row', { name: '会員登録時付与ポイント' }).getByRole('textbox').fill(welcomePoint);
      await page.getByRole('link', { name: 'この内容で登録する' }).click();
    });
  });
});
