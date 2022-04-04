import { test, expect, chromium, Page } from '@playwright/test';
import { ZapClient, Mode, ContextType } from '../../../utils/ZapClient';
import * as faker from '@faker-js/faker/locale/ja';
import * as fakerEn from '@faker-js/faker/locale/en';

import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ADMIN_DIR}system/index.php`;
const zapClient = new ZapClient();

test.describe.serial('システム設定＞メンバー管理画面を確認をします', () => {
  let page: Page;
  test.beforeAll(async () => {
    await zapClient.setMode(Mode.Protect);
    await zapClient.newSession('/zap/wrk/sessions/admin_system_input', true);
    await zapClient.importContext(ContextType.Admin);

    if (!await zapClient.isForcedUserModeEnabled()) {
      await zapClient.setForcedUserModeEnabled();
      expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy();
    }

    const browser = await chromium.launch();
    page = await browser.newPage();
    await page.goto(url);
  });

  test('メンバー管理画面を開きます', async () => {
    await expect(page.locator('h1')).toContainText('システム設定＞メンバー管理');
  });

  let popup: Page;
  test('メンバー登録画面を開きます', async () => {
    [ popup ] = await Promise.all([
      page.waitForEvent('popup'),
      page.click('text=メンバーを新規入力')
    ]);
    await popup.waitForLoadState('load');
    await expect(popup.locator('h2')).toContainText('メンバー登録/編集');
  });

  const name = faker.name.lastName();
  const department = faker.company.companyName();
  const user = fakerEn.internet.password();
  const password = fakerEn.fake('{{internet.password}}{{datatype.number}}');
  test('メンバー登録を確認します', async () => {
    popup.on('dialog', dialog => dialog.accept());
    await popup.fill('input[name=name]', name);
    await popup.fill('input[name=department]', department);
    await popup.fill('input[name=login_id]', user);
    await popup.fill('input[name=password]', password);
    await popup.fill('input[name=password02]', password);
    await popup.selectOption('select[name=authority]', { label: 'システム管理者' });
    await popup.check('#work_1');
    await popup.click('text=この内容で登録する');
  });

  let edit: Page;
  test('メンバー編集を確認します', async () => {
    await expect(page.locator('table.list >> tr >> nth=1')).toContainText(name);

    [ edit ] = await Promise.all([
      page.waitForEvent('popup'),
      page.click('table.list >> tr >> nth=1 >> text=編集')
    ]);
    edit.on('dialog', dialog => dialog.accept());
    expect(await edit.inputValue('input[name=name]')).toBe(name);
    await edit.fill('input[name=department]', `${department} 変更`);
    expect(await edit.inputValue('input[name=login_id]')).toBe(user);
    await edit.fill('input[name=password]', password);
    await edit.fill('input[name=password02]', password);
    await edit.click('text=この内容で登録する');

    await expect(page.locator('table.list >> tr >> nth=1')).toContainText(`${department} 変更`);
  });

  test('下へ移動を確認します', async () => {
    await page.click('table.list >> tr >> nth=1 >> text=下へ');
    await expect(page.locator('table.list >> tr >> nth=2')).toContainText(name);
  });

  test('上へ移動を確認します', async () => {
    await page.click('table.list >> tr >> nth=2 >> text=上へ');
    await expect(page.locator('table.list >> tr >> nth=1')).toContainText(name);
  });

  test('メンバー削除を確認します', async () => {
    page.on('dialog', dialog => dialog.accept());
    await page.click('table.list >> tr >> nth=1 >> text=削除');
    await expect(page.locator('table.list >> tr >> nth=1')).not.toContainText(name);
  });
});
