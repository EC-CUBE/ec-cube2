import { Locator, Page } from "@playwright/test";
import { ADMIN_DIR } from "../../config/default.config";
import { ZapClient } from '../../utils/ZapClient';

export class AdminLoginPage {
  readonly page: Page;
  readonly url: string;

  readonly loginId: Locator;
  readonly password: Locator;
  readonly loginButton: Locator;
  zapClient: ZapClient;

  constructor (page: Page) {
    this.page = page;
    this.url = `/${ADMIN_DIR}index.php`;

    this.loginId = page.locator('input[name=login_id]');
    this.password = page.locator('input[name=password]');
    this.loginButton = page.getByRole('link', { name: 'LOGIN' });
    this.zapClient = new ZapClient();
  }

  async goto () {
    await this.page.goto(this.url);
  }

  async login (loginId: string, password: string) {
    await this.loginId.fill(loginId);
    await this.password.fill(password);
    await this.loginButton.click();
  }

  async logout () {
    await this.page.goto(`/${ADMIN_DIR}/logout.php`);
  }

  getZapClient () {
    return this.zapClient;
  }
}
