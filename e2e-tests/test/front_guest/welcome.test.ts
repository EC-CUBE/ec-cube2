import { test, expect, chromium, Page } from '@playwright/test';

const url = '/index.php';

test.describe.serial('トップページのテストをします', () => {
  let page: Page;
  test.beforeAll(async () => {
    const browser = await chromium.launch();
    page = await browser.newPage();
    await page.goto(url);
  });

  test('TOPページが正常に見られているかを確認します', async () => {
    await expect(page.locator('#site_description')).toHaveText('EC-CUBE発!世界中を旅して見つけた立方体グルメを立方隊長が直送！');
    await expect(page.locator('#main_image')).toBeVisible();
  });

  test('body の class 名出力を確認します', async () => {
    await expect(page.locator('body')).toHaveAttribute('class', 'LC_Page_Index');
  });

  test('システムエラーが出ていないのを確認します', async () => {
    await expect(page.locator('.error')).not.toBeVisible();
  });

  test('LC_Page_Index_Ex クラスのテストをします @extends', async ( { page }) => {
    await page.goto(url);
    await expect(page).toHaveTitle(/カスタマイズ/);
  });
});
