import { test as base } from './cartin.fixture';
import { CartPage } from '../../pages/cart.page';

type ShoppingDelivLoginFixtures = {
  shoppingDelivLoginPage: CartPage;
};

export const test = base.extend<ShoppingDelivLoginFixtures>({
  shoppingDelivLoginPage: async ({ cartLoginPage, page }, use) => {
    await page.click('input[alt=選択したお届け先に送る]');
    use(cartLoginPage);
  }
});

export { expect } from '@playwright/test';
