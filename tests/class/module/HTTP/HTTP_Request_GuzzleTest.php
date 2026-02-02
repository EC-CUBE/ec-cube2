<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/data/module/HTTP/Request.php';

/**
 * HTTP_Request compatibility tests
 *
 * Tests to verify backward compatibility of the Guzzle-based HTTP_Request implementation.
 */
class HTTP_Request_GuzzleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test constants are defined
     */
    public function testConstantsDefined()
    {
        $this->assertEquals('GET', HTTP_REQUEST_METHOD_GET);
        $this->assertEquals('HEAD', HTTP_REQUEST_METHOD_HEAD);
        $this->assertEquals('POST', HTTP_REQUEST_METHOD_POST);
        $this->assertEquals('PUT', HTTP_REQUEST_METHOD_PUT);
        $this->assertEquals('DELETE', HTTP_REQUEST_METHOD_DELETE);
        $this->assertEquals('OPTIONS', HTTP_REQUEST_METHOD_OPTIONS);
        $this->assertEquals('TRACE', HTTP_REQUEST_METHOD_TRACE);

        $this->assertEquals(1, HTTP_REQUEST_ERROR_FILE);
        $this->assertEquals(2, HTTP_REQUEST_ERROR_URL);
        $this->assertEquals(4, HTTP_REQUEST_ERROR_PROXY);
        $this->assertEquals(8, HTTP_REQUEST_ERROR_REDIRECTS);
        $this->assertEquals(16, HTTP_REQUEST_ERROR_RESPONSE);
        $this->assertEquals(512, HTTP_REQUEST_ERROR_SSRF);

        $this->assertEquals('1.0', HTTP_REQUEST_HTTP_VER_1_0);
        $this->assertEquals('1.1', HTTP_REQUEST_HTTP_VER_1_1);

        $this->assertTrue(defined('HTTP_REQUEST_MBSTRING'));
    }

    /**
     * Test constructor with URL
     */
    public function testConstructorWithUrl()
    {
        $request = new HTTP_Request('https://example.com/path');

        $this->assertEquals('https://example.com/path', $request->getUrl());
        $this->assertEquals(HTTP_REQUEST_METHOD_GET, $request->_method);
        $this->assertEquals(HTTP_REQUEST_HTTP_VER_1_1, $request->_http);
    }

    /**
     * Test constructor with parameters
     */
    public function testConstructorWithParams()
    {
        $request = new HTTP_Request('https://example.com', [
            'method' => HTTP_REQUEST_METHOD_POST,
            'timeout' => 30,
            'allowRedirects' => true,
            'maxRedirects' => 5,
        ]);

        $this->assertEquals(HTTP_REQUEST_METHOD_POST, $request->_method);
        $this->assertEquals(30, $request->_timeout);
        $this->assertTrue($request->_allowRedirects);
        $this->assertEquals(5, $request->_maxRedirects);
    }

    /**
     * Test setURL
     */
    public function testSetUrl()
    {
        $request = new HTTP_Request();
        $request->setURL('https://example.com/new-path');

        $this->assertEquals('https://example.com/new-path', $request->getUrl());
    }

    /**
     * Test setURL with auth in URL
     */
    public function testSetUrlWithAuth()
    {
        $request = new HTTP_Request();
        $request->setURL('https://user:pass@example.com/path');

        $this->assertEquals('user', $request->_user);
        $this->assertEquals('pass', $request->_pass);
    }

    /**
     * Test setMethod
     */
    public function testSetMethod()
    {
        $request = new HTTP_Request('https://example.com');
        $request->setMethod(HTTP_REQUEST_METHOD_POST);

        $this->assertEquals(HTTP_REQUEST_METHOD_POST, $request->_method);
    }

    /**
     * Test setHttpVer
     */
    public function testSetHttpVer()
    {
        $request = new HTTP_Request('https://example.com');
        $request->setHttpVer(HTTP_REQUEST_HTTP_VER_1_0);

        $this->assertEquals(HTTP_REQUEST_HTTP_VER_1_0, $request->_http);
    }

    /**
     * Test addHeader and removeHeader
     */
    public function testAddAndRemoveHeader()
    {
        $request = new HTTP_Request('https://example.com');
        $request->addHeader('X-Custom-Header', 'value');

        $this->assertEquals('value', $request->_requestHeaders['x-custom-header']);

        $request->removeHeader('X-Custom-Header');
        $this->assertArrayNotHasKey('x-custom-header', $request->_requestHeaders);
    }

    /**
     * Test setBasicAuth
     */
    public function testSetBasicAuth()
    {
        $request = new HTTP_Request('https://example.com');
        $request->setBasicAuth('user', 'pass');

        $this->assertEquals('user', $request->_user);
        $this->assertEquals('pass', $request->_pass);
        $this->assertStringContainsString('Basic', $request->_requestHeaders['authorization']);
    }

    /**
     * Test setProxy
     */
    public function testSetProxy()
    {
        $request = new HTTP_Request('https://example.com');
        $request->setProxy('proxy.example.com', 8080, 'proxyuser', 'proxypass');

        $this->assertEquals('proxy.example.com', $request->_proxy_host);
        $this->assertEquals(8080, $request->_proxy_port);
        $this->assertEquals('proxyuser', $request->_proxy_user);
        $this->assertEquals('proxypass', $request->_proxy_pass);
    }

    /**
     * Test addQueryString
     */
    public function testAddQueryString()
    {
        $request = new HTTP_Request('https://example.com/path');
        $request->addQueryString('foo', 'bar');

        $url = $request->getUrl();
        $this->assertStringContainsString('foo=bar', $url);
    }

    /**
     * Test addPostData
     */
    public function testAddPostData()
    {
        $request = new HTTP_Request('https://example.com');
        $request->setMethod(HTTP_REQUEST_METHOD_POST);
        $request->addPostData('foo', 'bar');

        $this->assertArrayHasKey('foo', $request->_postData);
        $this->assertEquals('bar', $request->_postData['foo']);
    }

    /**
     * Test addPostDataArray
     */
    public function testAddPostDataArray()
    {
        $request = new HTTP_Request('https://example.com');
        $request->setMethod(HTTP_REQUEST_METHOD_POST);
        $request->addPostDataArray(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertEquals('bar', $request->_postData['foo']);
        $this->assertEquals('qux', $request->_postData['baz']);
    }

    /**
     * Test setBody
     */
    public function testSetBody()
    {
        $request = new HTTP_Request('https://example.com');
        $request->setBody('{"key": "value"}');

        $this->assertEquals('{"key": "value"}', $request->_body);
    }

    /**
     * Test addCookie
     */
    public function testAddCookie()
    {
        $request = new HTTP_Request('https://example.com');
        $request->addCookie('session', 'abc123');

        $this->assertStringContainsString('session=abc123', $request->_requestHeaders['cookie']);
    }

    /**
     * Test multiple cookies
     */
    public function testMultipleCookies()
    {
        $request = new HTTP_Request('https://example.com');
        $request->addCookie('session', 'abc123');
        $request->addCookie('user', 'john');

        $cookies = $request->_requestHeaders['cookie'];
        $this->assertStringContainsString('session=abc123', $cookies);
        $this->assertStringContainsString('user=john', $cookies);
    }

    /**
     * Test clearCookies
     */
    public function testClearCookies()
    {
        $request = new HTTP_Request('https://example.com');
        $request->addCookie('session', 'abc123');
        $request->clearCookies();

        $this->assertArrayNotHasKey('cookie', $request->_requestHeaders);
    }

    /**
     * Test clearPostData
     */
    public function testClearPostData()
    {
        $request = new HTTP_Request('https://example.com');
        $request->addPostData('foo', 'bar');
        $request->clearPostData();

        $this->assertNull($request->_postData);
    }

    /**
     * Test sendRequest without URL returns error
     */
    public function testSendRequestWithoutUrl()
    {
        $request = new HTTP_Request();
        $result = $request->sendRequest();

        $this->assertTrue(PEAR::isError($result));
        $this->assertEquals(HTTP_REQUEST_ERROR_URL, $result->getCode());
    }

    /**
     * Test SSRF protection blocks private IPs
     */
    public function testSsrfProtectionBlocksPrivateIp()
    {
        $request = new HTTP_Request('http://127.0.0.1/path');
        $result = $request->sendRequest();

        $this->assertTrue(PEAR::isError($result));
        $this->assertEquals(HTTP_REQUEST_ERROR_SSRF, $result->getCode());
    }

    /**
     * Test SSRF protection blocks 10.x.x.x
     */
    public function testSsrfProtectionBlocks10Network()
    {
        $request = new HTTP_Request('http://10.0.0.1/path');
        $result = $request->sendRequest();

        $this->assertTrue(PEAR::isError($result));
        $this->assertEquals(HTTP_REQUEST_ERROR_SSRF, $result->getCode());
    }

    /**
     * Test SSRF protection blocks 192.168.x.x
     */
    public function testSsrfProtectionBlocks192168Network()
    {
        $request = new HTTP_Request('http://192.168.1.1/path');
        $result = $request->sendRequest();

        $this->assertTrue(PEAR::isError($result));
        $this->assertEquals(HTTP_REQUEST_ERROR_SSRF, $result->getCode());
    }

    /**
     * Test SSRF protection can be disabled
     */
    public function testSsrfProtectionCanBeDisabled()
    {
        $request = new HTTP_Request('http://127.0.0.1/nonexistent', [
            'ssrfProtection' => false,
            'timeout' => 1,
        ]);

        // Verify the property is set correctly
        $this->assertFalse($request->_ssrfProtection);

        // This should not return SSRF error (it may fail for other reasons)
        $result = $request->sendRequest();

        if (PEAR::isError($result)) {
            $this->assertNotEquals(HTTP_REQUEST_ERROR_SSRF, $result->getCode());
        } else {
            // If request succeeded or failed for non-SSRF reason, that's fine
            $this->assertTrue(true);
        }
    }

    /**
     * Test listener attach and detach
     */
    public function testListenerAttachDetach()
    {
        require_once 'HTTP/Request/Listener.php';

        $request = new HTTP_Request('https://example.com');
        $listener = new HTTP_Request_Listener();

        $result = $request->attach($listener);
        $this->assertTrue($result);
        $this->assertArrayHasKey($listener->getId(), $request->_listeners);

        $result = $request->detach($listener);
        $this->assertTrue($result);
        $this->assertArrayNotHasKey($listener->getId(), $request->_listeners);
    }

    /**
     * Test attach with non-listener returns false
     */
    public function testAttachNonListener()
    {
        $request = new HTTP_Request('https://example.com');
        $result = $request->attach(new stdClass());

        $this->assertFalse($result);
    }

    /**
     * Test detach with non-listener returns false
     */
    public function testDetachNonListener()
    {
        $request = new HTTP_Request('https://example.com');
        $result = $request->detach(new stdClass());

        $this->assertFalse($result);
    }

    /**
     * Test _flattenArray with simple value
     */
    public function testFlattenArraySimple()
    {
        $request = new HTTP_Request('https://example.com');
        $result = $request->_flattenArray('name', 'value');

        $this->assertEquals([['name', 'value']], $result);
    }

    /**
     * Test _flattenArray with nested array
     */
    public function testFlattenArrayNested()
    {
        $request = new HTTP_Request('https://example.com');
        $result = $request->_flattenArray('data', ['a' => '1', 'b' => '2']);

        $this->assertCount(2, $result);
        $this->assertEquals(['data[a]', '1'], $result[0]);
        $this->assertEquals(['data[b]', '2'], $result[1]);
    }

    /**
     * Test _flattenArray without brackets
     */
    public function testFlattenArrayWithoutBrackets()
    {
        $request = new HTTP_Request('https://example.com');
        $request->_useBrackets = false;
        $result = $request->_flattenArray('data', ['a' => '1', 'b' => '2']);

        $this->assertCount(2, $result);
        $this->assertEquals(['data', '1'], $result[0]);
        $this->assertEquals(['data', '2'], $result[1]);
    }

    /**
     * Test _generateHostHeader for HTTP
     */
    public function testGenerateHostHeaderHttp()
    {
        $request = new HTTP_Request('http://example.com/path');

        $host = $request->_generateHostHeader();
        $this->assertEquals('example.com', $host);
    }

    /**
     * Test _generateHostHeader for HTTP with non-standard port
     */
    public function testGenerateHostHeaderHttpNonStandardPort()
    {
        $request = new HTTP_Request('http://example.com:8080/path');

        $host = $request->_generateHostHeader();
        $this->assertEquals('example.com:8080', $host);
    }

    /**
     * Test _generateHostHeader for HTTPS
     */
    public function testGenerateHostHeaderHttps()
    {
        $request = new HTTP_Request('https://example.com/path');

        $host = $request->_generateHostHeader();
        $this->assertEquals('example.com', $host);
    }

    /**
     * Test _generateHostHeader for HTTPS with non-standard port
     */
    public function testGenerateHostHeaderHttpsNonStandardPort()
    {
        $request = new HTTP_Request('https://example.com:8443/path');

        $host = $request->_generateHostHeader();
        $this->assertEquals('example.com:8443', $host);
    }

    /**
     * Test default User-Agent header is set
     */
    public function testDefaultUserAgentHeader()
    {
        $request = new HTTP_Request('https://example.com');

        $this->assertArrayHasKey('user-agent', $request->_requestHeaders);
        $this->assertStringContainsString('PEAR HTTP_Request', $request->_requestHeaders['user-agent']);
    }

    /**
     * Test default Connection header is set to close
     */
    public function testDefaultConnectionHeader()
    {
        $request = new HTTP_Request('https://example.com');

        $this->assertEquals('close', $request->_requestHeaders['connection']);
    }

    /**
     * Test Accept-Encoding header is set for HTTP/1.1
     */
    public function testAcceptEncodingHeader()
    {
        if (!extension_loaded('zlib')) {
            $this->markTestSkipped('zlib extension not available');
        }

        $request = new HTTP_Request('https://example.com');

        $this->assertArrayHasKey('accept-encoding', $request->_requestHeaders);
        $this->assertStringContainsString('gzip', $request->_requestHeaders['accept-encoding']);
    }

    /**
     * Test addFile with non-existent file
     */
    public function testAddFileNonExistent()
    {
        $request = new HTTP_Request('https://example.com');
        $result = $request->addFile('upload', '/nonexistent/file.txt');

        $this->assertTrue(PEAR::isError($result));
        $this->assertEquals(HTTP_REQUEST_ERROR_FILE, $result->getCode());
    }

    /**
     * Test addFile with valid file
     */
    public function testAddFileValid()
    {
        $request = new HTTP_Request('https://example.com');
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');

        try {
            $result = $request->addFile('upload', $tempFile);
            $this->assertTrue($result);
            $this->assertArrayHasKey('upload', $request->_postFiles);
        } finally {
            unlink($tempFile);
        }
    }

    /**
     * Test addRawPostData (deprecated)
     */
    public function testAddRawPostData()
    {
        $request = new HTTP_Request('https://example.com');
        $request->addRawPostData('raw body content', true);

        $this->assertEquals('raw body content', $request->_body);
    }

    /**
     * Test SSL verify option
     */
    public function testSslVerifyOption()
    {
        $request = new HTTP_Request('https://example.com', [
            'sslVerify' => false,
        ]);

        $this->assertFalse($request->_sslVerify);
    }

    /**
     * Test response methods return false when no response
     */
    public function testResponseMethodsReturnFalseWithNoResponse()
    {
        $request = new HTTP_Request('https://example.com');

        $this->assertFalse($request->getResponseCode());
        $this->assertFalse($request->getResponseReason());
        $this->assertFalse($request->getResponseBody());
        $this->assertFalse($request->getResponseCookies());
    }

    /**
     * Test getResponseHeader returns empty array when no response
     */
    public function testGetResponseHeaderWithNoResponse()
    {
        $request = new HTTP_Request('https://example.com');

        $this->assertEquals([], $request->getResponseHeader());
        $this->assertFalse($request->getResponseHeader('content-type'));
    }

    /**
     * Test disconnect method exists and is callable
     */
    public function testDisconnect()
    {
        $request = new HTTP_Request('https://example.com');

        // Should not throw exception
        $request->disconnect();
        $this->assertTrue(true);
    }

    /**
     * Test actual HTTP GET request
     *
     * @group integration
     */
    public function testActualHttpGetRequest()
    {
        $request = new HTTP_Request('https://httpbin.org/get', [
            'timeout' => 10,
            'ssrfProtection' => false,
        ]);

        $result = $request->sendRequest();

        if (PEAR::isError($result)) {
            $this->markTestSkipped('Could not connect to httpbin.org: '.$result->getMessage());
        }

        $this->assertTrue($result);
        $this->assertEquals(200, $request->getResponseCode());
        $this->assertNotEmpty($request->getResponseBody());

        $body = json_decode($request->getResponseBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('url', $body);
    }

    /**
     * Test actual HTTP POST request
     *
     * @group integration
     */
    public function testActualHttpPostRequest()
    {
        $request = new HTTP_Request('https://httpbin.org/post', [
            'method' => HTTP_REQUEST_METHOD_POST,
            'timeout' => 10,
            'ssrfProtection' => false,
        ]);
        $request->addPostData('foo', 'bar');
        $request->addPostData('baz', 'qux');

        $result = $request->sendRequest();

        if (PEAR::isError($result)) {
            $this->markTestSkipped('Could not connect to httpbin.org: '.$result->getMessage());
        }

        $this->assertTrue($result);
        $this->assertEquals(200, $request->getResponseCode());

        $body = json_decode($request->getResponseBody(), true);
        $this->assertIsArray($body);
        $this->assertEquals('bar', $body['form']['foo'] ?? null);
        $this->assertEquals('qux', $body['form']['baz'] ?? null);
    }

    /**
     * Test actual HTTP request with redirect
     *
     * @group integration
     */
    public function testActualHttpRedirect()
    {
        $request = new HTTP_Request('https://httpbin.org/redirect/1', [
            'timeout' => 10,
            'ssrfProtection' => false,
            'allowRedirects' => true,
            'maxRedirects' => 5,
        ]);

        $result = $request->sendRequest();

        if (PEAR::isError($result)) {
            $this->markTestSkipped('Could not connect to httpbin.org: '.$result->getMessage());
        }

        $this->assertTrue($result);
        $this->assertEquals(200, $request->getResponseCode());
        // Redirect counter should be incremented
        $this->assertGreaterThanOrEqual(1, $request->_redirects);
    }

    /**
     * Test actual HTTP request returns correct headers
     *
     * @group integration
     */
    public function testActualHttpResponseHeaders()
    {
        $request = new HTTP_Request('https://httpbin.org/response-headers?X-Test-Header=test-value', [
            'timeout' => 10,
            'ssrfProtection' => false,
        ]);

        $result = $request->sendRequest();

        if (PEAR::isError($result)) {
            $this->markTestSkipped('Could not connect to httpbin.org: '.$result->getMessage());
        }

        $this->assertTrue($result);
        $this->assertEquals(200, $request->getResponseCode());
        $this->assertEquals('test-value', $request->getResponseHeader('x-test-header'));
    }

    /**
     * Test HTTP request with custom headers
     *
     * @group integration
     */
    public function testActualHttpRequestWithHeaders()
    {
        $request = new HTTP_Request('https://httpbin.org/headers', [
            'timeout' => 10,
            'ssrfProtection' => false,
        ]);
        $request->addHeader('X-Custom-Header', 'custom-value');

        $result = $request->sendRequest();

        if (PEAR::isError($result)) {
            $this->markTestSkipped('Could not connect to httpbin.org: '.$result->getMessage());
        }

        $this->assertTrue($result);
        $body = json_decode($request->getResponseBody(), true);
        $this->assertEquals('custom-value', $body['headers']['X-Custom-Header'] ?? null);
    }

    /**
     * Test HTTP 404 response
     *
     * @group integration
     */
    public function testActualHttp404Response()
    {
        $request = new HTTP_Request('https://httpbin.org/status/404', [
            'timeout' => 10,
            'ssrfProtection' => false,
        ]);

        $result = $request->sendRequest();

        if (PEAR::isError($result)) {
            $this->markTestSkipped('Could not connect to httpbin.org: '.$result->getMessage());
        }

        $this->assertTrue($result);
        $this->assertEquals(404, $request->getResponseCode());
    }
}
