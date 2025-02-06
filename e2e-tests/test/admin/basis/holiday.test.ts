import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';

const url = `/${ ADMIN_DIR }/basis/holiday.php`;
test.describe('定休日管理画面のテストをします', () => {

  test('定休日管理画面のテストをします', async ( { adminLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/定休日管理/);
  });

  test('エラーハンドリングのテストをします', async ( { adminLoginPage, page }) => {
    await page.goto(url);
    await expect(page.getByRole('row', { name: 'タイトル' }).getByRole('textbox')).toBeEmpty();
    await expect(page.getByRole('row', { name: '日付' }).locator('select[name=month]')).toHaveValue('');
    await expect(page.getByRole('row', { name: '日付' }).locator('select[name=day]')).toHaveValue('');
    await page.getByRole('link', { name: 'この内容で登録する' }).click();
    await expect(page.getByText('タイトルが入力されていません。')).toBeVisible();
    await expect(page.getByText('妥当な日付ではありません。')).toBeVisible();
  });

  test('定休日の登録をします', async ( { adminLoginPage, page }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);

    const title = faker.lorem.sentence();
    await test.step('登録処理をします', async () => {
      await page.getByRole('row', { name: 'タイトル' }).getByRole('textbox').fill(title);
      await page.getByRole('row', { name: '日付' }).locator('select[name=month]').selectOption({ value: String(faker.number.int({ min: 1, max: 12 })) });
      await page.getByRole('row', { name: '日付' }).locator('select[name=day]').selectOption({ value: String(faker.number.int({ min: 1, max: 28 })) });

      await page.getByRole('link', { name: 'この内容で登録する' }).click();
      await expect(page.locator('table.list')).toContainText(title);
    });
    await test.step('登録処理をします', async () => {
      await page.getByRole('row', { name: 'タイトル' }).getByRole('textbox').fill(title);
      await page.getByRole('row', { name: '日付' }).locator('select[name=month]').selectOption({ value: String(faker.number.int({ min: 1, max: 12 })) });
      await page.getByRole('row', { name: '日付' }).locator('select[name=day]').selectOption({ value: String(faker.number.int({ min: 1, max: 28 })) });

      await page.getByRole('link', { name: 'この内容で登録する' }).click();
      await expect(page.locator('table.list')).toContainText(title);
    });

    await test.step('編集をします', async () => {
      await page.goto(url);
      await page.getByRole('row', { name: title }).getByRole('link', { name: '編集' }).click();
      await page.getByRole('row', { name: 'タイトル' }).getByRole('textbox').fill(`${ title }を編集`);
      await page.getByRole('row', { name: '日付' }).locator('select[name=month]').selectOption({ value: String(faker.number.int({ min: 1, max: 12 })) });
      await page.getByRole('row', { name: '日付' }).locator('select[name=day]').selectOption({ value: String(faker.number.int({ min: 1, max: 28 })) });

      await page.getByRole('link', { name: 'この内容で登録する' }).click();
      await expect(page.locator('table.list')).toContainText(`${ title }を編集`);
    });

    await test.step('削除をします', async () => {
      await page.goto(url);
      await page.getByRole('row', { name: `${ title }を編集` }).getByRole('link', { name: '削除' }).click();
      await expect(page.locator('table.list')).not.toContainText(`${ title }を編集`);
    });
  });
});
