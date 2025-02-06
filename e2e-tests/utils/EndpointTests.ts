import { test, expect, Page } from '@playwright/test';
export const endpointTests = async (page:Page, endpoint:string, title:string|null = 'EC') => {
  await test.step(`${ endpoint } を確認します`, async () => {
    await page.goto(endpoint);
    await expect(page, `toHaveURL: ${ endpoint }`).toHaveURL(new RegExp(`${ endpoint }`));
    if (title !== null) {
      await expect(page).toHaveTitle(new RegExp(`${ title }`));
    }

    await expect(page.locator('body'), 'ページの表示を確認').toBeVisible();
    await expect(page.locator('.error'), 'システムエラーの無いのを確認').not.toBeVisible();
  });
};
