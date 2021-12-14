import { Capabilities, ProxyConfig } from 'selenium-webdriver'

export const ZapProxyHost = process.env.ZAP_PROXY_HOST || 'localhost:8090';
const proxy : ProxyConfig = {
  proxyType: 'manual',
  httpProxy: ZapProxyHost,
  sslProxy: ZapProxyHost
};

export const SeleniumCapabilities = Capabilities.chrome();
SeleniumCapabilities.set('chromeOptions', {
  args: [
    '--headless',
    '--disable-gpu',
    '--window-size=1024,768',
    '--no-sandbox'
  ],
  w3c: false
})
  .setAcceptInsecureCerts(true)
  .setProxy(proxy);
