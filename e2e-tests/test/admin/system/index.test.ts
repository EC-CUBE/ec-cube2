import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { Page } from '@playwright/test';
import { faker } from '@faker-js/faker/locale/ja';
import { faker as fakerEn } from '@faker-js/faker/locale/en';

import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ ADMIN_DIR }system/index.php`;

test.describe.serial('システム設定＞メンバー管理画面を確認をします', () => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('メンバー管理画面を開きます', async ( { adminLoginPage, page } ) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText('システム設定＞メンバー管理');
  });

  let popup: Page;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('メンバー登録画面を開きます', async ( { adminLoginPage, page } ) => {
    await page.goto(url);
    [ popup ] = await Promise.all([
      page.waitForEvent('popup'),
      page.click('text=メンバーを新規入力')
    ]);
    await popup.waitForLoadState('load');
    await expect(popup.locator('h2')).toContainText('メンバー登録/編集');
  });

  const name = faker.person.lastName();
  const department = faker.company.name();
  const user = fakerEn.internet.password();
  const password = fakerEn.helpers.fake('{{internet.password}}{{number.int}}');

  let edit: Page;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('メンバー登録を確認します', async ( { adminLoginPage, page } ) => {
    await page.goto(url);
    [ popup ] = await Promise.all([
      page.waitForEvent('popup'),
      page.click('text=メンバーを新規入力')
    ]);
    await popup.waitForLoadState('load');
    popup.on('dialog', dialog => dialog.accept());
    await popup.fill('input[name=name]', name);
    await popup.fill('input[name=department]', department);
    await popup.fill('input[name=login_id]', user);
    await popup.fill('input[name=password]', password);
    await popup.fill('input[name=password02]', password);
    await popup.selectOption('select[name=authority]', { label: 'システム管理者' });
    await popup.check('#work_1');
    await popup.click('text=この内容で登録する');

    await expect(page.locator('table.list >> tr >> nth=1')).toContainText(name);

    [ edit ] = await Promise.all([
      page.waitForEvent('popup'),
      page.click('table.list >> tr >> nth=1 >> text=編集')
    ]);
    edit.on('dialog', dialog => dialog.accept());
    await expect(edit).toHaveValue('input[name=name]', name);
    await edit.fill('input[name=department]', `${ department } 変更`);
    await expect(edit).toHaveValue('input[name=login_id]', user);
    await edit.fill('input[name=password]', password);
    await edit.fill('input[name=password02]', password);
    await edit.click('text=この内容で登録する');

    await expect(page.locator('table.list >> tr >> nth=1')).toContainText(`${ department } 変更`);
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('下へ移動を確認します', async ( { adminLoginPage, page } ) => {
    await page.goto(url);
    await page.click('table.list >> tr >> nth=1 >> text=下へ');
    await expect(page.locator('table.list >> tr >> nth=2')).toContainText(name);
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('上へ移動を確認します', async ( { adminLoginPage, page } ) => {
    await page.goto(url);
    await page.click('table.list >> tr >> nth=2 >> text=上へ');
    await expect(page.locator('table.list >> tr >> nth=1')).toContainText(name);
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('メンバー削除を確認します', async ( { adminLoginPage, page } ) => {
    await page.goto(url);
    page.on('dialog', dialog => dialog.accept());
    await page.click('table.list >> tr >> nth=1 >> text=削除');
    await expect(page.locator('table.list >> tr >> nth=1')).not.toContainText(name);
  });
});
