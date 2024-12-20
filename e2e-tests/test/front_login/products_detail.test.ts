import { test, expect } from '../../fixtures/mypage_login.fixture';
import { chromium, Page } from '@playwright/test';

const url = '/products/detail.php?product_id=3';

test.describe('商品詳細のテストをします', () => {
  let page: Page;

  test('商品詳細が正常に見られているかを確認します', async ( { mypageLoginPage, page } ) => {
    await page.goto(url);
    await expect(page.locator('#site_description')).toHaveText('EC-CUBE発!世界中を旅して見つけた立方体グルメを立方隊長が直送！');
  });

  test('body の class 名出力を確認します', async ( { mypageLoginPage, page } ) => {
    await page.goto(url);
    await expect(page.locator('body')).toHaveAttribute('class', 'LC_Page_Products_Detail');
  });

  test('システムエラーが出ていないのを確認します', async ( { mypageLoginPage, page } ) => {
    await page.goto(url);
    await expect(page.locator('.error')).not.toBeVisible();
  });

  test('異常系を確認します', async ( { mypageLoginPage, page } ) => {
    await page.goto('/products/detail.php?product_id=a');
    await expect(page.locator('.error')).toBeVisible();
    await expect(page.locator('.error')).not.toContainText('システムエラー');
  });
});
