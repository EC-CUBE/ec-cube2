import { Locator, Page } from '@playwright/test';
import { PersonalInputPage } from '../personal_input.page';
import { FakerUtils } from '../../utils/FakerUtils';

export class EntryPage  {
  readonly page: Page;
  readonly email: string;
  readonly password: string;
  readonly url: string;
  readonly personalInputPage: PersonalInputPage;
  readonly agreeButton: Locator;
  readonly confirmButton: Locator;
  readonly registerButton: Locator;

  constructor(page: Page, email?: string, password?: string, url?: string) {
    this.page = page;
    this.password = password ?? FakerUtils.createPassword();
    this.url = url ?? '/entry/kiyaku.php';
    this.registerButton = page.locator('[alt=会員登録をする]');
    this.confirmButton = page.locator('[alt=確認ページへ]');
    this.agreeButton = page.locator('[alt=同意して会員登録へ]');
    this.personalInputPage = new PersonalInputPage(page, email, url);
    this.email = email ?? this.personalInputPage.emailAddress;
  }

  async goto() {
    await this.page.goto(this.url);
  }

  async agree() {
    await this.agreeButton.click();
  }

  async fill() {
    await this.personalInputPage.fillName();
    await this.personalInputPage.fillCompany();
    await this.personalInputPage.fillAddress();
    await this.personalInputPage.fillTel();
    await this.personalInputPage.fillFax();
    await this.personalInputPage.fillEmail();
    await this.personalInputPage.fillPassword(this.password);
    await this.personalInputPage.fillPersonalInfo();
    await this.personalInputPage.fillReminder();
    await this.personalInputPage.fillMailmagaFlg();
  }

  async confirm() {
    await this.confirmButton.click();
  }

  async register() {
    await this.registerButton.click();
  }
}
