const { chromium } = require('@playwright/test');

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({ ignoreHTTPSErrors: true });
  const page = await context.newPage();

  // Login
  await page.goto('https://localhost:4430/admin/');
  await page.getByRole('textbox', { name: 'ログインID' }).fill('admin');
  await page.getByRole('textbox', { name: 'パスワード' }).fill('password');
  await page.getByRole('button', { name: 'ログイン' }).click();

  // Go to product page
  await page.goto('https://localhost:4430/admin/products/product.php');
  await page.waitForLoadState('networkidle');

  // Get HTML for status row
  const statusRow = page.getByRole('row', { name: '公開・非公開' });
  const statusCell = statusRow.getByRole('cell').nth(1);
  const html = await statusCell.innerHTML();

  console.log('=== Status HTML ===');
  console.log(html);

  // Get HTML for product status row
  const productStatusRow = page.getByRole('row', { name: '商品ステータス' });
  const productStatusCell = productStatusRow.getByRole('cell').nth(1);
  const html2 = await productStatusCell.innerHTML();

  console.log('\n=== Product Status HTML ===');
  console.log(html2);

  await browser.close();
})();
