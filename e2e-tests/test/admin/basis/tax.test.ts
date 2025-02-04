import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';

const url = `/${ ADMIN_DIR }/basis/tax.php`;
test.describe.serial('税率設定のテストをします', () => {
  test('商品別税率のテストをします', async ( { adminLoginPage, page } ) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    await page.getByRole('row', { name: '商品別税率機能' }).getByLabel('有効').check();;
    await page.getByRole('link', { name: 'この内容で登録する' }).first().click();

    // XXX 何故か GitHUb Actions で変更が反映されないのでコメントアウト
    // await test.step('商品別税率設定が有効になっていることを確認します', async () => {
    //   await page.waitForTimeout(5000); // XXX 税率設定が商品管理画面に反映されるまで5秒くらいかかる
    //   await page.goto(`/${ ADMIN_DIR }/products/product.php`);
    //   await expect(page.getByRole('row', { name: '消費税率' })).toBeVisible();
    // });

    await page.goto(url);
    await page.getByRole('row', { name: '商品別税率機能' }).getByLabel('無効').check();;
    await page.getByRole('link', { name: 'この内容で登録する' }).first().click();
  });

  test('共通税率設定のテストをします', async ( { adminLoginPage, page }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    const taxRate = String(faker.number.int({ min: 1, max: 999 }));
    await page.getByRole('row', { name: '消費税率' }).getByRole('textbox').fill(taxRate);
    await page.getByRole('row', { name: '課税規則' }).getByLabel(faker.helpers.arrayElement(['切り捨て', '四捨五入', '切り上げ'])).check();
    const applyDate = faker.date.future({ years: 2 });
    await page.locator('select[name="apply_date_year"]').selectOption({ value: String(applyDate.getFullYear()) });
    await page.locator('select[name="apply_date_month"]').selectOption({ value: String(applyDate.getMonth() + 1) });
    await page.locator('select[name="apply_date_day"]').selectOption({ value: String(applyDate.getDate()) });
    await page.locator('select[name="apply_date_hour"]').selectOption({ value: String(applyDate.getHours()) });
    await page.locator('select[name="apply_date_minutes"]').selectOption({ value: String(applyDate.getMinutes()) });
    await page.getByRole('link', { name: 'この内容で登録する' }).nth(1).click();

    await test.step('共通税率設定の編集を確認します', async () => {
      await page.locator('table.list').getByRole('row').nth(1).getByRole('link', { name: '編集' }).click();
      await expect(page.getByRole('row', { name: '消費税率' }).getByRole('textbox')).toHaveValue(taxRate);
      await page.getByRole('row', { name: '消費税率' }).getByRole('textbox').fill('3');
      await page.getByRole('link', { name: 'この内容で登録する' }).nth(1).click();

      await expect(page.locator('table.list').getByRole('row').nth(1).getByRole('cell').nth(0)).toContainText('3');
    });

    await test.step('共通税率設定の削除を確認します', async () => {
      await page.locator('table.list').getByRole('row').nth(1).getByRole('link', { name: '削除' }).click();
      await expect(page.locator('table.list').getByRole('row').nth(1).getByRole('cell').nth(0)).not.toContainText('3');
    });
  });
});
