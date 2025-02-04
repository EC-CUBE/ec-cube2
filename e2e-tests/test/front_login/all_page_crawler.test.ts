import { test, expect } from '../../fixtures/front_login/mypage_login.fixture';
import { EndpointReader } from '../../utils/EndpointReader';
import { endpointTests } from '../../utils/EndpointTests';

/**
 * endpoints.csv を読み込み、指定されたエンドポイントのテストを実行します。
 */
const endpointReader = new EndpointReader();
test('/mypage 以下のエンポイントを確認します', async ({ mypageLoginPage, page }) => {
  const endpoints = endpointReader.filter('/mypage');
  for (const endpoint of endpoints) {
    await endpointTests(page, endpoint, null);
  }
});

test('/abouts 以下のエンポイントを確認します', async ({ mypageLoginPage, page }) => {
  const endpoints = endpointReader.filter('/abouts');
  for (const endpoint of endpoints) {
    await endpointTests(page, endpoint, null);
  }
});

test('/guide 以下のエンポイントを確認します', async ({ mypageLoginPage, page }) => {
  const endpoints = endpointReader.filter('/guide');
  for (const endpoint of endpoints) {
    await endpointTests(page, endpoint, null);
  }
});

test('/order 以下のエンポイントを確認します', async ({ mypageLoginPage, page }) => {
  const endpoints = endpointReader.filter('/order');
  for (const endpoint of endpoints) {
    await endpointTests(page, endpoint, null);
  }
});
