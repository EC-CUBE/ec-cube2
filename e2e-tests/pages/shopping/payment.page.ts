import { Locator, Page } from '@playwright/test';
import PlaywrightConfig from '../../../playwright.config';

export class ShoppingPaymentPage {
  readonly page: Page;
  readonly nextButton: Locator;
  readonly paymentMethod: Locator;
  readonly deliveryDate: Locator;
  readonly deliveryTime: Locator;
  readonly enablePoint: Locator;
  readonly disablePoint: Locator;
  readonly usePoint: Locator;
  readonly message: Locator;

  constructor(page: Page) {
    this.page = page;
    this.nextButton = page.locator('[alt=次へ]');
    this.paymentMethod = page.locator('#payment');
    this.deliveryDate = page.locator('#deliv_date0');
    this.deliveryTime = page.locator('#deliv_time_id0');
    this.enablePoint = page.locator('#point_on');
    this.disablePoint = page.locator('#point_off');
    this.usePoint = page.locator('input[name=use_point]');
    this.message = page.locator('textarea[name=message]');
  }

  async goto() {
    await this.page.goto(`${PlaywrightConfig.use.baseURL}/shopping/payment.php`);
  }

  async gotoNext() {
    await this.nextButton.click();
  }

  async selectPaymentMethod(label: string) {
    await this.paymentMethod.locator(`text=${label}`).click();
  }

  async selectDeliveryDate(index: number) {
    await this.deliveryDate.selectOption({ index: index });
  }

  async selectDeliveryTime(index: number) {
    await this.deliveryTime.selectOption({ index: index });
  }

  async chooseToUsePoint() {
    await this.enablePoint.check();
  }

  async doNotChooseToUsePoint() {
    await this.disablePoint.check();
  }

  async fillUsePoint(point: number) {
    await this.usePoint.fill(String(point));
  }

  async fillMessage(message: string) {
    await this.message.fill(message);
  }

  async fillOut(paymentMethod?: string, deliveryDateIndex?: number, deliveryTimeIndex?:number, message?: string, usePoint?: number) {
    await this.selectPaymentMethod(paymentMethod ?? '銀行振込');
    await this.selectDeliveryDate(deliveryDateIndex ?? 1);
    await this.selectDeliveryTime(deliveryTimeIndex ?? 1);
    await this.fillMessage(message ?? 'お問い合わせ');

    if (await this.enablePoint.isVisible()) {
      await this.chooseToUsePoint();
      await this.fillUsePoint(usePoint ?? 1);
    }
  }
}
