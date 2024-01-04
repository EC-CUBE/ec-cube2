import { test, expect, chromium, Page, request, APIRequestContext } from '@playwright/test';
import PlaywrightConfig from '../../../playwright.config';
import { ZapClient, Mode, ContextType } from '../../utils/ZapClient';
const zapClient = new ZapClient();

const url = '/products/detail.php?product_id=1';

test.describe.serial('購入フロー(ログイン)のテストをします', () => {
  let page: Page;
  let mailcatcher: APIRequestContext;
  test.beforeAll(async () => {
    await zapClient.setMode(Mode.Protect);
    await zapClient.newSession('/zap/wrk/sessions/front_login_shopping', true);
    await zapClient.importContext(ContextType.FrontLogin);

    if (!await zapClient.isForcedUserModeEnabled()) {
      await zapClient.setForcedUserModeEnabled();
      expect(await zapClient.isForcedUserModeEnabled()).toBeTruthy();
    }
    const browser = await chromium.launch();
    mailcatcher = await request.newContext({
      baseURL: 'http://mailcatcher:1080',
      proxy: PlaywrightConfig.use.proxy
    });
    await mailcatcher.delete('/messages');

    page = await browser.newPage();
    await page.goto(url);
  });

  test('商品を表示します', async () => {
    await expect(page.locator('#detailrightbloc > h2')).toContainText('アイスクリーム');
  });

  test('商品をカートに入れます', async () => {
    await page.selectOption('select[name=classcategory_id1]', { label: '抹茶' });
    await page.selectOption('select[name=classcategory_id2]', { label: 'S' });
    await page.fill('input[name=quantity]', '2');
    await page.click('[alt=カゴに入れる]');
  });

  test('カートの内容を確認します', async () => {
    await expect(page.locator('h2.title')).toContainText('現在のカゴの中');
    await expect(page.locator('table[summary=商品情報] >> tr >> nth=1')).toContainText('アイスクリーム');
    await page.click('[alt=購入手続きへ]');
  });

  test('お届け先の指定をします', async () => {
    await expect(page.locator('h2.title')).toContainText('お届け先の指定');
    await page.click('[alt=選択したお届け先に送る]');
  });

  test('お支払い方法・お届け時間の指定をします', async () => {
    await page.click('text=代金引換');
    await page.selectOption('select[name=deliv_date0]', { index: 2 });
    await page.selectOption('select[name=deliv_time_id0]', { label: '午後' });
    await page.check('#point_on');
    await page.fill('input[name=use_point]', '1');
    await page.fill('textarea[name=message]', 'お問い合わせ');
    await page.click('[alt=次へ]');
  });

  test('入力内容の確認をします', async () => {
    await expect(page.locator('h2.title')).toContainText('入力内容のご確認');
    await page.click('[alt=ご注文完了ページへ]');
  });

  const email = 'zap_user@example.com';
  test('注文完了を確認します', async () => {
    await expect(page.locator('h2.title')).toContainText('ご注文完了');

    const messages = await mailcatcher.get('/messages');
    await expect((await messages.json()).length).toBe(1);
    await expect(await messages.json()).toContainEqual(expect.objectContaining(
      {
        subject: expect.stringContaining('ご注文ありがとうございます'),
        recipients: expect.arrayContaining([ `<${email}>` ])
      }
    ));
  });
});
