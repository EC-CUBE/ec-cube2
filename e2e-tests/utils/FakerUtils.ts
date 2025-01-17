import { faker } from '@faker-js/faker/locale/ja';
import * as path from "path";

export class FakerUtils {
  static createEmail() {
    return faker.helpers.fake(String(Date.now()) + '.{{internet.exampleEmail}}').toLowerCase();
  }
  static dummyImage() {
    return path.join(__dirname, '..', 'fixtures', 'images', 'main.jpg');
  }
}
