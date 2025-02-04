import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { Page } from '@playwright/test';
import { toZonedTime } from 'date-fns-tz';

import fs from 'fs/promises';

import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ ADMIN_DIR }total/index.php`;

test.describe('売上集計画面を確認をします', () => {
  let page: Page;

  const current = toZonedTime(new Date(), 'Asia/Tokyo');
  test.describe('期間別集計の確認をします', () => {
    const method = 'term';
    test('期間別集計画面を開きます', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page.locator('h1')).toContainText('売上集計＞期間別集計');
    });

    test('日付の初期値を確認します', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      expect(await page.inputValue('select[name=search_startyear_m]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth_m]')).toBe(String(current.getMonth() + 1));

      expect(await page.inputValue('select[name=search_startyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_startday]')).toBe(String(current.getDate()));
      expect(await page.inputValue('select[name=search_endyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_endmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_endday]')).toBe(String(current.getDate()));
    });

    test('月度集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('期間集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('月別集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text=月別');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('年別集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text=年別');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('曜日別集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text=曜日別');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('時間別集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text=時間別');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('2行以上のCSVダウンロードできるか確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      const [ download ] = await Promise.all([
        page.waitForEvent('download'),
        page.click('text=CSVダウンロード')
      ]);
      await download.path()
        .then(path => fs.readFile(path, 'utf-8'))
        .then(file => expect(file.split('\r\n').length).toBeGreaterThanOrEqual(2));
    });
  });

  test.describe('商品別集計の確認をします', () => {
    const method = 'products';
    test('商品別集計画面を開きます', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page.locator('h1')).toContainText('売上集計＞商品別集計');
    });

    test('日付の初期値を確認します', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      expect(await page.inputValue('select[name=search_startyear_m]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth_m]')).toBe(String(current.getMonth() + 1));

      expect(await page.inputValue('select[name=search_startyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_startday]')).toBe(String(current.getDate()));
      expect(await page.inputValue('select[name=search_endyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_endmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_endday]')).toBe(String(current.getDate()));
    });

    test('月度集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('期間集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('会員集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text="会員"');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('非会員集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text=非会員');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('2行以上のCSVダウンロードできるか確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      const [ download ] = await Promise.all([
        page.waitForEvent('download'),
        page.click('text=CSVダウンロード')
      ]);
      await download.path()
        .then(path => fs.readFile(path, 'utf-8'))
        .then(file => expect(file.split('\r\n').length).toBeGreaterThanOrEqual(2));
    });
  });

  test.describe('年代別集計の確認をします', () => {
    const method = 'age';
    test('年内別集計画面を開きます', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page.locator('h1')).toContainText('売上集計＞年代別集計');
    });

    test('日付の初期値を確認します', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      expect(await page.inputValue('select[name=search_startyear_m]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth_m]')).toBe(String(current.getMonth() + 1));

      expect(await page.inputValue('select[name=search_startyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_startday]')).toBe(String(current.getDate()));
      expect(await page.inputValue('select[name=search_endyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_endmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_endday]')).toBe(String(current.getDate()));
    });

    test('月度集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('期間集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('会員集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text="会員"');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('非会員集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text=非会員');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('2行以上のCSVダウンロードできるか確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      const [ download ] = await Promise.all([
        page.waitForEvent('download'),
        page.click('text=CSVダウンロード')
      ]);
      await download.path()
        .then(path => fs.readFile(path, 'utf-8'))
        .then(file => expect(file.split('\r\n').length).toBeGreaterThanOrEqual(2));
    });
  });

  test.describe('職業別集計の確認をします', () => {
    const method = 'job';
    test('職業別集計画面を開きます', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page.locator('h1')).toContainText('売上集計＞職業別集計');
    });

    test('日付の初期値を確認します', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      expect(await page.inputValue('select[name=search_startyear_m]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth_m]')).toBe(String(current.getMonth() + 1));

      expect(await page.inputValue('select[name=search_startyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_startday]')).toBe(String(current.getDate()));
      expect(await page.inputValue('select[name=search_endyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_endmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_endday]')).toBe(String(current.getDate()));
    });

    test('月度集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('期間集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('2行以上のCSVダウンロードできるか確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=期間で集計する');
      const [ download ] = await Promise.all([
        page.waitForEvent('download'),
        page.click('text=CSVダウンロード')
      ]);
      await download.path()
        .then(path => fs.readFile(path, 'utf-8'))
        .then(file => expect(file.split('\r\n').length).toBeGreaterThanOrEqual(2));
    });
  });

  test.describe('会員別集計の確認をします', () => {
    const method = 'member';
    test('会員別集計画面を開きます', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page.locator('h1')).toContainText('売上集計＞会員別集計');
    });

    test('日付の初期値を確認します', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      expect(await page.inputValue('select[name=search_startyear_m]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth_m]')).toBe(String(current.getMonth() + 1));

      expect(await page.inputValue('select[name=search_startyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_startday]')).toBe(String(current.getDate()));
      expect(await page.inputValue('select[name=search_endyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_endmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_endday]')).toBe(String(current.getDate()));
    });

    test('月度集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('期間集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    test('2行以上のCSVダウンロードできるか確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=期間で集計する');
      const [ download ] = await Promise.all([
        page.waitForEvent('download'),
        page.click('text=CSVダウンロード')
      ]);
      await download.path()
        .then(path => fs.readFile(path, 'utf-8'))
        .then(file => expect(file.split('\r\n').length).toBeGreaterThanOrEqual(2));
    });
  });

  test('LC_Page_Admin_Total_Ex クラスのテストをします @extends', async ( { adminLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/カスタマイズ/);
  });
});
