import { Locator, Page } from "@playwright/test";
import { ADMIN_DIR } from "../../../config/default.config";
import { faker } from '@faker-js/faker/locale/ja';
import path from 'path';

type Category = { readonly label: string, value?: string };
export class AdminProductsProductPage {
  readonly page: Page;
  readonly url: string;
  readonly name: Locator;
  readonly categoryIdUnselect: Locator;
  readonly categoryId: Locator;
  readonly categoryRegisterButton: Locator;
  readonly categoryDeleteButton: Locator
  readonly status: Locator;
  readonly productStatus: Locator;
  readonly productType: Locator;
  readonly productCode: Locator;
  readonly price01: Locator;
  readonly price02: Locator;
  readonly stock: Locator;
  readonly stockUnlimited: Locator;
  readonly pointRate: Locator;
  readonly delivDate: Locator;
  readonly saleLimit: Locator;
  readonly comment3: Locator;
  readonly note: Locator;
  readonly mainListComment: Locator;
  readonly mainComment: Locator;
  readonly mainLargeImage: Locator;

  readonly subTitles: Locator[] = [];
  readonly subComments: Locator[] = [];
  readonly subLargeImages: Locator[] = [];

  readonly recommendChangeButtons: Locator[] = [];
  readonly recommendDeletes: Locator[] = [];
  readonly recommendComments: Locator[] = [];

  readonly confirmButton: Locator;
  readonly registerButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.url = `/${ ADMIN_DIR }products/product.php`;
    this.name = page.getByRole('row', { name: '商品名' }).getByRole('textbox');
    this.categoryIdUnselect = page.getByRole('row', { name: '商品カテゴリ' }).locator('id=category_id_unselect');
    this.categoryId = page.getByRole('row', { name: '商品カテゴリ' }).locator('id=category_id');
    this.categoryRegisterButton = page.getByRole('row', { name: '商品カテゴリ' }).getByRole('link', { name: '<- 登録' });
    this.categoryDeleteButton = page.getByRole('row', { name: '商品カテゴリ' }).getByRole('link', { name: '削除 ->' });
    this.status = page.getByRole('row', { name: '公開・非公開' }).getByRole('cell').nth(1);
    this.productStatus = page.getByRole('row', { name: '商品ステータス' }).getByRole('cell').nth(1);
    this.productType = page.getByRole('row', { name: '商品種別' }).getByRole('cell').nth(1);
    this.productCode = page.getByRole('row', { name: '商品コード' }).getByRole('textbox');
    this.price01 = page.getByRole('row', { name: '通常価格' }).getByRole('textbox');
    this.price02 = page.getByRole('row', { name: '販売価格' }).getByRole('textbox');
    this.stock = page.getByRole('row', { name: '在庫数' }).getByRole('textbox');
    this.stockUnlimited = page.getByRole('row', { name: '在庫数' }).locator('input[name=stock_unlimited]');
    this.pointRate = page.getByRole('row', { name: 'ポイント付与率' }).getByRole('textbox');
    this.delivDate = page.getByRole('row', { name: '発送日目安' }).locator('select');
    this.saleLimit = page.getByRole('row', { name: '販売制限数' }).getByRole('textbox');
    this.comment3 = page.getByRole('row', { name: '検索ワード' }).getByRole('textbox');
    this.note = page.getByRole('row', { name: '備考欄(SHOP専用)' }).getByRole('textbox');
    this.mainListComment = page.getByRole('row', { name: '一覧-メインコメント' }).getByRole('textbox');
    this.mainComment = page.getByRole('row', { name: '詳細-メインコメント' }).getByRole('textbox');
    this.mainLargeImage = page.getByRole('row', { name: '詳細-メイン拡大画像' }).getByRole('link', { name: 'アップロード' })

    this.confirmButton = page.getByRole('link', { name: '確認ページへ' });
    this.registerButton = page.getByRole('link', { name: 'この内容で登録する' });
    for (let i = 1; i <= 5; i++) {
      this.subTitles[i] = page.getByRole('row', { name: `詳細-サブタイトル( ${i})` }).getByRole('textbox');
      this.subComments[i] = page.getByRole('row', { name: `詳細-サブコメント( ${i})` }).getByRole('textbox');
      this.subLargeImages[i] = page.getByRole('row', { name: `詳細-サブ拡大画像( ${i})` }).getByRole('link', { name: 'アップロード' });
    }
    for (let i = 1; i <= 6; i++) {
      this.recommendChangeButtons[i] = page.getByRole('row', { name: `関連商品( ${i})` }).getByRole('link', { name: '変更' });
      this.recommendDeletes[i] = page.getByRole('row', { name: `関連商品( ${i})` }).locator(`input[name=recommend_delete${i}]`);
      this.recommendComments[i] = page.getByRole('row', { name: `関連商品( ${i})` }).getByRole('textbox');
    }
  }

  async goto() {
    await this.page.goto(this.url);
  }

  async fill() {
    await this.name.fill(faker.company.name());
    const selectCategories = await this.selectCategories();
    const unselectCategory = await this.unselectCategory(selectCategories);
    await this.categoryIdUnselect.selectOption(selectCategories);
    await this.categoryRegisterButton.click();
    if (selectCategories.length > 1) {
      await this.categoryId.selectOption(faker.helpers.arrayElement([unselectCategory]));
      await this.categoryDeleteButton.click();
    }
    await this.fillStatus();
    await this.fillProductStatus();
    await this.fillProductType();
    await this.productCode.fill(faker.string.ulid());
    await this.price01.fill(String(faker.number.int({ min: 1, max: 99999999 })));
    await this.price02.fill(String(faker.number.int({ min: 1, max: 99999999 })));
    await this.fillStock();
    await this.fillPointRate();
    await this.fillDelivDate();
    await this.fillSaleLimit();
    await this.fillComment3();
    await this.note.fill(faker.lorem.text());
    await this.mainListComment.fill(faker.lorem.paragraph());
    this.mainComment.fill(faker.lorem.text());
    await this.uploadMainLargeImage();
  }

  async gotoConfirm() {
    await this.confirmButton.click();
  }

  async register() {
    await this.registerButton.click();
  }

  async selectCategories() {
    return faker.helpers.arrayElements(
      [
        { label: '>雑貨' },
        { label: '>食品' },
        { label: '>食品>なべ' },
        { label: '>食品>お菓子' },
        { label: '>食品>お菓子>アイス' }
      ]
    );
  }

  async unselectCategory(selectedCategories: Category[]) {
    return faker.helpers.arrayElement(selectedCategories);
  }

  async fillStatus(): Promise<void>;
  async fillStatus(status?: '公開' | '非公開') {
    status = status ?? '公開';
    await this.status.getByLabel(status, { exact: true }).click();
  }

  async fillProductStatus(): Promise<void>;
  async fillProductStatus(productStatus?: string[]) {
    productStatus = productStatus ?? faker.helpers.arrayElements(['NEW', '残りわずか', 'ポイント２倍', 'オススメ', '限定品']);
    for (const status of productStatus) {
      await this.productStatus.getByLabel(status, { exact: true }).click();
    }
  }

  async fillProductType(): Promise<void>;
  async fillProductType(productType?: '通常商品' | 'ダウンロード商品') {
    productType = productType ?? '通常商品';
    await this.productType.getByLabel(productType, { exact: true }).click();
  }

  async fillStock(): Promise<void>;
  async fillStock(stock?: number) {
    stock = stock ?? faker.number.int({ min: 1, max: 999999 });
    await this.stock.fill(String(stock));
    if (stock > 500000) {
      await this.stockUnlimited.check();
    }
  }

  async fillPointRate(): Promise<void>;
  async fillPointRate(pointRate?: number) {
    pointRate = pointRate ?? faker.number.int({ min: 0, max: 999 });
    await this.pointRate.fill(String(pointRate));
  }

  async fillDelivDate(): Promise<void>;
  async fillDelivDate(delivDate?: '即日' | '1～2日後' | '3～4日後' | '1週間以降' | '2週間以降' | '3週間以降' | '1ヶ月以降' | '2ヶ月以降' | 'お取り寄せ(商品入荷後)') {
    delivDate = delivDate ?? faker.helpers.arrayElement(['即日', '1～2日後', '3～4日後', '1週間以降', '2週間以降', '3週間以降', '1ヶ月以降', '2ヶ月以降', 'お取り寄せ(商品入荷後)']);
    await this.delivDate.selectOption({ label: delivDate });
  }

  async fillSaleLimit(): Promise<void>;
  async fillSaleLimit(saleLimit?: number) {
    saleLimit = saleLimit ?? faker.number.int({ min: 1, max: 999999 });
    await this.saleLimit.fill(String(saleLimit > 500000 ? '' : saleLimit));
  }

  async fillComment3(): Promise<void>;
  async fillComment3(comment3?: string) {
    comment3 = comment3 ?? faker.lorem.words(5);
    await this.comment3.fill(comment3.split(' ').join(','));
  }

  async uploadMainLargeImage(): Promise<void>;
  async uploadMainLargeImage(filepath?: string): Promise<void> {
    const fileChooserPromise = this.page.waitForEvent('filechooser');
    await this.page.locator('input[name=main_large_image]').click();
    const fileChooser = await fileChooserPromise;
    await fileChooser.setFiles(filepath ?? path.join(__dirname, '..', '..', '..', 'fixtures', 'images', 'main.jpg'));
    await this.mainLargeImage.click();
  }

  async fillSubComments(): Promise<void> {
    if (await this.subTitles[1].isHidden()) {
      await this.page.getByRole('link', { name: 'サブ情報表示/非表示' }).click();
    }
    for (let i = 1; i <= 5; i++) {
      await this.subTitles[i].fill(faker.lorem.words(3));
      await this.subComments[i].fill(faker.lorem.sentence());
      await this.uploadSubLargeImage(i);
    }
  }

  async uploadSubLargeImage(index: number, filepath?: string): Promise<void> {
    const fileChooserPromise = this.page.waitForEvent('filechooser');
    await this.page.locator(`input[name=sub_large_image${index}]`).click();
    const fileChooser = await fileChooserPromise;
    await fileChooser.setFiles(filepath ?? path.join(__dirname, '..', '..', '..', 'fixtures', 'images', 'main.jpg'));
    await this.subLargeImages[index].click();
  }

  async fillRecommends(): Promise<void> {
    if (await this.recommendChangeButtons[1].isHidden()) {
      await this.page.getByRole('link', { name: '関連商品表示/非表示' }).click();
    }
    await this.fillRecommend(1, 'おなべ');
    await this.fillRecommend(2, 'おなべレシピ');
    await this.fillRecommend(3, 'アイスクリーム');
    await this.recommendDeletes[2].check();
  }

  async fillRecommend(index: number, productName: string): Promise<void> {
    const popupPromise = this.page.waitForEvent('popup');
    await this.recommendChangeButtons[index].click();
    const popup = await popupPromise;
    await popup.getByRole('row', { name: '商品名' }).getByRole('textbox').fill(productName);
    await popup.getByRole('link', { name: '検索を開始' }).click();
    await popup.locator('table.list').getByRole('row').nth(1).getByRole('link', { name: '決定' }).click();
    await this.recommendComments[index].fill(faker.lorem.sentence());
  }
}
