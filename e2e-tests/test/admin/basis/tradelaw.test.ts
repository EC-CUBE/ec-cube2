import { test, expect } from '../../../fixtures/admin/admin_login.fixture';
import { ADMIN_DIR } from '../../../config/default.config';
import { faker } from '@faker-js/faker/locale/ja';

const url = `/${ ADMIN_DIR }/basis/tradelaw.php`;
test.describe('特定商取引法のテストをします', () => {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars
  test('特定商取引法のテストをします', async ( { adminLoginPage, page }) => {
    page.on('dialog', dialog => dialog.accept());
    await page.goto(url);
    await expect(page.locator('h1')).toContainText(/特定商取引法/);

    const law_company = faker.company.name();
    await page.getByRole('row', { name: '販売業者' }).getByRole('textbox').fill(law_company);
    const law_manager = faker.person.fullName();
    await page.getByRole('row', { name: '運営責任者' }).getByRole('textbox').fill(law_manager);
    const zip01 = faker.location.zipCode('###');
    const zip02 = faker.location.zipCode('####');
    await page.getByRole('row', { name: '郵便番号' }).getByRole('textbox').first().fill(zip01);
    await page.getByRole('row', { name: '郵便番号' }).getByRole('textbox').nth(1).fill(zip02);
    const law_pref = faker.location.state();
    await page.getByRole('row', { name: '所在地' }).locator('select[name=law_pref]').selectOption({ label: law_pref });
    const law_addr01 = faker.location.city();
    await page.getByRole('row', { name: '所在地' }).locator('input[name=law_addr01]').fill(law_addr01);
    const law_addr02 = faker.location.street();
    await page.getByRole('row', { name: '所在地' }).locator('input[name=law_addr02]').fill(law_addr02);
    const tel01 = String(faker.string.numeric(3));
    const tel02 = String(faker.string.numeric(3));
    const tel03 = String(faker.string.numeric(3));
    await page.getByRole('row', { name: 'TEL' }).locator('input[name=law_tel01]').fill(tel01);
    await page.getByRole('row', { name: 'TEL' }).locator('input[name=law_tel02]').fill(tel02);
    await page.getByRole('row', { name: 'TEL' }).locator('input[name=law_tel03]').fill(tel03);
    const fax01 = String(faker.string.numeric(3));
    const fax02 = String(faker.string.numeric(3));
    const fax03 = String(faker.string.numeric(3));
    await page.getByRole('row', { name: 'FAX' }).locator('input[name=law_fax01]').fill(fax01);
    await page.getByRole('row', { name: 'FAX' }).locator('input[name=law_fax02]').fill(fax02);
    await page.getByRole('row', { name: 'FAX' }).locator('input[name=law_fax03]').fill(fax03);
    const law_email = faker.internet.email();
    await page.getByRole('row', { name: 'メールアドレス' }).getByRole('textbox').fill(law_email);
    const law_url = faker.internet.url();
    await page.getByRole('row', { name: 'URL' }).getByRole('textbox').fill(law_url);
    const law_term01 = faker.lorem.sentence();
    await page.getByRole('row', { name: '商品代金以外の必要料金' }).getByRole('textbox').fill(law_term01);
    const law_term02 = faker.lorem.sentence();
    await page.getByRole('row', { name: '注文方法' }).getByRole('textbox').fill(law_term02);
    const law_term03 = faker.lorem.sentence();
    await page.getByRole('row', { name: '支払方法' }).getByRole('textbox').fill(law_term03);
    const law_term04 = faker.lorem.sentence();
    await page.getByRole('row', { name: '支払期限' }).getByRole('textbox').fill(law_term04);
    const law_term05 = faker.lorem.sentence();
    await page.getByRole('row', { name: '引き渡し時期' }).getByRole('textbox').fill(law_term05);
    const law_term06 = faker.lorem.sentence();
    await page.getByRole('row', { name: '返品・交換について' }).getByRole('textbox').fill(law_term06);
    await page.getByRole('link', { name: 'この内容で登録する' }).click();

    await test.step('特定商取引のページの確認をします', async () => {
      await page.goto(`/order/index.php`);
      await expect(page.getByRole('row', { name: '販売業者' })).toContainText(law_company);
      await expect(page.getByRole('row', { name: '運営責任者' })).toContainText(law_manager);
      await expect(page.getByRole('row', { name: '住所' })).toContainText(`${ zip01 }-${ zip02 }`);
      await expect(page.getByRole('row', { name: '住所' })).toContainText(`${ law_pref }${ law_addr01 }${ law_addr02 }`);
      await expect(page.getByRole('row', { name: '電話番号' })).toContainText(`${ tel01 }-${ tel02 }-${ tel03 }`);
      await expect(page.getByRole('row', { name: 'FAX番号' })).toContainText(`${ fax01 }-${ fax02 }-${ fax03 }`);
      await expect(page.getByRole('row', { name: 'メールアドレス' })).toContainText(law_email);
      await expect(page.getByRole('row', { name: 'URL' })).toContainText(law_url);
      await expect(page.getByRole('row', { name: '商品以外の必要代金' })).toContainText(law_term01);
      await expect(page.getByRole('row', { name: '注文方法' })).toContainText(law_term02);
      await expect(page.getByRole('row', { name: '支払方法' })).toContainText(law_term03);
      await expect(page.getByRole('row', { name: '支払期限' })).toContainText(law_term04);
      await expect(page.getByRole('row', { name: '引渡し時期' })).toContainText(law_term05);
      await expect(page.getByRole('row', { name: '返品・交換について' })).toContainText(law_term06);
    });
  });
});
