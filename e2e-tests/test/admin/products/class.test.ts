import { test, expect } from '../../../fixtures/admin/register_product.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';

const url = `${ADMIN_DIR}/products/class.php`;
test.describe('規格管理画面のテストをします', () => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('規格管理画面のテストをします', async ({ page, adminProductsProductPage }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    const className = faker.lorem.word(2);
    await test.step('規格名を登録します', async () => {
      await page.getByRole('row', { name: '規格名' }).first().getByRole('textbox').fill(className);
      await page.getByRole('link', { name: 'この内容で登録する' }).click();
    });

    await test.step('下へ移動するテストをします', async () => {
      await page.getByRole('row', { name: className }).getByRole('link', { name: '下へ' }).click();
      await expect(page.locator('table.list').getByRole('row').nth(2).getByRole('cell').nth(0)).toContainText(className ?? '');
    });

    await test.step('上へ移動するテストをします', async () => {
      await page.getByRole('row', { name: className }).getByRole('link', { name: '上へ' }).click();
      await expect(page.locator('table.list').getByRole('row').nth(1).getByRole('cell').nth(0)).toContainText(className ?? '');
    });

    await test.step('規格分類登録をします', async () => {
      const classCategoryName1 = faker.lorem.word(2);
      const classCategoryName2 = faker.lorem.word(2);
      await page.getByRole('row', { name: className }).getByRole('link', { name: '分類登録' }).click();
      await page.getByRole('row', { name: '分類名' }).first().getByRole('textbox').fill(classCategoryName1);
      await page.getByRole('link', { name: 'この内容で登録する' }).click();
      await page.getByRole('row', { name: '分類名' }).first().getByRole('textbox').fill(classCategoryName2);
      await page.getByRole('link', { name: 'この内容で登録する' }).click();

      await test.step('下へ移動するテストをします', async () => {
        await page.getByRole('row', { name: classCategoryName2 }).getByRole('link', { name: '下へ' }).click();
        await expect(page.locator('table.list').getByRole('row').nth(2).getByRole('cell').nth(0)).toContainText(classCategoryName2 ?? '');
      });

      await test.step('上へ移動するテストをします', async () => {
        await page.getByRole('row', { name: classCategoryName2 }).getByRole('link', { name: '上へ' }).click();
        await expect(page.locator('table.list').getByRole('row').nth(1).getByRole('cell').nth(0)).toContainText(classCategoryName2 ?? '');
      });

      await test.step('規格分類を編集をします', async () => {
        await page.getByRole('row', { name: classCategoryName1 }).getByRole('link', { name: '編集' }).click();
        await expect(page.getByRole('row', { name: '分類名' }).first().getByRole('textbox')).toHaveValue(classCategoryName1);
        await page.getByRole('row', { name: '分類名' }).first().getByRole('textbox').fill(`${classCategoryName1}を編集`);
        await page.getByRole('link', { name: 'この内容で登録する' }).click();
      });

      await test.step('規格分類を削除します', async () => {
        await page.getByRole('row', { name: `${classCategoryName1}を編集` }).getByRole('link', { name: '削除' }).click();
        await expect(page.getByRole('row', { name: `${classCategoryName1}を編集` })).toBeHidden();

        await page.getByRole('row', { name: classCategoryName2 }).getByRole('link', { name: '削除' }).click();
        await expect(page.getByRole('row', { name: classCategoryName2 })).toBeHidden();
      });

      await page.getByRole('link', { name: '規格一覧に戻る' }).click();

      await test.step('規格を削除します', async () => {
        await page.getByRole('row', { name: className }).getByRole('link', { name: '削除' }).click();
        await expect(page.getByRole('row', { name: className })).toBeHidden();
      });
    });
  });
});
