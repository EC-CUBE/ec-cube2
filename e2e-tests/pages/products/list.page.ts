import { Locator, Page } from '@playwright/test';
import PlaywrightConfig from '../../../playwright.config';

export class ProductsListPage {
  readonly page: Page;
  readonly classCategoryId1: Locator;
  readonly classCategoryId2: Locator;
  readonly quantity: Locator;
  readonly cartInButton: Locator;
  readonly addFavoriteButton: Locator;

  constructor (page: Page) {
    const product_form = page.locator('form[name="product_form1"]');
    this.page = page;
    this.classCategoryId1 = product_form.locator('select[name=classcategory_id1]');
    this.classCategoryId2 = product_form.locator('select[name=classcategory_id2]');
    this.quantity = product_form.locator('input[name=quantity]');
    this.cartInButton = product_form.locator('[alt=カゴに入れる]');
    this.addFavoriteButton = product_form.locator('[alt=お気に入りに追加]');
  }

  async goto (productId: number) {
    await this.page.goto(`${PlaywrightConfig?.use?.baseURL}/products/detail.php?product_id=${productId}`);
  }

  async cartIn (quantity?: number, classCategory1?: string, classCategory2?: string) {
    await this.quantity.fill(String(quantity ?? 1));
    if (classCategory1 !== undefined) {
      await this.classCategoryId1.selectOption({ label: classCategory1 });
      if (classCategory2 !== undefined) {
        await this.classCategoryId2.selectOption({ label: classCategory2 });
      }
    }
    await this.cartInButton.click();
  }

  async addToFavorite () {
    await this.addFavoriteButton.click();
  }
}
