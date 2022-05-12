import { test, expect, chromium, Page } from '@playwright/test';
import { ZapClient, Mode, ContextType } from '../../../utils/ZapClient';
import fs from 'fs/promises';

import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ADMIN_DIR}total/index.php`;
const zapClient = new ZapClient();

test.describe.serial('売上集計画面を確認をします', () => {
  let page: Page;
  test.beforeAll(async () => {
    await zapClient.setMode(Mode.Protect);
    await zapClient.newSession('/zap/wrk/sessions/admin_total', true);
    await zapClient.importContext(ContextType.Admin);

    if (!await zapClient.isForcedUserModeEnabled()) {
      await zapClient.setForcedUserModeEnabled();
      expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy();
    }

    const browser = await chromium.launch();

    page = await browser.newPage();
    await page.goto(url);
  });

  const current = new Date();
  test.describe('期間別集計の確認をします', () => {
    const method = 'term';
    test('期間別集計画面を開きます', async () => {
      await page.goto(`${url}?page=${method}`);
      await expect(page.locator('h1')).toContainText('売上集計＞期間別集計');
    });

    test('日付の初期値を確認します', async () => {
      expect(await page.inputValue('select[name=search_startyear_m]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth_m]')).toBe(String(current.getMonth() + 1));

      expect(await page.inputValue('select[name=search_startyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_startday]')).toBe(String(current.getDate()));
      expect(await page.inputValue('select[name=search_endyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_endmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_endday]')).toBe(String(current.getDate()));
    });

    test('月度集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('期間集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('月別集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await page.click('text=月別');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('年別集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await page.click('text=年別');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('曜日別集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await page.click('text=曜日別');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('時間別集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await page.click('text=時間別');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('2行以上のCSVダウンロードできるか確認をします', async () => {
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
    test('商品別集計画面を開きます', async () => {
      await page.goto(`${url}?page=${method}`);
      await expect(page.locator('h1')).toContainText('売上集計＞商品別集計');
    });

    test('日付の初期値を確認します', async () => {
      expect(await page.inputValue('select[name=search_startyear_m]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth_m]')).toBe(String(current.getMonth() + 1));

      expect(await page.inputValue('select[name=search_startyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_startday]')).toBe(String(current.getDate()));
      expect(await page.inputValue('select[name=search_endyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_endmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_endday]')).toBe(String(current.getDate()));
    });

    test('月度集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('期間集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('会員集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await page.click('text="会員"');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('非会員集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await page.click('text=非会員');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('2行以上のCSVダウンロードできるか確認をします', async () => {
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
    test('年内別集計画面を開きます', async () => {
      await page.goto(`${url}?page=${method}`);
      await expect(page.locator('h1')).toContainText('売上集計＞年代別集計');
    });

    test('日付の初期値を確認します', async () => {
      expect(await page.inputValue('select[name=search_startyear_m]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth_m]')).toBe(String(current.getMonth() + 1));

      expect(await page.inputValue('select[name=search_startyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_startday]')).toBe(String(current.getDate()));
      expect(await page.inputValue('select[name=search_endyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_endmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_endday]')).toBe(String(current.getDate()));
    });

    test('月度集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('期間集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('会員集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await page.click('text="会員"');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('非会員集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await page.click('text=非会員');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('2行以上のCSVダウンロードできるか確認をします', async () => {
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
    test('職業別集計画面を開きます', async () => {
      await page.goto(`${url}?page=${method}`);
      await expect(page.locator('h1')).toContainText('売上集計＞職業別集計');
    });

    test('日付の初期値を確認します', async () => {
      expect(await page.inputValue('select[name=search_startyear_m]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth_m]')).toBe(String(current.getMonth() + 1));

      expect(await page.inputValue('select[name=search_startyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_startday]')).toBe(String(current.getDate()));
      expect(await page.inputValue('select[name=search_endyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_endmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_endday]')).toBe(String(current.getDate()));
    });

    test('月度集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('期間集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('2行以上のCSVダウンロードできるか確認をします', async () => {
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
    test('会員別集計画面を開きます', async () => {
      await page.goto(`${url}?page=${method}`);
      await expect(page.locator('h1')).toContainText('売上集計＞会員別集計');
    });

    test('日付の初期値を確認します', async () => {
      expect(await page.inputValue('select[name=search_startyear_m]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth_m]')).toBe(String(current.getMonth() + 1));

      expect(await page.inputValue('select[name=search_startyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_startmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_startday]')).toBe(String(current.getDate()));
      expect(await page.inputValue('select[name=search_endyear]')).toBe(String(current.getFullYear()));
      expect(await page.inputValue('select[name=search_endmonth]')).toBe(String(current.getMonth() + 1));
      expect(await page.inputValue('select[name=search_endday]')).toBe(String(current.getDate()));
    });

    test('月度集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('期間集計の確認をします', async () => {
      await page.goto(url);
      await page.goto(`${url}?page=${method}`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${method}`)).toBeEnabled();
    });

    test('2行以上のCSVダウンロードできるか確認をします', async () => {
      const [ download ] = await Promise.all([
        page.waitForEvent('download'),
        page.click('text=CSVダウンロード')
      ]);
      await download.path()
        .then(path => fs.readFile(path, 'utf-8'))
        .then(file => expect(file.split('\r\n').length).toBeGreaterThanOrEqual(2));
    });
  });
});
