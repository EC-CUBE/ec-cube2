import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { Page } from '@playwright/test';
import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ADMIN_DIR}customer/`;

/**
 * Issue #1398: 管理画面の会員一覧で LIMIT が SQL に反映されず
 * 全件取得される回帰 (PR #1116 起因) の E2E 回帰テスト.
 *
 * 前提: CI/ローカル環境で `--customers=30` 以上の会員フィクスチャが投入されていること.
 */
test.describe('会員一覧のページネーション (Issue #1398)', () => {

  /**
   * 会員レコードの先頭行 (rowspan="2" を持つ td を含む tr) から customer_id を取得.
   */
  async function collectCustomerIds (page: Page): Promise<string[]> {
    const ids = await page
      .locator('#customer-search-result tr:has(td[rowspan="2"]) td:nth-child(2)')
      .allTextContents();
    return ids.map((s) => s.trim()).filter((s) => s.length > 0);
  }

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('表示件数を指定したとき LIMIT が反映され, ページャで異なる会員が表示されること', async ({ adminLoginPage, page }) => {
    await page.goto(url);

    await page.selectOption('select[name=search_page_max]', '10');
    await page.click('text=この条件で検索する');

    await expect(page.locator('#customer-search-result')).toBeVisible();
    await expect(page.locator('.pager')).toBeVisible();

    const page1Ids = await collectCustomerIds(page);
    expect(page1Ids.length).toBe(10);

    await page.click('.pager ul li:nth-child(2) a');
    await expect(page.locator('.pager .on')).toContainText('2');

    const page2Ids = await collectCustomerIds(page);
    expect(page2Ids.length).toBeGreaterThan(0);

    const duplicates = page1Ids.filter((id) => page2Ids.includes(id));
    expect(duplicates).toEqual([]);
  });
});
