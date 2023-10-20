import { test as base } from './cartin.fixture';
import PlaywrightConfig from '../../playwright.config';

export const test = base.extend({
  page: async ({ page }, use) => {
    await page.click('input[alt=選択したお届け先に送る]');
    use(page);
  }
});

export { expect } from '@playwright/test';
