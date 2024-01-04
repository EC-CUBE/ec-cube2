import { Locator, Page } from '@playwright/test';
import PlaywrightConfig from '../../../playwright.config';

export class ProductsDetailPage {
  readonly page: Page;
  readonly classCategoryId1: Locator;
  readonly classCategoryId2: Locator;
  readonly quantity: Locator;
  readonly cartInButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.classCategoryId1 = page.locator('select[name=classcategory_id1]');
    this.classCategoryId2 = page.locator('select[name=classcategory_id2]');
    this.quantity = page.locator('input[name=quantity]');
    this.cartInButton = page.locator('[alt=カゴに入れる]');
  }

  async goto(productId: number) {
    await this.page.goto(`${PlaywrightConfig.use.baseURL}/products/detail.php?product_id=${productId}`);
  }

  async cartIn(quantity?: number, classCategory1?: string, classCategory2?: string) {
    await this.quantity.fill(String(quantity ?? 1));
    if (classCategory1 !== undefined) {
      await this.classCategoryId1.selectOption({ label: classCategory1 });
      if (classCategory2 !== undefined) {
        await this.classCategoryId2.selectOption({ label: classCategory2 });
      }
    }
    await this.cartInButton.click();
  }
}
