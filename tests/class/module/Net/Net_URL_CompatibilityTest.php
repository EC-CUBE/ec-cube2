<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/data/module/Net/URL.php';
require_once __DIR__.'/URL_Legacy.php';

/**
 * Net_URL compatibility tests
 *
 * Compares the new Guzzle-based implementation with the legacy implementation
 * to ensure backward compatibility.
 */
class Net_URL_CompatibilityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * URLs to test
     */
    public function urlProvider(): array
    {
        return [
            'simple http' => ['http://example.com'],
            'simple https' => ['https://example.com'],
            'with path' => ['https://example.com/path/to/page.php'],
            'with query' => ['https://example.com/path?foo=bar'],
            'with multiple query' => ['https://example.com/path?foo=bar&baz=qux'],
            'with fragment' => ['https://example.com/path#section'],
            'with query and fragment' => ['https://example.com/path?foo=bar#section'],
            'with port' => ['https://example.com:8443/path'],
            'with auth' => ['https://user:pass@example.com/path'],
            'with auth and port' => ['https://user:pass@example.com:8443/path'],
            'full url' => ['https://user:pass@example.com:8443/path/to/page.php?foo=bar&baz=qux#section'],
            'http with non-standard port' => ['http://example.com:8080/path'],
            'array query params' => ['https://example.com/path?items[0]=a&items[1]=b'],
            'encoded query' => ['https://example.com/path?foo=bar%20baz'],
            'empty query value' => ['https://example.com/path?flag'],
            'ftp url' => ['ftp://ftp.example.com/file.txt'],
        ];
    }

    /**
     * @dataProvider urlProvider
     */
    public function testPropertyCompatibility(string $url)
    {
        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        $this->assertEquals($legacy->protocol, $new->protocol, "protocol mismatch for $url");
        $this->assertEquals($legacy->host, $new->host, "host mismatch for $url");
        $this->assertEquals($legacy->port, $new->port, "port mismatch for $url");
        $this->assertEquals($legacy->path, $new->path, "path mismatch for $url");
        $this->assertEquals($legacy->user, $new->user, "user mismatch for $url");
        $this->assertEquals($legacy->pass, $new->pass, "pass mismatch for $url");
        $this->assertEquals($legacy->anchor, $new->anchor, "anchor mismatch for $url");
        $this->assertEquals($legacy->querystring, $new->querystring, "querystring mismatch for $url");
    }

    /**
     * @dataProvider urlProvider
     */
    public function testGetUrlCompatibility(string $url)
    {
        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        $this->assertEquals($legacy->getURL(), $new->getURL(), "getURL() mismatch for $url");
    }

    public function testAddQueryStringCompatibility()
    {
        $url = 'https://example.com/path';

        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        $legacy->addQueryString('foo', 'bar');
        $new->addQueryString('foo', 'bar');

        $this->assertEquals($legacy->querystring, $new->querystring);
        $this->assertEquals($legacy->getURL(), $new->getURL());
    }

    public function testAddQueryStringWithSpecialCharsCompatibility()
    {
        $url = 'https://example.com/path';

        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        $legacy->addQueryString('foo', 'bar baz');
        $new->addQueryString('foo', 'bar baz');

        $this->assertEquals($legacy->querystring, $new->querystring);
        $this->assertEquals($legacy->getURL(), $new->getURL());
    }

    public function testAddQueryStringPreencodedCompatibility()
    {
        $url = 'https://example.com/path';

        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        $legacy->addQueryString('foo', 'already%20encoded', true);
        $new->addQueryString('foo', 'already%20encoded', true);

        $this->assertEquals($legacy->querystring, $new->querystring);
        $this->assertEquals($legacy->getURL(), $new->getURL());
    }

    public function testAddQueryStringArrayCompatibility()
    {
        $url = 'https://example.com/path';

        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        $legacy->addQueryString('items', ['a', 'b', 'c']);
        $new->addQueryString('items', ['a', 'b', 'c']);

        $this->assertEquals($legacy->querystring, $new->querystring);
        $this->assertEquals($legacy->getURL(), $new->getURL());
    }

    public function testRemoveQueryStringCompatibility()
    {
        $url = 'https://example.com/path?foo=bar&baz=qux';

        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        $legacy->removeQueryString('foo');
        $new->removeQueryString('foo');

        $this->assertEquals($legacy->querystring, $new->querystring);
        $this->assertEquals($legacy->getURL(), $new->getURL());
    }

    public function testAddRawQueryStringCompatibility()
    {
        $url = 'https://example.com/path';

        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        $legacy->addRawQueryString('foo=bar&baz=qux');
        $new->addRawQueryString('foo=bar&baz=qux');

        $this->assertEquals($legacy->querystring, $new->querystring);
        $this->assertEquals($legacy->getURL(), $new->getURL());
    }

    public function testGetQueryStringCompatibility()
    {
        $url = 'https://example.com/path?foo=bar&baz=qux';

        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        $this->assertEquals($legacy->getQueryString(), $new->getQueryString());
    }

    public function testSetProtocolCompatibility()
    {
        $url = 'http://example.com/path';

        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        $legacy->setProtocol('https');
        $new->setProtocol('https');

        $this->assertEquals($legacy->protocol, $new->protocol);
        $this->assertEquals($legacy->port, $new->port);
        $this->assertEquals($legacy->getURL(), $new->getURL());
    }

    public function testSetProtocolWithPortCompatibility()
    {
        $url = 'http://example.com/path';

        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        $legacy->setProtocol('https', 8443);
        $new->setProtocol('https', 8443);

        $this->assertEquals($legacy->protocol, $new->protocol);
        $this->assertEquals($legacy->port, $new->port);
        $this->assertEquals($legacy->getURL(), $new->getURL());
    }

    public function resolvePathProvider(): array
    {
        return [
            ['/foo/bar/../boo.php'],
            ['/foo/bar/../../boo.php'],
            ['/foo/bar/.././boo.php'],
            ['/foo//boo.php'],
            ['/foo/./bar/./boo.php'],
            ['/../foo/bar'],
            ['/foo/bar/'],
            ['/./foo/./bar/./'],
        ];
    }

    /**
     * @dataProvider resolvePathProvider
     */
    public function testResolvePathCompatibility(string $path)
    {
        $this->assertEquals(
            Net_URL_Legacy::resolvePath($path),
            Net_URL::resolvePath($path),
            "resolvePath() mismatch for $path"
        );
    }

    public function standardPortProvider(): array
    {
        return [
            ['http', 80],
            ['https', 443],
            ['ftp', 21],
            ['imap', 143],
            ['imaps', 993],
            ['pop3', 110],
            ['pop3s', 995],
            ['unknown', null],
        ];
    }

    /**
     * @dataProvider standardPortProvider
     */
    public function testGetStandardPortCompatibility(string $scheme, ?int $expected)
    {
        $legacy = new Net_URL_Legacy('http://example.com');
        $new = new Net_URL('http://example.com');

        $this->assertEquals(
            $legacy->getStandardPort($scheme),
            $new->getStandardPort($scheme),
            "getStandardPort() mismatch for $scheme"
        );
    }

    public function testOptionsCompatibility()
    {
        $url = 'https://example.com/path';

        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        // Default option
        $this->assertEquals($legacy->getOption('encode_query_keys'), $new->getOption('encode_query_keys'));

        // Set option
        $legacy->setOption('encode_query_keys', true);
        $new->setOption('encode_query_keys', true);

        $this->assertEquals($legacy->getOption('encode_query_keys'), $new->getOption('encode_query_keys'));

        // Unknown option
        $this->assertEquals($legacy->getOption('unknown'), $new->getOption('unknown'));
        $this->assertEquals($legacy->setOption('unknown', 'value'), $new->setOption('unknown', 'value'));
    }

    public function testUseBracketsCompatibility()
    {
        $url = 'https://example.com/path';

        // With brackets (default)
        $legacyWithBrackets = new Net_URL_Legacy($url, true);
        $newWithBrackets = new Net_URL($url, true);

        $legacyWithBrackets->addQueryString('items', ['a', 'b']);
        $newWithBrackets->addQueryString('items', ['a', 'b']);

        $this->assertEquals($legacyWithBrackets->getQueryString(), $newWithBrackets->getQueryString());

        // Without brackets
        $legacyWithoutBrackets = new Net_URL_Legacy($url, false);
        $newWithoutBrackets = new Net_URL($url, false);

        $legacyWithoutBrackets->addQueryString('items', ['a', 'b']);
        $newWithoutBrackets->addQueryString('items', ['a', 'b']);

        $this->assertEquals($legacyWithoutBrackets->getQueryString(), $newWithoutBrackets->getQueryString());
    }

    public function testMultipleModificationsCompatibility()
    {
        $url = 'https://example.com/path?existing=value';

        $legacy = new Net_URL_Legacy($url);
        $new = new Net_URL($url);

        // Multiple operations
        $legacy->addQueryString('foo', 'bar');
        $new->addQueryString('foo', 'bar');

        $legacy->addQueryString('baz', 'qux');
        $new->addQueryString('baz', 'qux');

        $legacy->removeQueryString('existing');
        $new->removeQueryString('existing');

        $legacy->addQueryString('array', ['a', 'b']);
        $new->addQueryString('array', ['a', 'b']);

        $this->assertEquals($legacy->querystring, $new->querystring);
        $this->assertEquals($legacy->getURL(), $new->getURL());
    }
}
