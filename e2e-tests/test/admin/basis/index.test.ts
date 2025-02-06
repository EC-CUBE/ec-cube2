import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';
import { faker as fakerEn } from '@faker-js/faker/locale/en';

const url = `/${ ADMIN_DIR }/basis/index.php`;
test.describe('SHOPマスターのテストをします', () => {

  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('SHOPマスターのテストをします', async ( { adminLoginPage, page }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/SHOPマスター/);

    await page.getByRole('row', { name: '会社名' }).first().getByRole('textbox').fill(faker.company.name());
    await page.getByRole('row', { name: '会社名(フリガナ)' }).getByRole('textbox').fill('イーシーキューブ');
    await page.getByRole('row', { name: '店名' }).first().getByRole('textbox').fill(faker.company.name());
    await page.getByRole('row', { name: '店名(フリガナ)' }).getByRole('textbox').fill('イーシーキューブショップ');
    await page.getByRole('row', { name: '店名(英語表記)' }).getByRole('textbox').fill(fakerEn.company.name());
    await page.getByRole('row', { name: '郵便番号' }).getByRole('textbox').first().fill(faker.location.zipCode('###'));
    await page.getByRole('row', { name: '郵便番号' }).getByRole('textbox').nth(1).fill(faker.location.zipCode('####'));
    await page.getByRole('row', { name: 'SHOP所在地' }).locator('select[name=pref]').selectOption({ label: faker.location.state() });
    await page.getByRole('row', { name: 'SHOP所在地' }).locator('input[name=addr01]').fill(faker.location.city());
    await page.getByRole('row', { name: 'SHOP所在地' }).locator('input[name=addr02]').fill(faker.location.street());
    await page.getByRole('row', { name: 'TEL' }).locator('input[name=tel01]').fill(String(faker.string.numeric(3)));
    await page.getByRole('row', { name: 'TEL' }).locator('input[name=tel02]').fill(String(faker.string.numeric(3)));
    await page.getByRole('row', { name: 'TEL' }).locator('input[name=tel03]').fill(String(faker.string.numeric(3)));
    await page.getByRole('row', { name: 'FAX' }).locator('input[name=fax01]').fill(String(faker.string.numeric(3)));
    await page.getByRole('row', { name: 'FAX' }).locator('input[name=fax02]').fill(String(faker.string.numeric(3)));
    await page.getByRole('row', { name: 'FAX' }).locator('input[name=fax03]').fill(String(faker.string.numeric(3)));
    await page.getByRole('row', { name: '店舗営業時間' }).getByRole('textbox').fill('10:00〜19:00');
    const email01 = await page.getByRole('row', { name: '商品注文受付' }).getByRole('textbox').inputValue();
    await page.getByRole('row', { name: '商品注文受付' }).getByRole('textbox').fill(faker.internet.email());
    const email02 = await page.getByRole('row', { name: '問い合わせ受付' }).getByRole('textbox').inputValue();
    await page.getByRole('row', { name: '問い合わせ受付' }).getByRole('textbox').fill(faker.internet.email());
    const email03 = await page.getByRole('row', { name: 'メール送信元' }).getByRole('textbox').inputValue();
    await page.getByRole('row', { name: 'メール送信元' }).getByRole('textbox').fill(faker.internet.email());
    const email04 = await page.getByRole('row', { name: '送信エラー受付' }).getByRole('textbox').inputValue();
    await page.getByRole('row', { name: '送信エラー受付' }).getByRole('textbox').fill(faker.internet.email());
    await page.getByRole('row', { name: '取扱商品' }).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: 'メッセージ' }).getByRole('textbox').fill(faker.lorem.sentence());
    await page.getByRole('row', { name: '定休日' }).getByText('日', { exact: true }).check();
    await page.getByRole('row', { name: '定休日' }).getByText('月', { exact: true }).check();
    await page.getByRole('row', { name: '定休日' }).getByText('火', { exact: true }).check();
    await page.getByRole('row', { name: '定休日' }).getByText('水', { exact: true }).check();
    await page.getByRole('row', { name: '定休日' }).getByText('木', { exact: true }).check();
    await page.getByRole('row', { name: '定休日' }).getByText('金', { exact: true }).check();
    await page.getByRole('row', { name: '定休日' }).getByText('土', { exact: true }).check();
    await page.getByRole('row', { name: '送料無料条件' }).getByRole('textbox').fill(String(faker.number.int({ min: 1000, max: 10000 })));
    const downloadable_days = await page.getByRole('row', { name: 'ダウンロード可能日数' }).getByRole('textbox').inputValue();
    await page.getByRole('row', { name: 'ダウンロード可能日数' }).locator('input[name=downloadable_days_unlimited]').check();
    await page.getByRole('link', { name: '確認ページへ' }).click();
    await page.getByRole('link', { name: 'この内容で登録する' }).click();

    await test.step('後続のテストのために設定を戻します', async () => {
      await page.getByRole('row', { name: '商品注文受付' }).getByRole('textbox').fill(email01);
      await page.getByRole('row', { name: '問い合わせ受付' }).getByRole('textbox').fill(email02);
      await page.getByRole('row', { name: 'メール送信元' }).getByRole('textbox').fill(email03);
      await page.getByRole('row', { name: '送信エラー受付' }).getByRole('textbox').fill(email04);
      await page.getByRole('row', { name: '定休日' }).getByText('日', { exact: true }).uncheck();
      await page.getByRole('row', { name: '定休日' }).getByText('月', { exact: true }).uncheck();
      await page.getByRole('row', { name: '定休日' }).getByText('火', { exact: true }).uncheck();
      await page.getByRole('row', { name: '定休日' }).getByText('水', { exact: true }).uncheck();
      await page.getByRole('row', { name: '定休日' }).getByText('木', { exact: true }).uncheck();
      await page.getByRole('row', { name: '定休日' }).getByText('金', { exact: true }).uncheck();
      await page.getByRole('row', { name: '定休日' }).getByText('土', { exact: true }).uncheck();
      await page.getByRole('row', { name: '送料無料条件' }).getByRole('textbox').clear();
      await page.getByRole('row', { name: 'ダウンロード可能日数' }).locator('input[name=downloadable_days_unlimited]').uncheck();
      await page.getByRole('row', { name: 'ダウンロード可能日数' }).getByRole('textbox').fill(downloadable_days);

      await page.getByRole('link', { name: '確認ページへ' }).click();
      await page.getByRole('link', { name: 'この内容で登録する' }).click();
    });
  });
});
