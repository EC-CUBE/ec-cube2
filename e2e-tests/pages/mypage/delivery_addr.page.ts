import { Locator, Page } from '@playwright/test';
import { PersonalInputPage } from '../personal_input.page';

export class MypageDeliveryAddrPage {
  readonly personalInputPage: PersonalInputPage;
  readonly registerButton: Locator;

  constructor (page: Page) {
    this.personalInputPage = new PersonalInputPage(page);
    this.registerButton = page.locator('[alt=登録する]');
  }

  async fill () {
    await this.personalInputPage.fillName();
    await this.personalInputPage.fillCompany();
    await this.personalInputPage.fillAddress();
    await this.personalInputPage.fillTel();
    await this.personalInputPage.fillFax();
  }

  async register () {
    await this.registerButton.click();
  }
}
