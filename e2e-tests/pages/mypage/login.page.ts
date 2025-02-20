import { Locator, Page } from "@playwright/test";
import { ZapClient } from '../../utils/ZapClient';

export class MypageLoginPage {
  readonly page: Page;
  readonly url: string;
  readonly email: string;
  readonly password: string;

  readonly loginEmail: Locator;
  readonly loginPass: Locator;
  readonly loginButton: Locator;
  readonly logoutButton: Locator;
  zapClient: ZapClient;

  constructor (page: Page, email: string, password: string) {
    this.page = page;
    this.url = '/mypage/login.php';
    this.email = email;
    this.password = password;

    this.loginEmail = page.getByRole('textbox', { name: 'メールアドレスを入力して下さい' });
    this.loginPass = page.getByRole('textbox', { name: 'パスワードを入力して下さい' });
    this.loginButton = page.locator('id=header_login_form').getByRole('button');
    this.logoutButton = page.getByRole('button', { name: 'ログアウト' }).first();
    this.zapClient = new ZapClient();
  }

  async goto () {
    await this.page.goto(this.url);
  }

  async login () {
    await this.loginEmail.fill(this.email);
    await this.loginPass.fill(this.password);
    await this.loginButton.click();
  }

  async logout () {
    await this.goto();
    await this.logoutButton.click();
  }

  getZapClient () {
    return this.zapClient;
  }
}
