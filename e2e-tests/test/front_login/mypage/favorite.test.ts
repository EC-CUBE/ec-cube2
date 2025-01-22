import { test, expect } from '../../../fixtures/mypage_login.fixture';
import PlaywrightConfig from '../../../../playwright.config';
import { ProductsDetailPage } from '../../../pages/products/detail.page';

const url = `${ PlaywrightConfig.use?.baseURL ?? '' }/mypage/favorite.php`;
test.describe.serial('お気に入りのテストをします', () => {
  test('お気に入りのテストをします', async ( { mypageLoginPage, page }) => {
    await page.goto(url);
    await expect(page).toHaveTitle(/お気に入り一覧/);
    await expect(page.getByText('お気に入りが登録されておりません')).toBeVisible();

    await test.step('お気に入りを登録します', async () => {
      const productsDetailPage = new ProductsDetailPage(page);
      await productsDetailPage.goto(2);
      await productsDetailPage.addToFavorite();
      await expect(page.getByTitle('お気に入りに登録済み')).toBeVisible();
    });

    await test.step('お気に入り一覧を確認します', async () => {
      await page.goto(url);
      await expect(page).toHaveTitle(/お気に入り一覧/);
      await expect(page.getByText('1件のお気に入りがあります')).toBeVisible();
      await expect(page.locator('table[summary=お気に入り]').getByRole('row', {name: 'おなべ'})).toBeVisible();
    });

    await test.step('お気に入りを削除します', async () => {
      await page.locator('table[summary=お気に入り]').getByRole('row', {name: 'おなべ'}).getByRole('link', {name: '削除'}).click();
      await expect(page.getByText('お気に入りが登録されておりません')).toBeVisible();
    });
  });
});
