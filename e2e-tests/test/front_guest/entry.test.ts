import { test, expect, request, APIRequestContext } from '@playwright/test';

import { EntryPage } from '../../pages/entry/entry.page';
import PlaywrightConfig from '../../../playwright.config';
import { FakerUtils } from '../../utils/FakerUtils';

const url = '/entry/kiyaku.php';

test.describe.serial('会員登録のテストをします', () => {
  let mailcatcher: APIRequestContext;

  test.beforeAll(async () => {
    mailcatcher = await request.newContext({
      baseURL: PlaywrightConfig.use?.proxy ? 'http://mailcatcher:1080' : 'http://localhost:1080',
      proxy: PlaywrightConfig.use?.proxy
    });
    await mailcatcher.delete('/messages');
  });

  test.afterAll(async () => {
    mailcatcher.dispose();
  });

  test('会員登録のテストをします', async ({ page }) => {
    await page.goto(url);
    const password = FakerUtils.createPassword();
    const email = FakerUtils.createEmail();
    const entryPage = new EntryPage(page, email, password, url);

    await test.step('ご利用規約を確認します', async () => {
      await expect(page.locator('h2.title')).toContainText('ご利用規約');
    });

    await test.step('body の class 名出力を確認します(kiyaku)', async () => {
      await expect(page.locator('body')).toHaveAttribute('class', 'LC_Page_Entry_Kiyaku');
    });

    await test.step('規約に同意します', async () => {
      await entryPage.agree();
      await expect(page.locator('h2.title')).toContainText('会員登録(入力ページ)');
    });

    await test.step('body の class 名出力を確認します(index)', async () => {
      await expect(page.locator('body')).toHaveAttribute('class', 'LC_Page_Entry');
    });

    await test.step('会員登録内容を入力します', async () => {
      await entryPage.fill();
      await entryPage.confirm();
    });

    await test.step('会員登録内容を確認します', async () => {
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
      await entryPage.register();
    });

    await test.step('会員登録完了を確認します', async () => {
      await expect(page.locator('h2.title')).toContainText('会員登録(完了ページ)');
    });

    await test.step('会員登録完了メールを確認します', async () => {
      const messages = await mailcatcher.get('/messages');
      expect((await messages.json()).length).toBe(1);
      expect(await messages.json()).toContainEqual(expect.objectContaining(
        {
          subject: expect.stringContaining('会員登録のご完了'),
          recipients: expect.arrayContaining([`<${email}>`])
        }
      ));
    });
  });
});
