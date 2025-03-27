import { test, expect } from '../../../fixtures/admin/register_product.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';

const url = `/${ADMIN_DIR}/products/review.php`;
test.describe.serial('レビュー管理のテストをします', () => {

  const fullName = faker.person.fullName();
  let productName;

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('レビューを投稿します', async ( { adminProductsProductPage, page }) => {
    productName = adminProductsProductPage.productName;
    await page.goto('/');
    await page.locator('input[name=name]').fill(productName);
    await page.getByRole('button', { name: '検索 ' }).click();
    await page.getByRole('heading').getByRole('link', { name: productName }).click();

    await test.step('コメントを書き込みます', async () => {
      const popupPromise = page.waitForEvent('popup');
      await page.getByRole('link', { name: '新規コメントを書き込む' }).click();
      const popup = await popupPromise;

      await expect(popup.getByRole('row', { name: '商品名' }).getByRole('cell').nth(1)).toHaveText(productName);
      await popup.getByRole('row', { name: '投稿者名' }).getByRole('textbox').fill(fullName);
      await popup.getByRole('row', { name: '投稿者URL' }).getByRole('textbox').fill(faker.internet.url());
      await popup.getByRole('row', { name: '性別' }).getByLabel(faker.helpers.arrayElement(['男性', '女性'])).check();
      await popup.getByRole('row', { name: 'おすすめレベル' }).locator('select').selectOption({ label: faker.helpers.arrayElement(['★', '★★', '★★★', '★★★★', '★★★★★']) });
      await popup.getByRole('row', { name: 'タイトル' }).getByRole('textbox').fill(faker.lorem.paragraph());
      await popup.getByRole('row', { name: 'コメント' }).getByRole('textbox').fill(faker.lorem.sentences());
      await popup.getByAltText('確認ページへ').click();
      await popup.getByAltText('送信').click();
      await expect(popup.getByText('登録が完了しました')).toBeVisible();
      await popup.getByAltText('閉じる').click();
    });
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('レビュー管理のテストをします', async ({ adminLoginPage, page }) => {
    await page.goto(url);
    await page.getByRole('row', { name: '投稿者名' }).getByRole('textbox').first().fill(fullName);
    await page.getByRole('link', { name: 'この条件で検索する' }).click();

    await page.locator('id=products-review-result').getByRole('row', { name: fullName }).getByRole('link', { name: '編集' }).click();

    await page.getByRole('row', { name: 'レビュー表示' }).locator('input[name=status]').nth(1).check();
    await page.getByRole('link', { name: 'この内容で登録する' }).click();

    await test.step('レビューを確認します', async () => {
      await page.goto('/');
      await page.locator('input[name=name]').fill(productName);
      await page.getByRole('button', { name: '検索 ' }).click();
      await page.getByRole('heading').getByRole('link', { name: productName }).click();
      await expect(page.getByText(fullName)).toBeVisible();
    });
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('レビューを削除します', async ({ adminLoginPage, page }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    await page.getByRole('row', { name: '投稿者名' }).getByRole('textbox').first().fill(fullName);
    await page.getByRole('link', { name: 'この条件で検索する' }).click();

    await page.locator('id=products-review-result').getByRole('row', { name: fullName }).getByRole('link', { name: '削除' }).click();

    await test.step('レビューを確認します', async () => {
      await page.goto('/');
      await page.locator('input[name=name]').fill(productName);
      await page.getByRole('button', { name: '検索 ' }).click();
      await page.getByRole('heading').getByRole('link', { name: productName }).click();
      await expect(page.getByText(fullName)).toBeHidden();
    });
  });
});
