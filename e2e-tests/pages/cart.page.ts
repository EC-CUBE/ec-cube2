import { Locator, Page } from '@playwright/test';
import PlaywrightConfig from '../../playwright.config';

export class CartPage {
  readonly page: Page;
  readonly nextButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.nextButton = page.locator('input[name=confirm][alt=購入手続きへ]');
  }

  async goto() {
    await this.page.goto(`${PlaywrightConfig.use.baseURL}/cart/index.php`);
  }

  async gotoNext() {
    await this.nextButton.click();
  }
}
