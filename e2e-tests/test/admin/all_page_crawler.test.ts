import { test } from '../../fixtures/admin/admin_login.fixture';
import { EndpointReader } from '../../utils/EndpointReader';
import { endpointTests } from '../../utils/EndpointTests';

/**
 * endpoints.csv を読み込み、指定されたエンドポイントのテストを実行します。
 */
const endpointReader = new EndpointReader();
test('/admin 以下のエンポイントを確認します', async ({ adminLoginPage, page }) => {
  const endpoints = endpointReader.filter('/admin');
  for (const endpoint of endpoints) {
    await endpointTests(page, endpoint);
  }
});
