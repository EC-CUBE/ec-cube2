import { test, expect } from '../../../fixtures/admin/register_product.fixture.ts';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';
import fs from 'fs/promises';
import iconv from 'iconv-lite';

const url = `${ADMIN_DIR}/products/category.php`;
const maxCategoryDepth = 5;

test.describe.serial('カテゴリ登録画面のテストをします', () => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('カテゴリ登録・商品割り当て・削除のテストをします', async ({ page, adminProductsProductPage }) => {
    page.on('dialog', dialog => dialog.accept());
    const categoryName = faker.lorem.words(1);
    await page.goto(url);

    const categories: string[] = [];
    for (let i = 1; i <= maxCategoryDepth; i++) {
      await test.step(`階層${i}のカテゴリを登録します`, async () => {
        const category = `${categoryName} 階層${i}`;
        categories.push(category);
        await page.locator('input[name=category_name]').fill(category);
        await page.getByRole('link', { name: '登録' }).click();

        if (i === maxCategoryDepth) {
          await expect(page.locator('id=categoryTable').getByRole('row', { name: category }), '最大階層はリンクしません').not.toHaveRole('link');
        } else {
          await page.locator('id=categoryTable')
            .getByRole('row', { name: category })
            .getByRole('link', { name: category }).click();
        }
      });
    }

    await test.step('商品をカテゴリに割り当てます', async () => {
      await page.goto(`${ADMIN_DIR}/products/index.php`);
      await page.getByRole('row', { name: '商品名' }).getByRole('textbox').nth(1).fill(adminProductsProductPage.productName);
      await page.getByRole('link', { name: 'この条件で検索する' }).click();
      await page.locator('table.list').getByRole('row').nth(2).getByRole('link', { name: '編集' }).click();
      await expect(page.getByRole('row', { name: '商品名' }).locator('input[name=name]')).toHaveValue(adminProductsProductPage.productName);
      await adminProductsProductPage.categoryIdUnselect.selectOption({ label: `>${categories.join('>')}` });
      await adminProductsProductPage.categoryRegisterButton.click();
      await adminProductsProductPage.gotoConfirm();
      await adminProductsProductPage.register();
    });

    /**
     * カテゴリ最大階層の商品並び替えテスト
     * see https://github.com/EC-CUBE/ec-cube2/pull/1122
     */
    await test.step('商品並び替え画面の確認をします', async () => {
      await page.goto(`${ADMIN_DIR}/products/product_rank.php`);
      for (let i = 1; i <= maxCategoryDepth; i++) {
        await page.locator('id=products-rank-left').locator(`li.level${i} > a`).first().click();
      }
      await expect(page.locator('id=categoryTable').getByRole('row', { name: adminProductsProductPage.productName }), '最大階層の商品を確認します').toBeVisible();
    });

    await test.step('商品を削除します', async () => {
      await page.goto(`${ADMIN_DIR}/products/index.php`);
      await page.getByRole('row', { name: '商品名' }).getByRole('textbox').nth(1).fill(adminProductsProductPage.productName);
      await page.getByRole('link', { name: 'この条件で検索する' }).click();
      await page.locator('table.list').getByRole('row').nth(2).getByRole('link', { name: '削除' }).click();
    });

    await test.step('CSVダウンロードを確認します', async () => {
      await page.goto(url);
      const downloadPromise = page.waitForEvent('download');
      await page.getByRole('link', { name: 'CSV ダウンロード' }).click();
      const download = await downloadPromise;
      await test.step('CSVファイルにカテゴリ名が含まれていることを確認します', async () => {
      await download.path()
        .then(path =>  fs.readFile(path))
        .then(file => Buffer.from(file))
        .then(buf => iconv.decode(buf, 'Windows-31J'))
        .then(file => expect(file).toMatch(categoryName));
      });
    });

    await test.step('最大階層から順にカテゴリを削除します', async () => {
      await page.goto(url);
      for (let i = 1; i <= 4; i++) {
        await page.locator('id=products-category-left').locator(`li.level${i} > a`).first().click();
      }
      await expect(page.locator('id=categoryTable').getByRole('row', { name: `${categoryName} 階層5` }), '最大階層はリンクしません').not.toHaveRole('link');

      await page.locator('id=categoryTable').getByRole('row', { name: `${categoryName} 階層5` }).getByRole('link', { name: '削除' }).click();
      for (let i = 4; i >= 2; i--) {
        await test.step(`階層${i}のカテゴリを削除します`, async () => {
          await page.locator('id=products-category-left').locator(`li.level${i - 1} > a`).first().click();
          await page.locator('id=categoryTable').getByRole('row', { name: `${categoryName} 階層${i}` }).getByRole('link', { name: '削除' }).click();
        });
      }

      await page.locator('id=products-category-left').getByText('ホーム').click();
      await page.locator('id=categoryTable').getByRole('row', { name: `${categoryName} 階層1` }).getByRole('link', { name: '削除' }).click();
    });
  });
});
