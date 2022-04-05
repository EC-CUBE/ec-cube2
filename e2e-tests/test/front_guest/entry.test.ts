import { test, expect, chromium, Page, request, APIRequestContext } from '@playwright/test';
import PlaywrightConfig from '../../../playwright.config';
import * as faker from '@faker-js/faker/locale/ja';
import * as fakerEN from '@faker-js/faker/locale/en_US';
import { addYears } from 'date-fns';

const url = '/entry/kiyaku.php';

test.describe.serial('会員登録のテストをします', () => {
  let page: Page;
  let mailcatcher: APIRequestContext;

  test.beforeAll(async () => {
    const browser = await chromium.launch();
    mailcatcher = await request.newContext({
      baseURL: 'http://mailcatcher:1080',
      proxy: PlaywrightConfig.use.proxy
    });
    await mailcatcher.delete('/messages');

    page = await browser.newPage();
    await page.goto(url);
  });

  test.afterAll(async () => {
    mailcatcher.dispose();
  });

  test('ご利用規約を確認します', async () => {
    await expect(page.locator('h2.title')).toContainText('ご利用規約');
  });

  test('body の class 名出力を確認します(kiyaku)', async () => {
    await expect(page.locator('body')).toHaveAttribute('class', 'LC_Page_Entry_Kiyaku');
  });

  test('規約に同意します', async () => {
    await page.click('[alt=同意して会員登録へ]');
    await expect(page.locator('h2.title')).toContainText('会員登録(入力ページ)');
  });

  test('body の class 名出力を確認します(index)', async () => {
    await expect(page.locator('body')).toHaveAttribute('class', 'LC_Page_Entry');
  });

  let email: string;
  test('会員登録内容を入力します', async () => {
    await page.fill('input[name=name01]', faker.name.lastName());
    await page.fill('input[name=name02]', faker.name.firstName());
    await page.fill('input[name=kana01]', 'イシ');
    await page.fill('input[name=kana02]', 'キュウブ');
    await page.fill('input[name=company_name]', faker.company.companyName());
    await page.fill('input[name=zip01]', faker.address.zipCode('###'));
    await page.fill('input[name=zip02]', faker.address.zipCode('####'));
    await page.selectOption('select[name=pref]', { label: faker.address.state() });
    await page.fill('input[name=addr01]', faker.address.city());
    await page.fill('input[name=addr02]', faker.address.streetName());
    await page.fill('input[name=tel01]', faker.phone.phoneNumber('###'));
    await page.fill('input[name=tel02]', faker.phone.phoneNumber('###'));
    await page.fill('input[name=tel03]', faker.phone.phoneNumber('###'));
    await page.fill('input[name=fax01]', faker.phone.phoneNumber('###'));
    await page.fill('input[name=fax02]', faker.phone.phoneNumber('###'));
    await page.fill('input[name=fax03]', faker.phone.phoneNumber('###'));
    email = fakerEN.fake(String(Date.now()) + '.{{internet.exampleEmail}}').toLowerCase();
    await page.fill('input[name=email]', email);
    await page.fill('input[name=email02]', email);
    const password = faker.datatype.uuid();
    await page.fill('input[name=password]', password);
    await page.fill('input[name=password02]', password);
    const sex = faker.datatype.number({ min: 1, max: 2 });
    await page.check(`input[name=sex][value="${sex}"]`);
    const job = faker.datatype.number({ min: 1, max: 18 });
    await page.selectOption('select[name=job]', { value: String(job) });
    const birth = faker.date.past(20, addYears(new Date(), -20).toISOString());
    await page.selectOption('select[name=year]', String(birth.getFullYear()));
    await page.selectOption('select[name=month]', String(birth.getMonth() + 1));
    await page.selectOption('select[name=day]', String(birth.getDate()));
    const reminder = faker.datatype.number({ min: 1, max: 7 });
    await page.selectOption('select[name=reminder]', String(reminder));
    await page.fill('input[name=reminder_answer]', faker.lorem.word());
    const mailmaga_flg = faker.datatype.number({ min: 1, max: 3 });
    await page.check(`input[name=mailmaga_flg][value="${mailmaga_flg}"]`);
    await page.click('[alt=確認ページへ]');
  });

  test('会員登録内容を確認します', async () => {
    await expect(page.locator('h2.title')).toContainText('会員登録(確認ページ)');
    await expect(page.locator('#form1 >> tr:nth-child(1) > td')).toContainText(await page.locator('input[name=name01]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(1) > td')).toContainText(await page.locator('input[name=name02]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(2) > td')).toContainText(await page.locator('input[name=kana01]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(2) > td')).toContainText(await page.locator('input[name=kana02]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(3) > td')).toContainText(await page.locator('input[name=company_name]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(4) > td')).toContainText(await page.locator('input[name=zip01]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(4) > td')).toContainText(await page.locator('input[name=zip02]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(5) > td')).toContainText(await page.locator('input[name=addr01]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(5) > td')).toContainText(await page.locator('input[name=addr02]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(6) > td')).toContainText(await page.locator('input[name=tel01]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(6) > td')).toContainText(await page.locator('input[name=tel02]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(6) > td')).toContainText(await page.locator('input[name=tel03]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(7) > td')).toContainText(await page.locator('input[name=fax01]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(7) > td')).toContainText(await page.locator('input[name=fax02]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(7) > td')).toContainText(await page.locator('input[name=fax03]').inputValue());
    await expect(page.locator('#form1 >> tr:nth-child(8) > td')).toContainText(await page.locator('input[name=email]').inputValue());

    // TODO 性別、職業、パスワードを忘れた時のヒント等の Type を作成する
    await page.click('[alt=会員登録をする]');
  });

  test('会員登録完了を確認します', async () => {
    await expect(page.locator('h2.title')).toContainText('会員登録(完了ページ)');
  });

  test('会員登録完了メールを確認します', async () => {
    const messages = await mailcatcher.get('/messages');
    await expect((await messages.json()).length).toBe(1);
    await expect(await messages.json()).toContainEqual(expect.objectContaining(
      {
        subject: expect.stringContaining('会員登録のご完了'),
        recipients: expect.arrayContaining([ `<${email}>` ])
      }
    ));
  });
});
