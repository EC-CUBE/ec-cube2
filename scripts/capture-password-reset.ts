import { chromium } from '@playwright/test';
import * as path from 'path';
import * as fs from 'fs';

/**
 * パスワードリセット機能のスクリーンショット撮影スクリプト
 *
 * 使用方法:
 *   npx ts-node scripts/capture-password-reset.ts
 *
 * 前提条件:
 *   - EC-CUBEがlocalhost:4430で起動していること
 *   - MailCatcherがlocalhost:1080で起動していること
 */

const BASE_URL = process.env.BASE_URL || 'https://localhost:4430';
const SCREENSHOTS_DIR = path.join(__dirname, '../screenshots/password-reset');

async function capturePasswordResetFlow() {
  // スクリーンショット保存ディレクトリを作成
  if (!fs.existsSync(SCREENSHOTS_DIR)) {
    fs.mkdirSync(SCREENSHOTS_DIR, { recursive: true });
  }

  const browser = await chromium.launch({ headless: false }); // 動作確認のため非headless
  const context = await browser.newContext({
    ignoreHTTPSErrors: true,
    viewport: { width: 1280, height: 800 },
  });
  const page = await context.newPage();

  try {
    console.log('1. パスワード再発行ページにアクセス');
    await page.goto(`${BASE_URL}/forgot/`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({
      path: path.join(SCREENSHOTS_DIR, '01-forgot-index.png'),
      fullPage: true
    });

    console.log('2. メールアドレスを入力');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.screenshot({
      path: path.join(SCREENSHOTS_DIR, '02-forgot-email-filled.png'),
      fullPage: true
    });

    console.log('3. 送信ボタンをクリック（実際には送信しない - デモ用）');
    // await page.click('button[type="submit"]');
    // await page.waitForLoadState('networkidle');
    // await page.screenshot({
    //   path: path.join(SCREENSHOTS_DIR, '03-forgot-request-complete.png'),
    //   fullPage: true
    // });

    console.log('4. パスワード設定画面（URLパラメータ付き）');
    await page.goto(`${BASE_URL}/forgot/?mode=reset&token=dummy_token_for_screenshot_only`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({
      path: path.join(SCREENSHOTS_DIR, '04-forgot-reset-form.png'),
      fullPage: true
    });

    console.log('5. パスワード入力');
    await page.fill('input[name="password"]', '••••••••');
    await page.fill('input[name="password02"]', '••••••••');
    await page.screenshot({
      path: path.join(SCREENSHOTS_DIR, '05-forgot-password-filled.png'),
      fullPage: true
    });

    console.log(`\n✅ スクリーンショットを保存しました: ${SCREENSHOTS_DIR}`);
    console.log('\nPRに以下の画像を添付してください:');
    fs.readdirSync(SCREENSHOTS_DIR)
      .filter(file => file.endsWith('.png'))
      .forEach(file => console.log(`  - screenshots/password-reset/${file}`));

  } catch (error) {
    console.error('エラーが発生しました:', error);
    throw error;
  } finally {
    await browser.close();
  }
}

// スクリプト実行
capturePasswordResetFlow().catch(console.error);
