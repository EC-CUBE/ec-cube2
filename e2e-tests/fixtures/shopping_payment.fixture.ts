import { test as base } from './shopping_deliv.fixture';
import { ShoppingPaymentPage } from '../pages/shopping/payment.page';

export const test = base.extend({
  page: async ({ page }, use) => {
    const paymentPage = new ShoppingPaymentPage(page);
    await paymentPage.goto();
    await paymentPage.fillOut();
    await paymentPage.gotoNext();
    use(page);
  }
});

export { expect } from '@playwright/test';
