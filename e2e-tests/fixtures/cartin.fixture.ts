import { test as base } from '@playwright/test';
import PlaywrightConfig from '../../playwright.config';
import { ProductsDetailPage } from '../pages/products/detail.page';
import { CartPage } from '../pages/cart.page';

/** 商品をカートに入れて購入手続きへ進むフィクスチャ. */
export const test = base.extend({
  page: async ({ page }, use) => {
    await page.goto(PlaywrightConfig.use.baseURL); // トップへ遷移しないと、スキャン後にカートが空になってしまう
    const productsDetailPage = new ProductsDetailPage(page);
    await productsDetailPage.goto(1);
    await productsDetailPage.cartIn(2, '抹茶', 'S');
    const cartPage = new CartPage(page);
    await cartPage.gotoNext();
    use(page);
  }
});

export { expect } from '@playwright/test';
