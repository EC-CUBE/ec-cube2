import { chromium } from '@playwright/test';
import * as path from 'path';
import * as fs from 'fs';
import { execSync } from 'child_process';

/**
 * git diffから自動的にスクリーンショットを撮影するスクリプト
 *
 * 使用方法:
 *   npx ts-node scripts/capture-auto.ts
 *
 * 動作:
 *   1. git diffで変更されたファイルを検出
 *   2. PHPファイルやテンプレートからURLを推測
 *   3. 自動的にスクリーンショットを撮影
 */

const BASE_URL = process.env.BASE_URL || 'https://localhost:4430';

interface DetectedPage {
  url: string;
  description: string;
  sourceFile: string;
}

function getChangedFiles(): string[] {
  try {
    const output = execSync('git diff --name-only HEAD', { encoding: 'utf-8' });
    return output.split('\n').filter(f => f.length > 0);
  } catch (error) {
    console.error('git diffの取得に失敗しました:', error);
    return [];
  }
}

function detectPagesFromFiles(files: string[]): DetectedPage[] {
  const pages: DetectedPage[] = [];

  for (const file of files) {
    // data/class/pages/*/LC_Page_*.php からURLを推測
    const pageMatch = file.match(/data\/class\/pages\/(.+?)\/LC_Page_(.+?)\.php/);
    if (pageMatch) {
      const dir = pageMatch[1];
      const page = pageMatch[2].toLowerCase();
      pages.push({
        url: `/${dir}/`,
        description: `${dir} - ${page}ページ`,
        sourceFile: file,
      });
    }

    // html/*.php から直接URLを推測
    const htmlMatch = file.match(/html\/(.+?)\.php/);
    if (htmlMatch && !htmlMatch[1].includes('/')) {
      const phpFile = htmlMatch[1];
      pages.push({
        url: `/${phpFile}.php`,
        description: `${phpFile}ページ`,
        sourceFile: file,
      });
    }

    // テンプレートファイルからURLを推測
    const tplMatch = file.match(/data\/Smarty\/templates\/default\/(.+?)\/(.+?)\.tpl/);
    if (tplMatch) {
      const dir = tplMatch[1];
      const tpl = tplMatch[2];
      pages.push({
        url: `/${dir}/`,
        description: `${dir} - ${tpl}画面`,
        sourceFile: file,
      });
    }
  }

  // 重複を除去
  const uniquePages = pages.filter((page, index, self) =>
    index === self.findIndex((p) => p.url === page.url)
  );

  return uniquePages;
}

async function captureScreenshots() {
  console.log('=== 変更されたファイルから画面を自動検出 ===\n');

  const changedFiles = getChangedFiles();
  if (changedFiles.length === 0) {
    console.log('変更されたファイルがありません。');
    return;
  }

  console.log(`変更されたファイル: ${changedFiles.length}件`);
  changedFiles.forEach(f => console.log(`  - ${f}`));
  console.log('');

  const detectedPages = detectPagesFromFiles(changedFiles);
  if (detectedPages.length === 0) {
    console.log('画面関連のファイルが見つかりませんでした。');
    console.log('対話式スクリプトを使用してください: npx ts-node scripts/capture-interactive.ts');
    return;
  }

  console.log(`検出された画面: ${detectedPages.length}件`);
  detectedPages.forEach((page, i) => {
    console.log(`  ${i + 1}. ${page.description}`);
    console.log(`     URL: ${page.url}`);
    console.log(`     元ファイル: ${page.sourceFile}`);
  });
  console.log('');

  // スクリーンショット保存先
  const timestamp = new Date().toISOString().split('T')[0];
  const screenshotsDir = path.join(__dirname, `../screenshots/auto-${timestamp}`);
  if (!fs.existsSync(screenshotsDir)) {
    fs.mkdirSync(screenshotsDir, { recursive: true });
  }

  // スクリーンショット撮影
  console.log('=== スクリーンショット撮影開始 ===\n');

  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({
    ignoreHTTPSErrors: true,
    viewport: { width: 1280, height: 800 },
  });
  const page = await context.newPage();

  try {
    for (let i = 0; i < detectedPages.length; i++) {
      const config = detectedPages[i];
      console.log(`[${i + 1}/${detectedPages.length}] ${config.description}`);

      const fullUrl = `${BASE_URL}${config.url}`;
      console.log(`  アクセス中: ${fullUrl}`);

      try {
        await page.goto(fullUrl, { waitUntil: 'networkidle', timeout: 10000 });
        await page.waitForTimeout(500);

        const filename = `${String(i + 1).padStart(2, '0')}-${config.url.replace(/\//g, '-')}.png`;
        const screenshotPath = path.join(screenshotsDir, filename);
        await page.screenshot({
          path: screenshotPath,
          fullPage: true,
        });

        console.log(`  ✅ 保存: ${filename}\n`);
      } catch (error) {
        console.log(`  ⚠️  スキップ: ${error}\n`);
      }
    }

    console.log('=== 完了 ===\n');
    console.log(`スクリーンショットを保存しました: ${screenshotsDir}\n`);
    console.log('PRに画像を添付する場合、以下のマークダウンを使用してください:\n');

    const files = fs.readdirSync(screenshotsDir).filter(f => f.endsWith('.png'));
    files.forEach((filename, idx) => {
      const page = detectedPages[idx];
      console.log(`![${page.description}](./screenshots/auto-${timestamp}/${filename})`);
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
