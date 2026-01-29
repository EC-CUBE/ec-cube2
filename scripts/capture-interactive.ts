import { chromium } from '@playwright/test';
import * as path from 'path';
import * as fs from 'fs';
import * as readline from 'readline';

/**
 * 対話式スクリーンショット撮影スクリプト
 *
 * 使用方法:
 *   npx ts-node scripts/capture-interactive.ts
 *
 * 前提条件:
 *   - EC-CUBEがlocalhost:4430で起動していること
 */

const BASE_URL = process.env.BASE_URL || 'https://localhost:4430';

interface ScreenshotConfig {
  url: string;
  filename: string;
  description: string;
  waitForSelector?: string;
  interactions?: Array<{ type: 'fill' | 'click', selector: string, value?: string }>;
}

async function askQuestion(question: string): Promise<string> {
  const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout,
  });

  return new Promise((resolve) => {
    rl.question(question, (answer) => {
      rl.close();
      resolve(answer);
    });
  });
}

async function captureScreenshots() {
  console.log('=== EC-CUBE スクリーンショット撮影ツール ===\n');

  // 機能名を聞く
  const featureName = await askQuestion('機能名を入力してください (例: password-reset): ');
  const screenshotsDir = path.join(__dirname, `../screenshots/${featureName}`);

  // ディレクトリ作成
  if (!fs.existsSync(screenshotsDir)) {
    fs.mkdirSync(screenshotsDir, { recursive: true });
  }

  const screenshots: ScreenshotConfig[] = [];
  let continueAdding = true;
  let index = 1;

  // 画面情報を入力
  while (continueAdding) {
    console.log(`\n--- 画面 ${index} ---`);
    const url = await askQuestion(`URL (例: /forgot/ または /products/detail.php?product_id=1): `);
    if (!url) break;

    const description = await askQuestion('画面の説明 (例: メールアドレス入力画面): ');
    const filename = `${String(index).padStart(2, '0')}-${featureName}.png`;

    screenshots.push({
      url,
      filename,
      description,
    });

    const more = await askQuestion('別の画面も撮影しますか？ (y/N): ');
    if (more.toLowerCase() !== 'y') {
      continueAdding = false;
    }
    index++;
  }

  if (screenshots.length === 0) {
    console.log('スクリーンショットが指定されていません。終了します。');
    return;
  }

  // スクリーンショット撮影開始
  console.log('\n=== スクリーンショット撮影開始 ===\n');

  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({
    ignoreHTTPSErrors: true,
    viewport: { width: 1280, height: 800 },
  });
  const page = await context.newPage();

  try {
    for (let i = 0; i < screenshots.length; i++) {
      const config = screenshots[i];
      console.log(`[${i + 1}/${screenshots.length}] ${config.description}`);
      console.log(`  URL: ${config.url}`);

      const fullUrl = config.url.startsWith('http') ? config.url : `${BASE_URL}${config.url}`;
      await page.goto(fullUrl);
      await page.waitForLoadState('networkidle');

      // 少し待つ（CSSアニメーションなど）
      await page.waitForTimeout(500);

      const screenshotPath = path.join(screenshotsDir, config.filename);
      await page.screenshot({
        path: screenshotPath,
        fullPage: true,
      });

      console.log(`  ✅ 保存: ${screenshotPath}\n`);
    }

    console.log('=== 完了 ===\n');
    console.log(`スクリーンショットを保存しました: ${screenshotsDir}\n`);
    console.log('PRに以下の画像を添付してください:\n');

    screenshots.forEach((config, idx) => {
      console.log(`${idx + 1}. ${config.description}`);
      console.log(`   ![${config.description}](./screenshots/${featureName}/${config.filename})\n`);
    });

  } catch (error) {
    console.error('エラーが発生しました:', error);
    throw error;
  } finally {
    await browser.close();
  }
}

// スクリプト実行
captureScreenshots().catch(console.error);
