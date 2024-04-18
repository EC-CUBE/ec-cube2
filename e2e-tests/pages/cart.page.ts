import { Locator, Page } from '@playwright/test';
import PlaywrightConfig from '../../playwright.config';
import { ZapClient } from '../utils/ZapClient';

export class CartPage {
  readonly page: Page;
  readonly url: string;

  readonly nextButton: Locator;
  zapClient: ZapClient;

  constructor(page: Page) {
    this.page = page;
    this.url = `${PlaywrightConfig.use?.baseURL ?? ""}/cart/index.php`;
    this.nextButton = page.locator('input[name=confirm][alt=購入手続きへ]');
    this.zapClient = new ZapClient();
  }

  async goto() {
    await this.page.goto(this.url);
  }

  async gotoNext() {
    await this.nextButton.click();
  }

  getAdditionButton(row?: number) {
    return this.page.locator(`table[summary=商品情報] >> tr >> nth=${row ?? 1} >> td >> nth=4 >> [alt="＋"]`);
  }

  getSubtructionButton(row?: number) {
    return this.page.locator(`table[summary=商品情報] >> tr >> nth=${row ?? 1} >> td >> nth=4 >> [alt="-"]`);
  }

  getQuantity(row?: number) {
    return this.page.locator(`table[summary=商品情報] >> tr >> nth=${row ?? 1} >> td >> nth=4`);
  }

  async addition(row?: number) {
    await this.getAdditionButton(row).click();
  }

  async subtruction(row?: number) {
    await this.getSubtructionButton(row).click();
  }

  getZapClient() {
    return this.zapClient;
  }
}
