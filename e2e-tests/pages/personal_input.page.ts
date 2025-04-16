import { Locator, Page } from '@playwright/test';
import { faker } from '@faker-js/faker/locale/ja';
import { FakerUtils } from '../utils/FakerUtils';
import { ECCUBE_DEFAULT_PASS } from '../config/default.config';
import { addYears } from 'date-fns';

export class PersonalInputPage {
  readonly page: Page;
  readonly url?: string;

  readonly name01: Locator;
  readonly name02: Locator;
  readonly kana01: Locator;
  readonly kana02: Locator;
  readonly companyName: Locator;
  readonly zip01: Locator;
  readonly zip02: Locator;
  readonly pref: Locator;
  readonly addr01: Locator;
  readonly addr02: Locator;
  readonly tel01: Locator;
  readonly tel02: Locator;
  readonly tel03: Locator;
  readonly fax01: Locator;
  readonly fax02: Locator;
  readonly fax03: Locator;
  readonly email: Locator;
  readonly email02: Locator;
  readonly password: Locator;
  readonly password02: Locator;
  readonly sex: Locator;
  readonly job: Locator;
  readonly birthYear: Locator;
  readonly birthMonth: Locator;
  readonly birthDay: Locator;
  readonly reminder: Locator;
  readonly reminderAnswer: Locator;
  readonly mailmagaFlg: Locator;
  readonly emailAddress: string;

  constructor (page: Page, emailAddress?: string, url?: string, prefix?: string) {
    this.page = page;
    this.url = url;
    this.emailAddress = emailAddress ?? FakerUtils.createEmail();
    prefix = prefix ?? '';
    this.name01 = page.locator(`input[name=${prefix}name01]`);
    this.name02 = page.locator(`input[name=${prefix}name02]`);
    this.kana01 = page.locator(`input[name=${prefix}kana01]`);
    this.kana02 = page.locator(`input[name=${prefix}kana02]`);
    this.companyName = page.locator(`input[name=${prefix}company_name]`);
    this.pref = page.locator(`select[name=${prefix}pref]`);
    this.zip01 = page.locator(`input[name=${prefix}zip01]`);
    this.zip02 = page.locator(`input[name=${prefix}zip02]`);
    this.addr01 = page.locator(`input[name=${prefix}addr01]`);
    this.addr02 = page.locator(`input[name=${prefix}addr02]`);
    this.tel01 = page.locator(`input[name=${prefix}tel01]`);
    this.tel02 = page.locator(`input[name=${prefix}tel02]`);
    this.tel03 = page.locator(`input[name=${prefix}tel03]`);
    this.fax01 = page.locator(`input[name=${prefix}fax01]`);
    this.fax02 = page.locator(`input[name=${prefix}fax02]`);
    this.fax03 = page.locator(`input[name=${prefix}fax03]`);
    this.email = page.locator(`input[name=${prefix}email]`);
    this.email02 = page.locator(`input[name=${prefix}email02]`);
    this.password = page.locator(`input[name=${prefix}password]`);
    this.password02 = page.locator(`input[name=${prefix}password02]`);
    this.sex = page.locator(`input[name=${prefix}sex]`);
    this.job = page.locator(`select[name=${prefix}job]`);
    this.birthYear = page.locator(`select[name=${prefix}year]`);
    this.birthMonth = page.locator(`select[name=${prefix}month]`);
    this.birthDay = page.locator(`select[name=${prefix}day]`);
    this.reminder = page.locator(`select[name=reminder]`);
    this.reminderAnswer = page.locator(`input[name=reminder_answer]`);
    this.mailmagaFlg = page.locator(`input[name=mailmaga_flg]`);
  }

  fillName(): Promise<void>;
  async fillName (name01?: string, name02?: string, kana01?: string, kana02?: string) {
    name01 = name01 ?? faker.person.lastName();
    name02 = name02 ?? faker.person.firstName();
    kana01 = kana01 ?? 'イシ';
    kana02 = kana02 ?? 'キュウブ';
    await this.name01.fill(name01);
    await this.name02.fill(name02);
    await this.kana01.fill(kana01);
    await this.kana02.fill(kana02);
  }

  fillCompany(): Promise<void>;
  async fillCompany (companyName?: string) {
    companyName = companyName ?? faker.company.name();
    await this.companyName.fill(companyName);
  }

  fillAddress(): Promise<void>;
  async fillAddress (zip01?: number, zip02?: number, pref?: string, addr01?: string, addr02?: string) {
    await this.zip01.fill(String(zip01 ?? faker.location.zipCode('###')));
    await this.zip02.fill(String(zip02 ?? faker.location.zipCode('####')));
    await this.pref.selectOption({ label: pref ?? faker.location.state() });
    await this.addr01.fill(addr01 ?? faker.location.city());
    await this.addr02.fill(addr02 ?? faker.location.street());
  }

  fillTel(): Promise<void>;
  async fillTel (tel01?: number | string, tel02?: number, tel03?: number) {
    await this.tel01.fill(String(tel01 ?? faker.string.numeric(3)));
    await this.tel02.fill(String(tel02 ?? faker.string.numeric(3)));
    await this.tel03.fill(String(tel03 ?? faker.string.numeric(3)));
  }

  fillFax(): Promise<void>;
  async fillFax (fax01?: number | string, fax02?: number, fax03?: number) {
    await this.fax01.fill(String(fax01 ?? faker.string.numeric(3)));
    await this.fax02.fill(String(fax02 ?? faker.string.numeric(3)));
    await this.fax03.fill(String(fax03 ?? faker.string.numeric(3)));
  }

  fillEmail(): Promise<void>;
  async fillEmail (emailAddress?: string) {
    emailAddress = emailAddress ?? this.emailAddress;
    await this.email.fill(emailAddress);
    await this.email02.fill(emailAddress);
  }

  fillPassword(): Promise<void>;
  async fillPassword (password?: string) {
    password = password ?? ECCUBE_DEFAULT_PASS;
    await this.password.fill(password);
    await this.password02.fill(password);
  }

  fillPersonalInfo(): Promise<void>;
  async fillPersonalInfo (sex?:number, job?:number, birth?:Date) {
    sex = sex ?? faker.number.int({ min: 1, max: 2 });
    await this.sex.and(this.page.locator(`[value="${String(sex)}"]`)).check();
    job = job ?? faker.number.int({ min: 1, max: 18 });
    await this.job.selectOption({ value: String(job) });
    birth = birth ?? faker.date.past({ years: 20, refDate: addYears(new Date(), -20).toISOString() });
    await this.birthYear.selectOption({ value: String(birth.getFullYear()) });
    await this.birthMonth.selectOption({ value: String(birth.getMonth() + 1) });
    await this.birthDay.selectOption({ value: String(birth.getDate()) });
  }

  fillReminder(): Promise<void>;
  async fillReminder (reminder?: number, reminderAnswer?: string) {
    reminder = reminder ?? faker.number.int({ min: 1, max: 7 });
    await this.reminder.selectOption({ value: String(reminder) });
    reminderAnswer = reminderAnswer ?? faker.lorem.word();
    await this.reminderAnswer.fill(reminderAnswer);
  }

  fillMailmagaFlg(): Promise<void>;
  async fillMailmagaFlg (mailmagaFlg?: number) {
    mailmagaFlg = mailmagaFlg ?? faker.number.int({ min: 1, max: 3 });
    await this.mailmagaFlg.and(this.page.locator(`[value="${String(mailmagaFlg)}"]`)).check();
  }
}
