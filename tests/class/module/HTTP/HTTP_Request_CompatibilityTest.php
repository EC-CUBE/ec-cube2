<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/data/module/HTTP/Request.php';
require_once __DIR__.'/Request_Legacy.php';

/**
 * HTTP_Request compatibility tests
 *
 * Compares the new Guzzle-based implementation with the legacy implementation
 * to ensure backward compatibility of the API.
 */
class HTTP_Request_CompatibilityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test constructor defaults are the same
     */
    public function testConstructorDefaults()
    {
        $url = 'http://example.com/path';
        $legacy = new HTTP_Request_Legacy($url);
        $new = new HTTP_Request($url);

        $this->assertEquals($legacy->_method, $new->_method, '_method mismatch');
        $this->assertEquals($legacy->_http, $new->_http, '_http mismatch');
        $this->assertEquals($legacy->_allowRedirects, $new->_allowRedirects, '_allowRedirects mismatch');
        $this->assertEquals($legacy->_maxRedirects, $new->_maxRedirects, '_maxRedirects mismatch');
        $this->assertEquals($legacy->_useBrackets, $new->_useBrackets, '_useBrackets mismatch');
        $this->assertEquals($legacy->_saveBody, $new->_saveBody, '_saveBody mismatch');
    }

    /**
     * Test constructor with parameters
     */
    public function testConstructorWithParams()
    {
        $url = 'http://example.com/path';
        $params = [
            'method' => HTTP_REQUEST_METHOD_POST,
            'http' => HTTP_REQUEST_HTTP_VER_1_0,
            'timeout' => 30,
            'allowRedirects' => true,
            'maxRedirects' => 10,
            'useBrackets' => false,
        ];

        $legacy = new HTTP_Request_Legacy($url, $params);
        $new = new HTTP_Request($url, $params);

        $this->assertEquals($legacy->_method, $new->_method);
        $this->assertEquals($legacy->_http, $new->_http);
        $this->assertEquals($legacy->_timeout, $new->_timeout);
        $this->assertEquals($legacy->_allowRedirects, $new->_allowRedirects);
        $this->assertEquals($legacy->_maxRedirects, $new->_maxRedirects);
        $this->assertEquals($legacy->_useBrackets, $new->_useBrackets);
    }

    /**
     * Test getUrl returns the same value
     */
    public function testGetUrl()
    {
        $url = 'http://example.com/path?foo=bar';

        $legacy = new HTTP_Request_Legacy($url);
        $new = new HTTP_Request($url);

        $this->assertEquals($legacy->getUrl(), $new->getUrl());
    }

    /**
     * Test setURL sets the same values
     */
    public function testSetUrl()
    {
        $legacy = new HTTP_Request_Legacy();
        $new = new HTTP_Request();

        $legacy->setURL('https://example.com/new-path');
        $new->setURL('https://example.com/new-path');

        $this->assertEquals($legacy->getUrl(), $new->getUrl());
    }

    /**
     * Test setURL with authentication in URL
     */
    public function testSetUrlWithAuth()
    {
        $legacy = new HTTP_Request_Legacy();
        $new = new HTTP_Request();

        $legacy->setURL('https://user:pass@example.com/path');
        $new->setURL('https://user:pass@example.com/path');

        $this->assertEquals($legacy->_user, $new->_user);
        $this->assertEquals($legacy->_pass, $new->_pass);
    }

    /**
     * Test setMethod
     */
    public function testSetMethod()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $methods = [
            HTTP_REQUEST_METHOD_GET,
            HTTP_REQUEST_METHOD_POST,
            HTTP_REQUEST_METHOD_PUT,
            HTTP_REQUEST_METHOD_DELETE,
            HTTP_REQUEST_METHOD_HEAD,
        ];

        foreach ($methods as $method) {
            $legacy->setMethod($method);
            $new->setMethod($method);
            $this->assertEquals($legacy->_method, $new->_method);
        }
    }

    /**
     * Test setHttpVer
     */
    public function testSetHttpVer()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $legacy->setHttpVer(HTTP_REQUEST_HTTP_VER_1_0);
        $new->setHttpVer(HTTP_REQUEST_HTTP_VER_1_0);

        $this->assertEquals($legacy->_http, $new->_http);
    }

    /**
     * Test addHeader
     */
    public function testAddHeader()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $legacy->addHeader('X-Custom-Header', 'test-value');
        $new->addHeader('X-Custom-Header', 'test-value');

        $this->assertEquals(
            $legacy->_requestHeaders['x-custom-header'],
            $new->_requestHeaders['x-custom-header']
        );
    }

    /**
     * Test removeHeader
     */
    public function testRemoveHeader()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $legacy->addHeader('X-Custom-Header', 'test-value');
        $new->addHeader('X-Custom-Header', 'test-value');

        $legacy->removeHeader('X-Custom-Header');
        $new->removeHeader('X-Custom-Header');

        $this->assertEquals(
            isset($legacy->_requestHeaders['x-custom-header']),
            isset($new->_requestHeaders['x-custom-header'])
        );
    }

    /**
     * Test setBasicAuth
     */
    public function testSetBasicAuth()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $legacy->setBasicAuth('user', 'pass');
        $new->setBasicAuth('user', 'pass');

        $this->assertEquals($legacy->_user, $new->_user);
        $this->assertEquals($legacy->_pass, $new->_pass);
        $this->assertEquals(
            $legacy->_requestHeaders['authorization'],
            $new->_requestHeaders['authorization']
        );
    }

    /**
     * Test setProxy
     */
    public function testSetProxy()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $legacy->setProxy('proxy.example.com', 8080, 'proxyuser', 'proxypass');
        $new->setProxy('proxy.example.com', 8080, 'proxyuser', 'proxypass');

        $this->assertEquals($legacy->_proxy_host, $new->_proxy_host);
        $this->assertEquals($legacy->_proxy_port, $new->_proxy_port);
        $this->assertEquals($legacy->_proxy_user, $new->_proxy_user);
        $this->assertEquals($legacy->_proxy_pass, $new->_proxy_pass);
    }

    /**
     * Test addQueryString
     */
    public function testAddQueryString()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com/path');
        $new = new HTTP_Request('http://example.com/path');

        $legacy->addQueryString('foo', 'bar');
        $new->addQueryString('foo', 'bar');

        $this->assertEquals($legacy->getUrl(), $new->getUrl());
    }

    /**
     * Test addPostData
     */
    public function testAddPostData()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $legacy->addPostData('foo', 'bar');
        $new->addPostData('foo', 'bar');

        $this->assertEquals($legacy->_postData, $new->_postData);
    }

    /**
     * Test addPostData with array
     */
    public function testAddPostDataArray()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $legacy->addPostData('items', ['a', 'b', 'c']);
        $new->addPostData('items', ['a', 'b', 'c']);

        $this->assertEquals($legacy->_postData, $new->_postData);
    }

    /**
     * Test addPostDataArray method
     */
    public function testAddPostDataArrayMethod()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $data = ['foo' => 'bar', 'baz' => 'qux'];

        $legacy->addPostDataArray($data);
        $new->addPostDataArray($data);

        $this->assertEquals($legacy->_postData, $new->_postData);
    }

    /**
     * Test setBody
     */
    public function testSetBody()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $body = '{"key": "value"}';

        $legacy->setBody($body);
        $new->setBody($body);

        $this->assertEquals($legacy->_body, $new->_body);
    }

    /**
     * Test addCookie
     */
    public function testAddCookie()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $legacy->addCookie('session', 'abc123');
        $new->addCookie('session', 'abc123');

        $this->assertEquals(
            $legacy->_requestHeaders['cookie'],
            $new->_requestHeaders['cookie']
        );
    }

    /**
     * Test multiple cookies
     */
    public function testMultipleCookies()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $legacy->addCookie('session', 'abc123');
        $new->addCookie('session', 'abc123');

        $legacy->addCookie('user', 'john');
        $new->addCookie('user', 'john');

        $this->assertEquals(
            $legacy->_requestHeaders['cookie'],
            $new->_requestHeaders['cookie']
        );
    }

    /**
     * Test clearCookies
     */
    public function testClearCookies()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $legacy->addCookie('session', 'abc123');
        $new->addCookie('session', 'abc123');

        $legacy->clearCookies();
        $new->clearCookies();

        $this->assertEquals(
            isset($legacy->_requestHeaders['cookie']),
            isset($new->_requestHeaders['cookie'])
        );
    }

    /**
     * Test clearPostData
     */
    public function testClearPostData()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $legacy->addPostData('foo', 'bar');
        $new->addPostData('foo', 'bar');

        $legacy->clearPostData();
        $new->clearPostData();

        $this->assertEquals($legacy->_postData, $new->_postData);
    }

    /**
     * Test _generateHostHeader for various URLs
     */
    public function hostHeaderProvider(): array
    {
        return [
            'http standard port' => ['http://example.com/path'],
            'http non-standard port' => ['http://example.com:8080/path'],
            'https standard port' => ['https://example.com/path'],
            'https non-standard port' => ['https://example.com:8443/path'],
        ];
    }

    /**
     * @dataProvider hostHeaderProvider
     */
    public function testGenerateHostHeader(string $url)
    {
        $legacy = new HTTP_Request_Legacy($url);
        $new = new HTTP_Request($url);

        $this->assertEquals(
            $legacy->_generateHostHeader(),
            $new->_generateHostHeader(),
            "_generateHostHeader() mismatch for $url"
        );
    }

    /**
     * Test _flattenArray
     */
    public function testFlattenArray()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        // Simple value
        $this->assertEquals(
            $legacy->_flattenArray('name', 'value'),
            $new->_flattenArray('name', 'value')
        );

        // Nested array
        $data = ['a' => '1', 'b' => '2'];
        $this->assertEquals(
            $legacy->_flattenArray('data', $data),
            $new->_flattenArray('data', $data)
        );

        // Deeply nested array
        $deepData = ['level1' => ['level2' => 'value']];
        $this->assertEquals(
            $legacy->_flattenArray('data', $deepData),
            $new->_flattenArray('data', $deepData)
        );
    }

    /**
     * Test _flattenArray without brackets
     */
    public function testFlattenArrayWithoutBrackets()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com', ['useBrackets' => false]);
        $new = new HTTP_Request('http://example.com', ['useBrackets' => false]);

        $data = ['a' => '1', 'b' => '2'];
        $this->assertEquals(
            $legacy->_flattenArray('data', $data),
            $new->_flattenArray('data', $data)
        );
    }

    /**
     * Test _arrayMapRecursive
     */
    public function testArrayMapRecursive()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        // Simple value
        $this->assertEquals(
            $legacy->_arrayMapRecursive('strtoupper', 'hello'),
            $new->_arrayMapRecursive('strtoupper', 'hello')
        );

        // Array
        $data = ['hello', 'world'];
        $this->assertEquals(
            $legacy->_arrayMapRecursive('strtoupper', $data),
            $new->_arrayMapRecursive('strtoupper', $data)
        );

        // Nested array
        $nestedData = ['a' => 'hello', 'b' => ['c' => 'world']];
        $this->assertEquals(
            $legacy->_arrayMapRecursive('strtoupper', $nestedData),
            $new->_arrayMapRecursive('strtoupper', $nestedData)
        );
    }

    /**
     * Test response methods return same value when no response
     */
    public function testResponseMethodsWithNoResponse()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        $this->assertEquals($legacy->getResponseCode(), $new->getResponseCode());
        $this->assertEquals($legacy->getResponseReason(), $new->getResponseReason());
        $this->assertEquals($legacy->getResponseBody(), $new->getResponseBody());
        $this->assertEquals($legacy->getResponseCookies(), $new->getResponseCookies());
        $this->assertEquals($legacy->getResponseHeader(), $new->getResponseHeader());
        $this->assertEquals($legacy->getResponseHeader('content-type'), $new->getResponseHeader('content-type'));
    }

    /**
     * Test default headers are the same
     */
    public function testDefaultHeaders()
    {
        $legacy = new HTTP_Request_Legacy('http://example.com');
        $new = new HTTP_Request('http://example.com');

        // User-Agent header
        $this->assertEquals(
            $legacy->_requestHeaders['user-agent'],
            $new->_requestHeaders['user-agent'],
            'User-Agent header mismatch'
        );

        // Connection header
        $this->assertEquals(
            $legacy->_requestHeaders['connection'],
            $new->_requestHeaders['connection'],
            'Connection header mismatch'
        );

        // Host header
        $this->assertEquals(
            $legacy->_requestHeaders['host'],
            $new->_requestHeaders['host'],
            'Host header mismatch'
        );
    }
}
