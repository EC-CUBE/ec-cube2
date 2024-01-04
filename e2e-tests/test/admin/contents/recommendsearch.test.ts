import { test, expect, chromium, Page } from '@playwright/test';
import { ZapClient, Mode, ContextType } from '../../../utils/ZapClient';
import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ADMIN_DIR}contents/recommend.php`;
const zapClient = new ZapClient();

test.describe.serial('おすすめ商品管理を確認します', () => {
  let page: Page;
  test.beforeAll(async () => {
    await zapClient.setMode(Mode.Protect);
    await zapClient.newSession('/zap/wrk/sessions/admin_contents_recommend', true);
    await zapClient.importContext(ContextType.Admin);

    if (!await zapClient.isForcedUserModeEnabled()) {
      await zapClient.setForcedUserModeEnabled();
      expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy();
    }

    const browser = await chromium.launch();

    page = await browser.newPage();
    await page.goto(url);
    page.on('dialog', dialog => dialog.accept());
  });

  test('おすすめ商品管理画面を確認します', async () => {
    await expect(page.locator('h1')).toContainText('コンテンツ管理＞おすすめ商品管理');
  });

  test.describe('カテゴリ検索を確認します', () => {
    let popup: Page;
    const nth = 0;
    test('おすすめ商品(1)の編集を確認します', async () => {
      [ popup ] = await Promise.all([
        page.waitForEvent('popup'),
        page.click(`.recommend-product >> nth=${nth} >> a >> text=編集`)
      ]);
    });

    test('カテゴリ検索を確認します', async () => {
      await popup.waitForLoadState('load');
      await expect(popup.locator('#popup-container')).toContainText('カテゴリ');
      await popup.selectOption('select[name=search_category_id]', { label: '>>>アイス' });
      await popup.click('text=検索を開始');
      await expect(popup.locator('#recommend-search-results >> tr >> nth=1')).toContainText('アイスクリーム');
    });

    test('おすすめ商品(1)を変更します', async () => {
      await popup.click('#recommend-search-results >> tr >> nth=1 >> text=決定');
      await expect(page.locator(`.recommend-product >> nth=${nth}`)).toContainText('アイスクリーム');
      await page.click(`.recommend-product >> nth=${nth} >> text=この内容で登録する`);

    });
  });

  test.describe('商品コード検索を確認します', () => {
    let popup: Page;
    const nth = 1;
    test('おすすめ商品(2)の編集を確認します', async () => {
      [ popup ] = await Promise.all([
        page.waitForEvent('popup'),
        page.click(`.recommend-product >> nth=${nth} >> a >> text=編集`)
      ]);
    });

    test('商品コード検索を確認します', async () => {
      await popup.waitForLoadState('load');
      await expect(popup.locator('#popup-container')).toContainText('商品コード');
      await popup.fill('input[name=search_product_code]', 'recipe');
      await popup.click('text=検索を開始');
      await expect(popup.locator('#recommend-search-results >> tr >> nth=1')).toContainText('おなべレシピ');
    });

    test('おすすめ商品(2)を変更します', async () => {
      await popup.click('#recommend-search-results >> tr >> nth=1 >> text=決定');
      await expect(page.locator(`.recommend-product >> nth=${nth}`)).toContainText('おなべレシピ');
      await page.click(`.recommend-product >> nth=${nth} >> text=この内容で登録する`);
    });
  });
});
