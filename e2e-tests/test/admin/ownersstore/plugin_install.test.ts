import { test, expect, chromium, Page } from '@playwright/test';
import { ZapClient, Mode, ContextType } from '../../../utils/ZapClient';
import * as tar from 'tar';
import path from 'path';
import fs from 'fs';

import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ADMIN_DIR}ownersstore/index.php`;
const zapClient = new ZapClient();
const pluginPath = path.join(__dirname, '..', '..', '..', '..', 'tests', 'class',
                             'fixtures', 'plugin', 'PrefilterTransformPlugin');

const pluginFile = path.join(__dirname, 'PrefilterTransformPlugin.tar.gz');

test.describe.serial('プラグイン管理の確認をします', () => {
  let page: Page;
  test.beforeAll(async () => {
    await zapClient.setMode(Mode.Protect);
    await zapClient.newSession('/zap/wrk/sessions/admin_ownersstore', true);
    await zapClient.importContext(ContextType.Admin);

    if (!await zapClient.isForcedUserModeEnabled()) {
      await zapClient.setForcedUserModeEnabled();
      expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy();
    }

    const browser = await chromium.launch();

    page = await browser.newPage();
    await page.goto(url);
    page.on('dialog', dialog => dialog.accept());
  });

  test.afterAll(async () => {
    await fs.promises.unlink(pluginFile);
  });

  test('プラグインを作成します', async () => {
    await tar.c(
      {
        gzip: true,
        file: pluginFile,
        cwd: pluginPath
      },
      [ 'PrefilterTransformPlugin.php', 'plugin_info.php' ]
    );
  });

  test('プラグインをインストールします', async () => {
    await page.setInputFiles('input[name=plugin_file]', pluginFile);
    await page.click('a.btn-action >> text=インストール');
    await expect(page.locator('table.system-plugin >> nth=0 >> .plugin_name')).toContainText('PrefilterTransformPlugin');
  });

  test('プラグインを有効にします', async () => {
    await page.click('table.system-plugin >> nth=0 >> text=有効にする');
  });

  test('prefilterTransform の動作を確認します', async () => {
    await page.goto('/products/list.php');
    await expect(page.locator('#undercolumn >> p >> nth=0')).toContainText('プラグイン仕様書の記述方法');
    await expect(page.locator('#undercolumn >> p >> nth=1')).toContainText('一部のプラグインは完全一致が使用されている');
  });

  test('プラグインを無効にします', async () => {
    await page.goto(url);
    await page.click('table.system-plugin >> nth=0 >> text=有効');
  });

  test('プラグインを削除します', async () => {
    await page.click('table.system-plugin >> nth=0 >> text=削除');
    await expect(page.locator('#system')).toContainText('登録されているプラグインはありません');
  });
});
