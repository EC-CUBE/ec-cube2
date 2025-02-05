import { test, expect } from '../../../fixtures/admin/register_product.fixture.ts';
import { ADMIN_DIR } from '../../../config/default.config';
import fs from 'fs/promises';
import iconv from 'iconv-lite';

const url = `${ADMIN_DIR}/products/index.php`;
test.describe('商品マスターのテストをします', () => {
  test('商品名で検索します', async ({ page, adminProductsProductPage }) => {
    await page.goto(url);
    await page.getByRole('row', { name: '商品名' }).getByRole('textbox').nth(1).fill(adminProductsProductPage.productName);
    await page.getByRole('link', { name: 'この条件で検索する' }).click();
    await expect(page.locator('table.list').getByText(adminProductsProductPage.productName)).toBeVisible();
  });

  test('商品編集のテストをします', async ({ page, adminProductsProductPage }) => {
    await page.goto(url);
    await page.getByRole('row', { name: '商品名' }).getByRole('textbox').nth(1).fill(adminProductsProductPage.productName);
    await page.getByRole('link', { name: 'この条件で検索する' }).click();
    await page.locator('table.list').getByRole('row').nth(2).getByRole('link', { name: '編集' }).click();
    await expect(page.getByRole('row', { name: '商品名' }).locator('input[name=name]')).toHaveValue(adminProductsProductPage.productName);

    await test.step('商品名を編集します', async () => {
      await adminProductsProductPage.name.first().fill(`${adminProductsProductPage.productName}を編集`);
      await adminProductsProductPage.gotoConfirm();
      await adminProductsProductPage.register();
      await expect(page.getByText('登録が完了致しました')).toBeVisible();
      await page.getByRole('link', { name: '検索結果へ戻る' }).click();
    });
    await expect(page.locator('table.list').getByText(`${adminProductsProductPage.productName}を編集`)).toBeVisible();
  });

  test('商品確認のテストをします', async ({ page, adminProductsProductPage }) => {
    await page.goto(url);
    await page.getByRole('row', { name: '商品名' }).getByRole('textbox').nth(1).fill(adminProductsProductPage.productName);
    await page.getByRole('link', { name: 'この条件で検索する' }).click();
    const popupPromise = page.waitForEvent('popup');
    await page.locator('table.list').getByRole('row').nth(2).getByRole('link', { name: '確認' }).click();
    const popup = await popupPromise;
    await expect(popup.getByRole('heading', { name: adminProductsProductPage.productName })).toBeVisible();
  });

  test('商品削除のテストをします', async ({ page, adminProductsProductPage }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    await page.getByRole('row', { name: '商品名' }).getByRole('textbox').nth(1).fill(adminProductsProductPage.productName);
    await page.getByRole('link', { name: 'この条件で検索する' }).click();
    await page.locator('table.list').getByRole('row').nth(2).getByRole('link', { name: '削除' }).click();
    await expect(page.locator('table.list').getByText(adminProductsProductPage.productName)).not.toBeVisible();
  });

  test('商品複製のテストをします', async ({ page, adminProductsProductPage }) => {
    await page.goto(url);
    await page.getByRole('row', { name: '商品名' }).getByRole('textbox').nth(1).fill(adminProductsProductPage.productName);
    await page.getByRole('link', { name: 'この条件で検索する' }).click();
    await page.locator('table.list').getByRole('row').nth(2).getByRole('link', { name: '複製' }).click();
    await expect(page.getByRole('row', { name: '商品ID' }).getByRole('cell').nth(1), '商品IDが空になっていることを確認します').toHaveText('');
    await expect(page.getByRole('row', { name: '商品名' }).locator('input[name=name]')).toHaveValue(adminProductsProductPage.productName);

    await test.step('商品名を編集します', async () => {
      await adminProductsProductPage.name.first().fill(`${adminProductsProductPage.productName}のコピー`);
      await adminProductsProductPage.gotoConfirm();
      await adminProductsProductPage.register();
      await expect(page.getByText('登録が完了致しました')).toBeVisible();
      await page.getByRole('link', { name: '検索結果へ戻る' }).click();
    });
    await expect(page.locator('table.list').getByText(`${adminProductsProductPage.productName}のコピー`)).toBeVisible();
  });

  test('商品規格登録のテストをします', async ({ page, adminProductsProductPage }) => {
    await page.goto(url);
    await page.getByRole('row', { name: '商品名' }).getByRole('textbox').nth(1).fill(adminProductsProductPage.productName);
    await page.getByRole('link', { name: 'この条件で検索する' }).click();
    await page.locator('table.list').getByRole('row').nth(2).getByRole('link', { name: '規格' }).click();

    await test.step('規格の組み合わせを表示します', async () => {
      await page.getByRole('row', { name: '規格1' }).locator('select[name=class_id1]').selectOption({ label: '大きさ' });
      await page.getByRole('row', { name: '規格2' }).locator('select[name=class_id2]').selectOption({ label: '味' });
      await page.getByRole('link', { name: '表示する' }).click();
      await page.locator('id=allCheck').check();
    });

    await page.getByRole('link', { name: '確認ページへ' }).click();
    await expect(page.getByRole('heading', { name: '確認' })).toBeVisible();
    await page.getByRole('link', { name: 'この内容で登録する' }).click();
    await expect(page.getByText('登録が完了致しました')).toBeVisible();
  });

  test('CSVダウンロードのテストをします', async ({ page, adminProductsProductPage }) => {
    await page.goto(url);
    await page.getByRole('row', { name: '商品名' }).getByRole('textbox').nth(1).fill(adminProductsProductPage.productName);
    await page.getByRole('link', { name: 'この条件で検索する' }).click();
    const downloadPromise = page.waitForEvent('download');
    await page.getByRole('link', { name: 'CSV ダウンロード' }).click()
    const download = await downloadPromise;
    await test.step('CSVファイルに商品名が含まれていることを確認します', async () => {
      await download.path()
        .then(path =>  fs.readFile(path))
        .then(file => Buffer.from(file))
        .then(buf => iconv.decode(buf, 'Windows-31J'))
        .then(file => expect(file).toMatch(adminProductsProductPage.productName));
    });
  });
});
