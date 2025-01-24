import { test, expect } from '../../../fixtures/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';

const url = `/${ ADMIN_DIR }/basis/delivery.php`;
test.describe('配送方法設定のテストをします', () => {
  test('配送方法一覧のテストをします', async ( { adminLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/配送方法設定/);

    await test.step('下へ移動するテストをします', async () => {
      const row1Name = await page.getByRole('row').nth(1).getByRole('cell').nth(1).textContent();
      await page.getByRole('row').nth(1).getByRole('link', { name: '下へ' }).click();
      await expect(page.getByRole('row').nth(2).getByRole('cell').nth(1)).toContainText(row1Name ?? '');
    });

    await test.step('上へ移動するテストをします', async () => {
      const row2Name = await page.getByRole('row').nth(2).getByRole('cell').nth(1).textContent();
      await page.getByRole('row').nth(2).getByRole('link', { name: '上へ' }).click();
      await expect(page.getByRole('row').nth(1).getByRole('cell').nth(1)).toContainText(row2Name ?? '');
    });
  });

  test('配送方法を登録するテストをします', async ( { adminLoginPage, page }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    await page.getByRole('link', { name: '配送方法・配送料を新規入力' }).click();
    const deliveryName = faker.company.name();
    await page.getByRole('row', { name: '配送業者名' }).getByRole('textbox').fill(deliveryName);
    await page.getByRole('row', { name: '名称' }).getByRole('textbox').fill(faker.company.name());
    await page.getByRole('row', { name: '説明' }).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: '伝票No.URL' }).getByRole('textbox').fill(faker.internet.url());
    await page.getByRole('row', { name: 'お届け時間1' }).getByRole('cell').nth(1).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間2' }).getByRole('cell').nth(3).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間3' }).getByRole('cell').nth(1).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間4' }).getByRole('cell').nth(3).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間5' }).getByRole('cell').nth(1).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間6' }).getByRole('cell').nth(3).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間7' }).getByRole('cell').nth(1).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間8' }).getByRole('cell').nth(3).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間9' }).getByRole('cell').nth(1).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間10' }).getByRole('cell').nth(3).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間11' }).getByRole('cell').nth(1).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間12' }).getByRole('cell').nth(3).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間13' }).getByRole('cell').nth(1).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間14' }).getByRole('cell').nth(3).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間15' }).getByRole('cell').nth(1).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'お届け時間16' }).getByRole('cell').nth(3).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: '商品種別' }).getByLabel('通常商品').check();
    await page.getByRole('row', { name: '支払方法' }).getByLabel(faker.helpers.arrayElement(['郵便振替', '現金書留', '銀行振込', '代金引換'])).check();
    await page.locator('input[name=fee_all]').fill(String(faker.number.int({ min: 100, max: 9999 })));
    await page.getByRole('link', { name: '反映' }).click();
    await page.getByRole('link', { name: 'この内容で登録する' }).click();
    await page.getByRole('link', { name: '前のページに戻る' }).click();

    await test.step('編集するテストをします', async () => {
      await page.getByRole('row', { name: deliveryName }).getByRole('link', { name: '編集' }).click();
      await page.getByRole('row', { name: 'お届け時間16' }).getByRole('cell').nth(3).getByRole('textbox').fill('午前中');
      await page.getByRole('link', { name: 'この内容で登録する' }).click();
      await page.getByRole('link', { name: '前のページに戻る' }).click();
    });

    await test.step('削除するテストをします', async () => {
      await page.getByRole('row', { name: deliveryName }).getByRole('link', { name: '削除' }).click();
      await expect(page.getByRole('row', { name: deliveryName })).toBeHidden();
    });
  });
});
