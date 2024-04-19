import { test, expect } from '../../../fixtures/admin_login.fixture';
import { Page } from '@playwright/test';
import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ ADMIN_DIR }contents/recommend.php`;

test.describe('おすすめ商品管理を確認します', () => {
  let page: Page;

  test('おすすめ商品管理画面を確認します', async ( { loginPage, page } ) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText('コンテンツ管理＞おすすめ商品管理');
  });

  test.describe('カテゴリ検索を確認します', () => {
    let popup: Page;
    const nth = 0;
    test('おすすめ商品(1)の編集を確認します', async ( { loginPage, page } ) => {
      await page.goto(url);
      page.on('dialog', dialog => dialog.accept());
      [ popup ] = await Promise.all([
        page.waitForEvent('popup'),
        page.click(`.recommend-product >> nth=${ nth } >> a >> text=編集`)
      ]);
    });

    test('カテゴリ検索を確認します', async ( { loginPage, page } ) => {
      await page.goto(url);
      page.on('dialog', dialog => dialog.accept());
      [ popup ] = await Promise.all([
        page.waitForEvent('popup'),
        page.click(`.recommend-product >> nth=${ nth } >> a >> text=編集`)
      ]);
      await popup.waitForLoadState('load');
      await expect(popup.locator('#popup-container')).toContainText('カテゴリ');
      await popup.selectOption('select[name=search_category_id]', { label: '>>>アイス' });
      await popup.click('text=検索を開始');
      await expect(popup.locator('#recommend-search-results >> tr >> nth=1')).toContainText('アイスクリーム');

      await popup.click('#recommend-search-results >> tr >> nth=1 >> text=決定');
      await expect(page.locator(`.recommend-product >> nth=${ nth }`)).toContainText('アイスクリーム');
      await page.click(`.recommend-product >> nth=${ nth } >> text=この内容で登録する`);
    });
  });

  test.describe('商品コード検索を確認します', () => {
    let popup: Page;
    const nth = 1;
    test('おすすめ商品(2)の編集を確認します', async ( { loginPage, page } ) => {
      await page.goto(url);
      page.on('dialog', dialog => dialog.accept());
      [ popup ] = await Promise.all([
        page.waitForEvent('popup'),
        page.click(`.recommend-product >> nth=${ nth } >> a >> text=編集`)
      ]);
    });

    test('商品コード検索を確認します', async ( { loginPage, page } ) => {
      await page.goto(url);
      page.on('dialog', dialog => dialog.accept());
      [ popup ] = await Promise.all([
        page.waitForEvent('popup'),
        page.click(`.recommend-product >> nth=${ nth } >> a >> text=編集`)
      ]);
      await popup.waitForLoadState('load');
      await expect(popup.locator('#popup-container')).toContainText('商品コード');
      await popup.fill('input[name=search_product_code]', 'recipe');
      await popup.click('text=検索を開始');
      await expect(popup.locator('#recommend-search-results >> tr >> nth=1')).toContainText('おなべレシピ');

      await popup.click('#recommend-search-results >> tr >> nth=1 >> text=決定');
      await expect(page.locator(`.recommend-product >> nth=${ nth }`)).toContainText('おなべレシピ');
      await page.click(`.recommend-product >> nth=${ nth } >> text=この内容で登録する`);
    });
  });
});
