import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';

const url = `/${ ADMIN_DIR }/basis/mail.php`;
test.describe('メール設定のテストをします', () => {
  test('メール設定のテストをします', async ( { adminLoginPage, page }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    await page.getByRole('row', { name: 'テンプレート' }).locator('select').selectOption({ label: '取り寄せ確認メール' });
    const title = faker.lorem.paragraph();
    await page.getByRole('row', { name: 'メールタイトル' }).getByRole('textbox').fill(title);
    const header = faker.lorem.sentences();
    const footer = faker.lorem.sentences();
    await page.getByRole('row', { name: 'ヘッダー' }).locator('textarea').fill(header);
    await page.getByRole('row', { name: 'フッター' }).locator('textarea').fill(footer);
    await page.getByRole('link', { name: 'この内容で登録する' }).click();

    await test.step('登録内容を確認します', async () => {
      await page.getByRole('row', { name: 'テンプレート' }).locator('select').selectOption({ label: '取り寄せ確認メール' });
      await expect(page.getByRole('row', { name: 'メールタイトル' }).getByRole('textbox')).toHaveValue(title);
      await expect(page.getByRole('row', { name: 'ヘッダー' }).locator('textarea')).toHaveValue(header);
      await expect(page.getByRole('row', { name: 'フッター' }).locator('textarea')).toHaveValue(footer);
    });
  });
});
