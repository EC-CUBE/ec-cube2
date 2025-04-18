import { test as base } from './shopping_deliv.fixture';
import { ShoppingPaymentPage } from '../../pages/shopping/payment.page';

type ShoppingPaymentLoginFixtures = {
  shoppingPaymentLoginPage: ShoppingPaymentPage;
};

export const test = base.extend<ShoppingPaymentLoginFixtures>({
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  shoppingPaymentLoginPage: async ({ shoppingDelivLoginPage, page }, use) => {
    const paymentPage = new ShoppingPaymentPage(page);
    await paymentPage.goto();
    await paymentPage.fillOut();
    await paymentPage.gotoNext();
    use(paymentPage);
  }
});

export { expect } from '@playwright/test';
