import { test as base } from "./admin_login.fixture";
import { AdminProductsProductPage } from "../../pages/admin/products/product.page";

type RegisterProductFixtures = {
  adminProductsProductPage: AdminProductsProductPage;
};

export const test = base.extend<RegisterProductFixtures>({
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  adminProductsProductPage: async ({ adminLoginPage, page }, use) => {
    const productPage = new AdminProductsProductPage(page);
    await productPage.goto();
    await productPage.fill();
    await productPage.fillSubComments();
    await productPage.fillRecommends();
    await productPage.gotoConfirm();
    await productPage.register();
    use(productPage);
  }
});

export { expect } from "@playwright/test";
