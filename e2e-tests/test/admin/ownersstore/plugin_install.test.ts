import { test, expect } from '../../../fixtures/admin_login.fixture';
import { Page } from '@playwright/test';
import * as tar from 'tar';
import path from 'path';
import fs from 'fs';

import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ADMIN_DIR}ownersstore/index.php`;

const pluginPath = path.join(__dirname, '..', '..', '..', '..', 'tests', 'class',
                             'fixtures', 'plugin', 'PrefilterTransformPlugin');

const pluginFile = path.join(__dirname, 'PrefilterTransformPlugin.tar.gz');

test.describe.serial('プラグイン管理の確認をします', () => {
  let page: Page;

  test.afterAll(async () => {
    await fs.promises.unlink(pluginFile);
  });

  test('プラグイン管理画面を確認します', async ({ loginPage, page }) => {
    await page.goto(url);
    page.on('dialog', dialog => dialog.accept());

    // プラグインを作成します
    await tar.c(
      {
        gzip: true,
        file: pluginFile,
        cwd: pluginPath
      },
      [ 'PrefilterTransformPlugin.php', 'plugin_info.php' ]
    );

    // プラグインをインストールします
    await page.setInputFiles('input[name=plugin_file]', pluginFile);
    await page.click('a.btn-action >> text=インストール');
    await expect(page.locator('table.system-plugin >> nth=0 >> .plugin_name')).toContainText('PrefilterTransformPlugin');

    // プラグインを有効にします
    await page.click('table.system-plugin >> nth=0 >> text=有効にする');

    // prefilterTransform の動作を確認します
    await page.goto('/products/list.php');
    await expect(page.locator('#undercolumn >> p >> nth=0')).toContainText('プラグイン仕様書の記述方法');
    await expect(page.locator('#undercolumn >> p >> nth=1')).toContainText('一部のプラグインは完全一致が使用されている');

    // プラグインを無効にします
    await page.goto(url);
    await page.click('table.system-plugin >> nth=0 >> text=有効');

    // プラグインを削除します
    await page.click('table.system-plugin >> nth=0 >> text=削除');
    await expect(page.locator('#system')).toContainText('登録されているプラグインはありません');
  });
});
