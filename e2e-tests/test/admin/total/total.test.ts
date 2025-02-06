import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { toZonedTime } from 'date-fns-tz';

import fs from 'fs/promises';

import { ADMIN_DIR } from '../../../config/default.config';

const url = `/${ ADMIN_DIR }total/index.php`;

test.describe('売上集計画面を確認をします', () => {

  const current = toZonedTime(new Date(), 'Asia/Tokyo');
  test.describe('期間別集計の確認をします', () => {
    const method = 'term';
    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('期間別集計画面を開きます', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page.locator('h1')).toContainText('売上集計＞期間別集計');
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('日付の初期値を確認します', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page).toHaveValue('select[name=search_startyear_m]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_startmonth_m]', String(current.getMonth() + 1));

      await expect(page).toHaveValue('select[name=search_startyear]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_startmonth]', String(current.getMonth() + 1));
      await expect(page).toHaveValue('select[name=search_startday]', String(current.getDate()));
      await expect(page).toHaveValue('select[name=search_endyear]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_endmonth]', String(current.getMonth() + 1));
      await expect(page).toHaveValue('select[name=search_endday]', String(current.getDate()));
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('月度集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('期間集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('月別集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text=月別');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('年別集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text=年別');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('曜日別集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text=曜日別');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('時間別集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text=時間別');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
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

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test.describe('商品別集計の確認をします', () => {
    const method = 'products';
    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('商品別集計画面を開きます', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page.locator('h1')).toContainText('売上集計＞商品別集計');
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('日付の初期値を確認します', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page).toHaveValue('select[name=search_startyear_m]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_startmonth_m]', String(current.getMonth() + 1));

      await expect(page).toHaveValue('select[name=search_startyear]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_startmonth]', String(current.getMonth() + 1));
      await expect(page).toHaveValue('select[name=search_startday]', String(current.getDate()));
      await expect(page).toHaveValue('select[name=search_endyear]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_endmonth]', String(current.getMonth() + 1));
      await expect(page).toHaveValue('select[name=search_endday]', String(current.getDate()));
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('月度集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('期間集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('会員集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text="会員"');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('非会員集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text=非会員');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
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
    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('年内別集計画面を開きます', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page.locator('h1')).toContainText('売上集計＞年代別集計');
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('日付の初期値を確認します', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page).toHaveValue('select[name=search_startyear_m]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_startmonth_m]', String(current.getMonth() + 1));

      await expect(page).toHaveValue('select[name=search_startyear]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_startmonth]', String(current.getMonth() + 1));
      await expect(page).toHaveValue('select[name=search_startday]', String(current.getDate()));
      await expect(page).toHaveValue('select[name=search_endyear]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_endmonth]', String(current.getMonth() + 1));
      await expect(page).toHaveValue('select[name=search_endday]', String(current.getDate()));
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('月度集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('期間集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('会員集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text="会員"');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('非会員集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await page.click('text=非会員');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
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

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test.describe('職業別集計の確認をします', () => {
    const method = 'job';
    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('職業別集計画面を開きます', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page.locator('h1')).toContainText('売上集計＞職業別集計');
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('日付の初期値を確認します', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page).toHaveValue('select[name=search_startyear_m]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_startmonth_m]', String(current.getMonth() + 1));

      await expect(page).toHaveValue('select[name=search_startyear]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_startmonth]', String(current.getMonth() + 1));
      await expect(page).toHaveValue('select[name=search_startday]', String(current.getDate()));
      await expect(page).toHaveValue('select[name=search_endyear]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_endmonth]', String(current.getMonth() + 1));
      await expect(page).toHaveValue('select[name=search_endday]', String(current.getDate()));
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('月度集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('期間集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
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
    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('会員別集計画面を開きます', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page.locator('h1')).toContainText('売上集計＞会員別集計');
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('日付の初期値を確認します', async ( { adminLoginPage, page } ) => {
      await page.goto(`${ url }?page=${ method }`);
      await expect(page).toHaveValue('select[name=search_startyear_m]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_startmonth_m]', String(current.getMonth() + 1));

      await expect(page).toHaveValue('select[name=search_startyear]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_startmonth]', String(current.getMonth() + 1));
      await expect(page).toHaveValue('select[name=search_startday]', String(current.getDate()));
      await expect(page).toHaveValue('select[name=search_endyear]', String(current.getFullYear()));
      await expect(page).toHaveValue('select[name=search_endmonth]', String(current.getMonth() + 1));
      await expect(page).toHaveValue('select[name=search_endday]', String(current.getDate()));
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('月度集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=月度で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
    test('期間集計の確認をします', async ( { adminLoginPage, page } ) => {
      await page.goto(url);
      await page.goto(`${ url }?page=${ method }`);
      await page.click('text=期間で集計する');
      await expect(page.locator(`#total-${ method }`)).toBeEnabled();
    });

    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
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

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('LC_Page_Admin_Total_Ex クラスのテストをします @extends', async ( { adminLoginPage, page }) => {
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/カスタマイズ/);
  });
});
