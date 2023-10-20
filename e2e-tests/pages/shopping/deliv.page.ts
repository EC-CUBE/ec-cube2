import { Locator, Page } from '@playwright/test';
import PlaywrightConfig from '../../../playwright.config';

export class ShoppingDelivPage {
  readonly page: Page;
  readonly nextButton: Locator;
  readonly addNewDeliveryAddressButton: Locator;
  readonly sendToMultipleButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.nextButton = page.locator('input[alt=選択したお届け先に送る]');
    this.addNewDeliveryAddressButton = page.locator('[alt=新しいお届け先を追加する]');
    this.sendToMultipleButton = page.locator('[alt=お届け先を複数指定する]');
  }

  async goto() {
    await this.page.goto(`${PlaywrightConfig.use.baseURL}/shopping/deliv.php`);
  }

  async gotoNext() {
    await this.nextButton.click();
  }

  async gotoAddNewDeliveryAddress() {
    await this.addNewDeliveryAddressButton.click();
  }

  async gotoSendToMultiple() {
    await this.sendToMultipleButton.click();
  }
}
