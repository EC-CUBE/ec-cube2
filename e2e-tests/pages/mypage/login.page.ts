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

    // マイページ専用のログインフォームを使用
    this.loginEmail = page.locator('#login_mypage input[name="login_email"]');
    this.loginPass = page.locator('#login_mypage input[name="login_pass"]');
    this.loginButton = page.locator('#login_mypage input[type="image"]');
    this.logoutButton = page.getByRole('button', { name: 'ログアウト' }).first();
    this.zapClient = new ZapClient();
  }

  async goto () {
    await this.page.goto(this.url);
  }

  async login () {
    await this.loginEmail.fill(this.email);
    await this.loginPass.fill(this.password);

    // AJAX対応: クリック後、ページ遷移を待つ
    await Promise.all([
      // ページ遷移を待つ（AJAX成功時にリダイレクトされる）
      this.page.waitForURL(url => url.pathname.includes('/mypage/index.php') || url.pathname.includes('/mypage/'), { timeout: 10000 }),
      // ログインボタンをクリック
      this.loginButton.click()
    ]);
  }

  async logout () {
    await this.goto();
    await this.logoutButton.click();
  }

  getZapClient () {
    return this.zapClient;
  }
}
