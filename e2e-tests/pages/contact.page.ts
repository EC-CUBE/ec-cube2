import { test, expect } from '../fixtures/front_login/mypage_login.fixture';
import { Locator, Page } from '@playwright/test';
import PlaywrightConfig from '../../playwright.config';
import { ZapClient } from '../utils/ZapClient';

export class ContactPage {
  readonly page: Page;
  readonly url: string;

  readonly confirmButton: Locator;
  readonly submitButton: Locator;
  readonly name01: Locator;
  readonly name02: Locator;
  readonly kana01: Locator;
  readonly kana02: Locator;
  readonly zip01: Locator;
  readonly zip02: Locator;
  readonly addr01: Locator;
  readonly addr02: Locator;
  readonly tel01: Locator;
  readonly tel02: Locator;
  readonly tel03: Locator;
  readonly email: Locator;
  readonly emailConfirm: Locator;
  readonly contents: Locator;
  zapClient: ZapClient;

  //   'name01', 'name02', 'kana01', 'kana02', 'zip01', 'zip02', 'addr01', 'addr02',  'tel01', 'tel02', 'tel03'
  constructor(page: Page) {
    this.page = page;
    this.url = `${ PlaywrightConfig.use?.baseURL ?? "" }/contact/index.php`;
    this.confirmButton = page.locator('input[name=confirm][alt=確認ページへ]');
    this.submitButton = page.locator('input[name=send][alt=送信]');
    this.name01 = page.locator('input[name=name01]');
    this.name02 = page.locator('input[name=name02]');
    this.kana01 = page.locator('input[name=kana01]');
    this.kana02 = page.locator('input[name=kana02]');
    this.zip01 = page.locator('input[name=zip01]');
    this.zip02 = page.locator('input[name=zip02]');
    this.addr01 = page.locator('input[name=addr01]');
    this.addr02 = page.locator('input[name=addr02]');
    this.tel01 = page.locator('input[name=tel01]');
    this.tel02 = page.locator('input[name=tel02]');
    this.tel03 = page.locator('input[name=tel03]');
    this.email = page.locator('input[name=email]');
    this.emailConfirm = page.locator('input[name=email_confirm]');
    this.contents = page.locator('textarea[name=contents]');
    this.zapClient = new ZapClient();
  }

  async goto() {
    await this.page.goto(this.url);
  }

  async confirm() {
    await this.confirmButton.click();
  }

  async submit() {
    await this.submitButton.click();
  }

  async expectConfirmPage() {
    this.getInputFields().forEach(async (fieled) => {
      await expect(fieled).toBeHidden();
      await expect(fieled).not.toBeEmpty();
    });
  }

  private getInputFields(): Locator[] {
    return [
      this.name01,
      this.name02,
      this.kana01,
      this.kana02,
      this.zip01,
      this.zip02,
      this.addr01,
      this.addr02,
      this.tel01,
      this.tel02,
      this.tel03,
      this.email,
      this.emailConfirm
    ];
  }
  getZapClient() {
    return this.zapClient;
  }
}
