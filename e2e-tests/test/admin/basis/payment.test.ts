import { test, expect } from '../../../fixtures/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';
import path from 'path';

const url = `/${ ADMIN_DIR }/basis/payment.php`;
test.describe.serial('支払方法設定のテストをします', () => {
  test('支払方法一覧のテストをします', async ( { adminLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/支払方法設定/);

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

  test('支払方法を登録するテストをします', async ( { adminLoginPage, page }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    await page.getByRole('link', { name: '支払方法を新規入力' }).click();
    const paymentName = faker.company.name();
    await page.getByRole('row', { name: '支払方法' }).getByRole('textbox').fill(paymentName);
    await page.getByRole('row', { name: '手数料' }).getByRole('textbox').fill(String(faker.number.int({ min: 100, max: 9999 })));
    const ruleMax = faker.number.int({ min: 1, max: 10 });
    const upperRule = faker.number.int({ min: ruleMax, max: 9999 });
    await page.getByRole('row', { name: '利用条件' }).getByRole('textbox').first().fill(String(ruleMax));
    await page.getByRole('row', { name: '利用条件' }).getByRole('textbox').nth(1).fill(String(upperRule));
    await page.getByRole('link', { name: 'この内容で登録する' }).click();

    await test.step('編集するテストをします', async () => {
      await page.getByRole('row', { name: paymentName }).getByRole('link', { name: '編集' }).click();
      await page.getByRole('row', { name: '手数料' }).getByRole('textbox').fill(String(faker.number.int({ min: 100, max: 9999 })));

      const fileChooserPromise = page.waitForEvent('filechooser');
      await page.locator('input[name=payment_image]').click();
      const fileChooser = await fileChooserPromise;
      await fileChooser.setFiles(path.join(__dirname, '..', '..', '..', 'fixtures', 'images', 'main.jpg'));
      await page.getByRole('link', { name: 'アップロード' }).click();
      await page.getByRole('link', { name: 'この内容で登録する' }).click();
    });

    await test.step('削除するテストをします', async () => {
      await page.getByRole('row', { name: paymentName }).getByRole('link', { name: '削除' }).click();
      await expect(page.getByRole('row', { name: paymentName })).toBeHidden();
    });
  });
});
