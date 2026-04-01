<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/data/module/Net/URL.php';

/**
 * Net_URL compatibility tests
 *
 * Tests to verify backward compatibility of the Guzzle-based Net_URL implementation.
 */
class Net_URLTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test basic URL parsing
     */
    public function testBasicUrlParsing()
    {
        $url = new Net_URL('https://example.com/path/to/page.php');

        $this->assertEquals('https', $url->protocol);
        $this->assertEquals('example.com', $url->host);
        $this->assertEquals(443, $url->port);
        $this->assertEquals('/path/to/page.php', $url->path);
    }

    /**
     * Test URL with authentication
     */
    public function testUrlWithAuthentication()
    {
        $url = new Net_URL('https://user:pass@example.com:8080/path?foo=bar#anchor');

        $this->assertEquals('https', $url->protocol);
        $this->assertEquals('user', $url->user);
        $this->assertEquals('user', $url->username);
        $this->assertEquals('pass', $url->pass);
        $this->assertEquals('pass', $url->password);
        $this->assertEquals('example.com', $url->host);
        $this->assertEquals(8080, $url->port);
        $this->assertEquals('/path', $url->path);
        $this->assertEquals(['foo' => 'bar'], $url->querystring);
        $this->assertEquals('anchor', $url->anchor);
    }

    /**
     * Test standard port detection
     */
    public function testGetStandardPort()
    {
        $url = new Net_URL('http://example.com');

        $this->assertEquals(80, $url->getStandardPort('http'));
        $this->assertEquals(443, $url->getStandardPort('https'));
        $this->assertEquals(21, $url->getStandardPort('ftp'));
        $this->assertEquals(143, $url->getStandardPort('imap'));
        $this->assertEquals(993, $url->getStandardPort('imaps'));
        $this->assertEquals(110, $url->getStandardPort('pop3'));
        $this->assertEquals(995, $url->getStandardPort('pop3s'));
        $this->assertNull($url->getStandardPort('unknown'));
    }

    /**
     * Test getURL() method
     */
    public function testGetUrl()
    {
        $url = new Net_URL('https://example.com/path');

        $this->assertEquals('https://example.com/path', $url->getURL());
    }

    /**
     * Test getURL() with non-standard port
     */
    public function testGetUrlWithNonStandardPort()
    {
        $url = new Net_URL('https://example.com:8443/path');

        $this->assertEquals('https://example.com:8443/path', $url->getURL());
    }

    /**
     * Test getURL() with query string
     */
    public function testGetUrlWithQueryString()
    {
        $url = new Net_URL('https://example.com/path?foo=bar&baz=qux');

        $result = $url->getURL();
        $this->assertStringContainsString('foo=bar', $result);
        $this->assertStringContainsString('baz=qux', $result);
    }

    /**
     * Test addQueryString()
     */
    public function testAddQueryString()
    {
        $url = new Net_URL('https://example.com/path');
        $url->addQueryString('foo', 'bar');

        $this->assertEquals(['foo' => 'bar'], $url->querystring);
        $this->assertStringContainsString('foo=bar', $url->getURL());
    }

    /**
     * Test addQueryString() with special characters
     */
    public function testAddQueryStringWithSpecialChars()
    {
        $url = new Net_URL('https://example.com/path');
        $url->addQueryString('foo', 'bar baz');

        $this->assertEquals('bar%20baz', $url->querystring['foo']);
    }

    /**
     * Test addQueryString() with preencoded value
     */
    public function testAddQueryStringPreencoded()
    {
        $url = new Net_URL('https://example.com/path');
        $url->addQueryString('foo', 'already%20encoded', true);

        $this->assertEquals('already%20encoded', $url->querystring['foo']);
    }

    /**
     * Test addQueryString() with array value
     */
    public function testAddQueryStringWithArray()
    {
        $url = new Net_URL('https://example.com/path');
        $url->addQueryString('foo', ['a', 'b', 'c']);

        $this->assertEquals(['a', 'b', 'c'], $url->querystring['foo']);
    }

    /**
     * Test removeQueryString()
     */
    public function testRemoveQueryString()
    {
        $url = new Net_URL('https://example.com/path?foo=bar&baz=qux');
        $url->removeQueryString('foo');

        $this->assertArrayNotHasKey('foo', $url->querystring);
        $this->assertArrayHasKey('baz', $url->querystring);
    }

    /**
     * Test addRawQueryString()
     */
    public function testAddRawQueryString()
    {
        $url = new Net_URL('https://example.com/path');
        $url->addRawQueryString('foo=bar&baz=qux');

        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $url->querystring);
    }

    /**
     * Test getQueryString()
     */
    public function testGetQueryString()
    {
        $url = new Net_URL('https://example.com/path?foo=bar&baz=qux');

        $queryString = $url->getQueryString();
        $this->assertStringContainsString('foo=bar', $queryString);
        $this->assertStringContainsString('baz=qux', $queryString);
    }

    /**
     * Test setProtocol()
     */
    public function testSetProtocol()
    {
        $url = new Net_URL('http://example.com/path');
        $url->setProtocol('https');

        $this->assertEquals('https', $url->protocol);
        $this->assertEquals(443, $url->port);
    }

    /**
     * Test setProtocol() with custom port
     */
    public function testSetProtocolWithCustomPort()
    {
        $url = new Net_URL('http://example.com/path');
        $url->setProtocol('https', 8443);

        $this->assertEquals('https', $url->protocol);
        $this->assertEquals(8443, $url->port);
    }

    /**
     * Test resolvePath() - basic cases
     */
    public function testResolvePath()
    {
        $this->assertEquals('/foo/boo.php', Net_URL::resolvePath('/foo/bar/../boo.php'));
        $this->assertEquals('/boo.php', Net_URL::resolvePath('/foo/bar/../../boo.php'));
        $this->assertEquals('/foo/boo.php', Net_URL::resolvePath('/foo/bar/.././boo.php'));
    }

    /**
     * Test resolvePath() - double slashes
     */
    public function testResolvePathDoubleSlashes()
    {
        $this->assertEquals('/foo/boo.php', Net_URL::resolvePath('/foo//boo.php'));
    }

    /**
     * Test resolvePath() - current directory references
     */
    public function testResolvePathCurrentDir()
    {
        $this->assertEquals('/foo/bar/boo.php', Net_URL::resolvePath('/foo/./bar/./boo.php'));
    }

    /**
     * Test option handling
     */
    public function testOptions()
    {
        $url = new Net_URL('https://example.com/path');

        $this->assertFalse($url->getOption('encode_query_keys'));

        $url->setOption('encode_query_keys', true);
        $this->assertTrue($url->getOption('encode_query_keys'));
    }

    /**
     * Test getOption() returns false for unknown option
     */
    public function testGetOptionUnknown()
    {
        $url = new Net_URL('https://example.com/path');

        $this->assertFalse($url->getOption('unknown_option'));
    }

    /**
     * Test setOption() returns false for unknown option
     */
    public function testSetOptionUnknown()
    {
        $url = new Net_URL('https://example.com/path');

        $result = $url->setOption('unknown_option', 'value');
        $this->assertFalse($result);
    }

    /**
     * Test useBrackets option
     */
    public function testUseBrackets()
    {
        $url = new Net_URL('https://example.com/path', true);
        $url->addQueryString('items', ['a', 'b']);

        $queryString = $url->getQueryString();
        $this->assertStringContainsString('items[0]=a', $queryString);
        $this->assertStringContainsString('items[1]=b', $queryString);
    }

    /**
     * Test without brackets
     */
    public function testWithoutBrackets()
    {
        $url = new Net_URL('https://example.com/path', false);
        $url->addQueryString('items', ['a', 'b']);

        $queryString = $url->getQueryString();
        $this->assertStringContainsString('items=a', $queryString);
        $this->assertStringContainsString('items=b', $queryString);
    }

    /**
     * Test URL with fragment/anchor
     */
    public function testUrlWithAnchor()
    {
        $url = new Net_URL('https://example.com/path#section');

        $this->assertEquals('section', $url->anchor);
        $this->assertStringContainsString('#section', $url->getURL());
    }

    /**
     * Test URL reconstruction maintains all parts
     */
    public function testUrlReconstruction()
    {
        $originalUrl = 'https://user:pass@example.com:8443/path/to/page.php?foo=bar#anchor';
        $url = new Net_URL($originalUrl);

        $rebuilt = $url->getURL();

        $this->assertStringContainsString('https://', $rebuilt);
        $this->assertStringContainsString('user:pass@', $rebuilt);
        $this->assertStringContainsString('example.com:8443', $rebuilt);
        $this->assertStringContainsString('/path/to/page.php', $rebuilt);
        $this->assertStringContainsString('foo=bar', $rebuilt);
        $this->assertStringContainsString('#anchor', $rebuilt);
    }

    /**
     * Test URL with array query parameters
     */
    public function testArrayQueryParameters()
    {
        $url = new Net_URL('https://example.com/path?items[0]=a&items[1]=b');

        $this->assertIsArray($url->querystring['items']);
        $this->assertEquals('a', $url->querystring['items'][0]);
        $this->assertEquals('b', $url->querystring['items'][1]);
    }

    /**
     * Test empty path becomes /
     */
    public function testEmptyPathDefaultsToSlash()
    {
        $url = new Net_URL('https://example.com');

        $this->assertEquals('', $url->path);
    }

    /**
     * Test query string with null value
     */
    public function testQueryStringNullValue()
    {
        $url = new Net_URL('https://example.com/path?flag');

        $this->assertArrayHasKey('flag', $url->querystring);
        $this->assertNull($url->querystring['flag']);
    }
}
