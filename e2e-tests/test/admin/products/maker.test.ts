import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';

const url = `/${ADMIN_DIR}/products/maker.php`;
test.describe('メーカー設定のテストをします', () => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('メーカー設定のテストをします', async ( { adminLoginPage, page }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    const makerName = faker.lorem.word(2);
    await page.getByRole('row', { name: 'メーカー名' }).getByRole('textbox').fill(makerName);
    await page.getByRole('link', { name: 'この内容で登録する' }).click();
    await page.getByRole('row', { name: makerName }).getByRole('link', { name: '編集' }).click();
    await expect(page.getByRole('row', { name: 'メーカー名' }).getByRole('textbox')).toHaveValue(makerName);
    await page.getByRole('row', { name: makerName }).getByRole('link', { name: '削除' }).click();
  });
});
