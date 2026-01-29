import { test, expect, Page } from '@playwright/test';
export const endpointTests = async (page:Page, endpoint:string, title:string|null = 'EC') => {
  await test.step(`${endpoint} を確認します`, async () => {
    try {
      await page.goto(endpoint, { waitUntil: 'commit' });
    } catch (error) {
      // ERR_ABORTED エラーが発生しても、ページが実際にレンダリングされていれば続行
      if (error instanceof Error && error.message.includes('ERR_ABORTED')) {
        // ページが読み込まれているか確認するため、短時間待機
        await page.waitForLoadState('domcontentloaded', { timeout: 5000 }).catch(() => {});
      } else {
        throw error;
      }
    }
    await expect(page, `toHaveURL: ${endpoint}`).toHaveURL(new RegExp(`${endpoint}`));
    if (title !== null) {
      await expect(page).toHaveTitle(new RegExp(`${title}`));
    }

    await expect(page.locator('body'), 'ページの表示を確認').toBeVisible();
    await expect(page.locator('.error'), 'システムエラーの無いのを確認').toBeHidden();
  });
};
