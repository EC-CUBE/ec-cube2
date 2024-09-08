import { test, expect, chromium, Page, request, APIRequestContext } from '@playwright/test';
import PlaywrightConfig from '../../../playwright.config';
import { faker } from '@faker-js/faker/locale/ja';
import { faker as fakerEN } from '@faker-js/faker/locale/en_US';
import { addYears } from 'date-fns';

const url = '/products/list.php?category_id=3';

test.describe.serial('購入フロー(ゲスト)のテストをします', () => {
  let page: Page;
  let mailcatcher: APIRequestContext;
  test.beforeAll(async () => {
    const browser = await chromium.launch();
    mailcatcher = await request.newContext({
      baseURL: PlaywrightConfig.use?.proxy ? 'http://mailcatcher:1080' : 'http://localhost:1080',
      proxy: PlaywrightConfig.use?.proxy
    });
    await mailcatcher.delete('/messages');

    page = await browser.newPage();
    await page.goto(url);
  });

  test('商品一覧を表示します', async () => {
    await expect(page.locator('form[name=product_form1] >> h3')).toContainText('アイスクリーム');
  });

  test('商品をカートに入れます', async () => {
    await page.selectOption('form[name=product_form1] >> select[name=classcategory_id1]', { label: '抹茶' });
    await page.selectOption('form[name=product_form1] >> select[name=classcategory_id2]', { label: 'S' });
    await page.fill('form[name=product_form1] >> input[name=quantity]', '2');
    await page.click('form[name=product_form1] >> [alt=カゴに入れる]');
  });

  test('カートの内容を確認します', async () => {
    await expect(page.locator('h2.title')).toContainText('現在のカゴの中');
    await expect(page.locator('table[summary=商品情報] >> tr >> nth=1')).toContainText('アイスクリーム');
    await page.click('[alt=購入手続きへ]');
  });

  test('購入手続きへ進みます', async () => {
    await expect(page).toHaveTitle(/ログイン/);
    await page.click('[alt=購入手続きへ]');
  });

  let email: string;
  test('お客様情報を入力します', async () => {
    await page.fill('input[name=order_name01]', faker.person.lastName());
    await page.fill('input[name=order_name02]', faker.person.firstName());
    await page.fill('input[name=order_kana01]', 'イシ');
    await page.fill('input[name=order_kana02]', 'キュウブ');
    await page.fill('input[name=order_company_name]', faker.company.name());
    await page.fill('input[name=order_zip01]', faker.location.zipCode('###'));
    await page.fill('input[name=order_zip02]', faker.location.zipCode('####'));
    await page.selectOption('select[name=order_pref]', { label: faker.location.state() });
    await page.fill('input[name=order_addr01]', faker.location.city());
    await page.fill('input[name=order_addr02]', faker.location.street());
    await page.fill('input[name=order_tel01]', faker.string.numeric(3));
    await page.fill('input[name=order_tel02]', faker.string.numeric(3));
    await page.fill('input[name=order_tel03]', faker.string.numeric(3));
    await page.fill('input[name=order_fax01]', faker.string.numeric(3));
    await page.fill('input[name=order_fax02]', faker.string.numeric(3));
    await page.fill('input[name=order_fax03]', faker.string.numeric(3));
    email = fakerEN.helpers.fake(String(Date.now()) + '.{{internet.exampleEmail}}').toLowerCase();
    await page.fill('input[name=order_email]', email);
    await page.fill('input[name=order_email02]', email);
    const sex = faker.number.int({ min: 1, max: 2 });
    await page.check(`input[name=order_sex][value="${ sex }"]`);
    const job = faker.number.int({ min: 1, max: 18 });
    await page.selectOption('select[name=order_job]', { value: String(job) });
    const birth = faker.date.past({ years:20, refDate:addYears(new Date(), -20).toISOString() });
    await page.selectOption('select[name=order_year]', String(birth.getFullYear()));
    await page.selectOption('select[name=order_month]', String(birth.getMonth() + 1));
    await page.selectOption('select[name=order_day]', String(birth.getDate()));

    await page.click('text=お届け先を指定');
    await page.fill('input[name=shipping_name01]', faker.person.lastName());
    await page.fill('input[name=shipping_name02]', faker.person.firstName());
    await page.fill('input[name=shipping_kana01]', 'イシ');
    await page.fill('input[name=shipping_kana02]', 'キュウブ');
    await page.fill('input[name=shipping_company_name]', faker.company.name());
    await page.fill('input[name=shipping_zip01]', faker.location.zipCode('###'));
    await page.fill('input[name=shipping_zip02]', faker.location.zipCode('####'));
    await page.selectOption('select[name=shipping_pref]', { label: faker.location.state() });
    await page.fill('input[name=shipping_addr01]', faker.location.city());
    await page.fill('input[name=shipping_addr02]', faker.location.street());
    await page.fill('input[name=shipping_tel01]', faker.string.numeric(3));
    await page.fill('input[name=shipping_tel02]', faker.string.numeric(3));
    await page.fill('input[name=shipping_tel03]', faker.string.numeric(3));
    await page.fill('input[name=shipping_fax01]', faker.string.numeric(3));
    await page.fill('input[name=shipping_fax02]', faker.string.numeric(3));
    await page.fill('input[name=shipping_fax03]', faker.string.numeric(3));

    await page.click('[alt=上記のお届け先のみに送る]');
  });

  test('お支払い方法・お届け時間の指定をします', async () => {
    await page.click('text=代金引換');
    await page.selectOption('select[name=deliv_date1]', { index: 2 });
    await page.selectOption('select[name=deliv_time_id1]', { label: '午後' });
    await page.fill('textarea[name=message]', 'お問い合わせ');
    await page.click('[alt=次へ]');
  });

  test('入力内容の確認をします', async () => {
    await expect(page.locator('h2.title')).toContainText('入力内容のご確認');
    await page.click('[alt=ご注文完了ページへ]');
  });

  test('注文完了を確認します', async () => {
    await expect(page.locator('h2.title')).toContainText('ご注文完了');

    const messages = await mailcatcher.get('/messages');
    await expect((await messages.json()).length).toBe(1);
    await expect(await messages.json()).toContainEqual(expect.objectContaining(
      {
        subject: expect.stringContaining('ご注文ありがとうございます'),
        recipients: expect.arrayContaining([ `<${ email }>` ])
      }
    ));
  });
});
