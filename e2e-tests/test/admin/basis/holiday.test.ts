import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';
import type { Page } from '@playwright/test';

const url = `/${ADMIN_DIR}/basis/holiday.php`;
test.describe('定休日管理画面のテストをします', () => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('定休日管理画面のテストをします', async ( { adminLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/定休日管理/);
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('エラーハンドリングのテストをします', async ( { adminLoginPage, page }) => {
    await page.goto(url);
    await expect(page.getByRole('row', { name: 'タイトル' }).getByRole('textbox')).toBeEmpty();
    await expect(page.getByRole('row', { name: '日付' }).locator('select[name=month]')).toHaveValue('');
    await expect(page.getByRole('row', { name: '日付' }).locator('select[name=day]')).toHaveValue('');
    await page.getByRole('link', { name: 'この内容で登録する' }).click();
    await expect(page.getByText('タイトルが入力されていません。')).toBeVisible();
    await expect(page.getByText('妥当な日付ではありません。')).toBeVisible();
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('定休日の登録をします', async ( { adminLoginPage, page }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);

    const title = faker.lorem.sentence();
    await test.step('登録処理をします', async () => {
      await page.getByRole('row', { name: 'タイトル' }).getByRole('textbox').fill(title);
      const { month, day } = await findAvailableHolidayDate(page);
      await page.getByRole('row', { name: '日付' }).locator('select[name=month]').selectOption({ value: month });
      await page.getByRole('row', { name: '日付' }).locator('select[name=day]').selectOption({ value: day });

      await page.getByRole('link', { name: 'この内容で登録する' }).click();
      await expect(page.locator('table.list')).toContainText(title);
    });

    await test.step('編集をします', async () => {
      await page.goto(url);
      await page.getByRole('row', { name: title }).getByRole('link', { name: '編集' }).click();
      await page.getByRole('row', { name: 'タイトル' }).getByRole('textbox').fill(`${title}を編集`);
      const { month, day } = await findAvailableHolidayDate(page);
      await page.getByRole('row', { name: '日付' }).locator('select[name=month]').selectOption({ value: month });
      await page.getByRole('row', { name: '日付' }).locator('select[name=day]').selectOption({ value: day });

      await page.getByRole('link', { name: 'この内容で登録する' }).click();
      await expect(page.locator('table.list')).toContainText(`${title}を編集`);
    });

    await test.step('削除をします', async () => {
      await page.goto(url);
      await page.getByRole('row', { name: `${title}を編集` }).getByRole('link', { name: '削除' }).click();
      await expect(page.locator('table.list')).not.toContainText(`${title}を編集`);
    });
  });

  const findAvailableHolidayDate = async (page: Page): Promise<{ month: string; day: string }> => {
    for (;;) {
      const month = String(faker.number.int({ min: 1, max: 12 }));
      const day = String(faker.number.int({ min: 1, max: 28 }));
      const rowsText = await page.locator('table.list tr').allTextContents();
      const existsInList = rowsText.some(text => text.includes(`${month}月${day}日`) && !text.includes('編集中'));
      if (!existsInList) {
        return { month, day };
      }
    }
  };
});
