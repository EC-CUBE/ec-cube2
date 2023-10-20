import { test, expect, chromium, Page } from '@playwright/test';

const url = '/products/list.php';

test.describe.serial('商品一覧のテストをします', () => {
  let page: Page;
  test.beforeAll(async () => {
    const browser = await chromium.launch();
    page = await browser.newPage();
    await page.goto(url);
  });

  test('商品一覧が正常に見られているかを確認します', async () => {
    await expect(page.locator('#site_description')).toHaveText('EC-CUBE発!世界中を旅して見つけた立方体グルメを立方隊長が直送！');
  });

  test('body の class 名出力を確認します', async () => {
    await expect(page.locator('body')).toHaveAttribute('class', 'LC_Page_Products_List');
  });

  test('50件まで一覧表示します', async () => {
    await page.selectOption('#page_navi_top select', { label: '50件' });
    await page.waitForSelector('#undercolumn > form > div > div.listrightbloc > h3 > a');
    const all_products = await page.locator('#undercolumn > form > div > div.listrightbloc > h3 > a').count();
    expect(all_products).toBeLessThanOrEqual(50);


    if (all_products < 50) {
      await expect(page.locator('#undercolumn > div > span.attention')).toContainText(`${all_products}件`);
    } else {
      const hasPager = await page.locator('#page_navi_top .navi > strong').isVisible();
      if (hasPager) {
        await expect(page.locator('#undercolumn > div > span.attention')).not.toContainText(`${all_products}件`);
      } else {
        await expect(page.locator('#undercolumn > div > span.attention')).toContainText(`${all_products}件`);
      }
    }
  });

  test('食品のカテゴリを確認します', async () => {
    await page.goto(`${url}?category_id=3`);
    await page.selectOption('#page_navi_top select', { label: '50件' });
    await page.waitForSelector('#undercolumn > form > div > div.listrightbloc > h3 > a');
    const all_products = await page.locator('#undercolumn > form > div > div.listrightbloc > h3 > a').count();
    expect(all_products).toBeLessThanOrEqual(50);

    // see https://github.com/EC-CUBE/ec-cube2/pull/273
    await expect(page.locator('#undercolumn > form > div > div.listrightbloc > h3 > a')).toContainText('アイスクリーム');
    await expect(page.locator('#undercolumn > form > div > div.listrightbloc > h3 > a')).not.toContainText('おなべ');

    if (all_products < 50) {
      await expect(page.locator('#undercolumn > div > span.attention')).toContainText(`${all_products}件`);
    } else {
      const hasPager = await page.locator('#page_navi_top .navi > strong').isVisible();
      if (hasPager) {
        await expect(page.locator('#undercolumn > div > span.attention')).not.toContainText(`${all_products}件`);
      } else {
        await expect(page.locator('#undercolumn > div > span.attention')).toContainText(`${all_products}件`);
      }
    }
  });

});
