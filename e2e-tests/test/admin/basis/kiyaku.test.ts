import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';

const url = `/${ADMIN_DIR}/basis/kiyaku.php`;
test.describe.serial('会員規約設定のテストをします', () => {

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('ソートのテストをします', async ( { adminLoginPage, page } ) => {
    await page.goto(url);
    await test.step('下へ移動するテストをします', async () => {
      const row1Name = await page.locator('table.list').getByRole('row').nth(1).getByRole('cell').nth(1).textContent();
      await page.locator('table.list').getByRole('row').nth(1).getByRole('link', { name: '下へ' }).click();
      await expect(page.locator('table.list').getByRole('row').nth(2).getByRole('cell').nth(1)).toContainText(row1Name ?? '');
    });

    await test.step('上へ移動するテストをします', async () => {
      const row2Name = await page.locator('table.list').getByRole('row').nth(2).getByRole('cell').nth(1).textContent();
      await page.locator('table.list').getByRole('row').nth(2).getByRole('link', { name: '上へ' }).click();
      await expect(page.locator('table.list').getByRole('row').nth(1).getByRole('cell').nth(1)).toContainText(row2Name ?? '');
    });
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('会員規約設定のテストをします', async ( { adminLoginPage, page }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    const title = faker.lorem.paragraph().substring(0, 60).trim();
    await page.getByRole('row', { name: '規約タイトル' }).getByRole('textbox').fill(title);
    const kiyaku = faker.lorem.sentences();
    await page.getByRole('row', { name: '規約内容' }).locator('textarea').fill(kiyaku);
    await page.getByRole('link', { name: 'この内容で登録する' }).click();

    await test.step('会員規約の編集を確認します', async () => {
      await test.step('登録直後は編集中表示', async () => {
        await expect(page.locator('table.list').getByRole('row').nth(1).getByText('編集中')).toBeVisible();
      });

      await test.step('初期表示に戻って編集リンクをクリック', async () => {
        await page.goto(url);
        await page.locator('table.list').getByRole('row').nth(1).getByRole('link', { name: '編集' }).click();
      });

      await test.step('タイトルと内容がフォームに反映されていることを確認', async () => {
        await expect(page.getByRole('row', { name: '規約タイトル' }).getByRole('textbox')).toHaveValue(title);
        await expect(page.getByRole('row', { name: '規約内容' }).locator('textarea')).toHaveValue(kiyaku);
      });

      await test.step('内容を修正して確認', async () => {
        await page.getByRole('row', { name: '規約タイトル' }).getByRole('textbox').fill('タイトル');
        await page.getByRole('row', { name: '規約内容' }).locator('textarea').fill('内容');
        await page.getByRole('link', { name: 'この内容で登録する' }).click();

        await expect(page.getByRole('row', { name: '規約タイトル' }).getByRole('textbox')).toHaveValue('タイトル');
        await expect(page.getByRole('row', { name: '規約内容' }).locator('textarea')).toHaveValue('内容');
      });
    });

    await test.step('削除を確認します', async () => {
      await page.locator('table.list').getByRole('row').nth(1).getByRole('link', { name: '削除' }).click();
      await expect(page.locator('table.list').getByRole('row', { name: title })).toBeHidden();
    });
  });
});
