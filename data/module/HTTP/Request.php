<?php
/**
 * HTTP_Request - Guzzle based HTTP client class
 *
 * This is a backward-compatible wrapper around GuzzleHttp\Client
 * that maintains the original HTTP_Request API.
 *
 * Original HTTP_Request Copyright (c) 2002-2007, Richard Heyes
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * o Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * o Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * o The names of the authors may not be used to endorse or promote
 *   products derived from this software without specific prior written
 *   permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    HTTP
 *
 * @author      Richard Heyes <richard@phpguru.org>
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2002-2007 Richard Heyes
 * @license     http://opensource.org/licenses/bsd-license.php New BSD License
 */

require_once 'PEAR.php';
require_once 'Net/URL.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/*
 * Constants for HTTP request methods
 */
define('HTTP_REQUEST_METHOD_GET', 'GET');
define('HTTP_REQUEST_METHOD_HEAD', 'HEAD');
define('HTTP_REQUEST_METHOD_POST', 'POST');
define('HTTP_REQUEST_METHOD_PUT', 'PUT');
define('HTTP_REQUEST_METHOD_DELETE', 'DELETE');
define('HTTP_REQUEST_METHOD_OPTIONS', 'OPTIONS');
define('HTTP_REQUEST_METHOD_TRACE', 'TRACE');

/*
 * Constants for HTTP request error codes
 */
define('HTTP_REQUEST_ERROR_FILE', 1);
define('HTTP_REQUEST_ERROR_URL', 2);
define('HTTP_REQUEST_ERROR_PROXY', 4);
define('HTTP_REQUEST_ERROR_REDIRECTS', 8);
define('HTTP_REQUEST_ERROR_RESPONSE', 16);
define('HTTP_REQUEST_ERROR_GZIP_METHOD', 32);
define('HTTP_REQUEST_ERROR_GZIP_READ', 64);
define('HTTP_REQUEST_ERROR_GZIP_DATA', 128);
define('HTTP_REQUEST_ERROR_GZIP_CRC', 256);
define('HTTP_REQUEST_ERROR_SSRF', 512);

/*
 * Constants for HTTP protocol versions
 */
define('HTTP_REQUEST_HTTP_VER_1_0', '1.0');
define('HTTP_REQUEST_HTTP_VER_1_1', '1.1');

/*
 * Whether mbstring functions overload standard string functions
 */
if (extension_loaded('mbstring') && (2 & ini_get('mbstring.func_overload'))) {
    define('HTTP_REQUEST_MBSTRING', true);
} else {
    define('HTTP_REQUEST_MBSTRING', false);
}

/**
 * Class for performing HTTP requests using Guzzle
 *
 * Simple example (fetches yahoo.com and displays it):
 * <code>
 * $a = new HTTP_Request('http://www.yahoo.com/');
 * $a->sendRequest();
 * echo $a->getResponseBody();
 * </code>
 *
 * @category    HTTP
 *
 * @author      Richard Heyes <richard@phpguru.org>
 * @author      Alexey Borzov <avb@php.net>
 */
class HTTP_Request
{
    /**
     * Instance of Net_URL
     *
     * @var Net_URL
     */
    public $_url;

    /**
     * Type of request
     *
     * @var string
     */
    public $_method;

    /**
     * HTTP Version
     *
     * @var string
     */
    public $_http;

    /**
     * Request headers
     *
     * @var array
     */
    public $_requestHeaders;

    /**
     * Basic Auth Username
     *
     * @var string
     */
    public $_user;

    /**
     * Basic Auth Password
     *
     * @var string
     */
    public $_pass;

    /**
     * Guzzle HTTP Client
     *
     * @var Client
     */
    public $_client;

    /**
     * Proxy server
     *
     * @var string
     */
    public $_proxy_host;

    /**
     * Proxy port
     *
     * @var int
     */
    public $_proxy_port;

    /**
     * Proxy username
     *
     * @var string
     */
    public $_proxy_user;

    /**
     * Proxy password
     *
     * @var string
     */
    public $_proxy_pass;

    /**
     * Post data
     *
     * @var array
     */
    public $_postData;

    /**
     * Request body
     *
     * @var string
     */
    public $_body;

    /**
     * A list of methods that MUST NOT have a request body, per RFC 2616
     *
     * @var array
     */
    public $_bodyDisallowed = ['TRACE'];

    /**
     * Methods having defined semantics for request body
     *
     * @var array
     */
    public $_bodyRequired = ['POST', 'PUT'];

    /**
     * Files to post
     *
     * @var array
     */
    public $_postFiles = [];

    /**
     * Connection timeout
     *
     * @var float
     */
    public $_timeout;

    /**
     * HTTP_Response object
     *
     * @var HTTP_Response
     */
    public $_response;

    /**
     * Whether to allow redirects
     *
     * @var bool
     */
    public $_allowRedirects;

    /**
     * Maximum redirects allowed
     *
     * @var int
     */
    public $_maxRedirects;

    /**
     * Current number of redirects
     *
     * @var int
     */
    public $_redirects;

    /**
     * Whether to append brackets [] to array variable names
     *
     * @var bool
     */
    public $_useBrackets = true;

    /**
     * Attached listeners
     *
     * @var array
     */
    public $_listeners = [];

    /**
     * Whether to save response body in response object property
     *
     * @var bool
     */
    public $_saveBody = true;

    /**
     * Timeout for reading from socket (array(seconds, microseconds))
     *
     * @var array
     */
    public $_readTimeout;

    /**
     * Options to pass to stream context
     *
     * @var array
     */
    public $_socketOptions;

    /**
     * Whether to enable SSRF protection (default: true)
     *
     * @var bool
     */
    public $_ssrfProtection = true;

    /**
     * Whether to verify SSL certificates
     *
     * @var bool
     */
    public $_sslVerify = true;

    /**
     * Resolved IP address for SSRF protection (prevents DNS rebinding)
     *
     * @var string|null
     */
    protected $_resolvedIP = null;

    /**
     * Constructor
     *
     * Sets up the object
     *
     * @param string $url    The url to fetch/access
     * @param array  $params Associative array of parameters which can have the following keys:
     * <ul>
     *   <li>method         - Method to use, GET, POST etc (string)</li>
     *   <li>http           - HTTP Version to use, 1.0 or 1.1 (string)</li>
     *   <li>user           - Basic Auth username (string)</li>
     *   <li>pass           - Basic Auth password (string)</li>
     *   <li>proxy_host     - Proxy server host (string)</li>
     *   <li>proxy_port     - Proxy server port (integer)</li>
     *   <li>proxy_user     - Proxy auth username (string)</li>
     *   <li>proxy_pass     - Proxy auth password (string)</li>
     *   <li>timeout        - Connection timeout in seconds (float)</li>
     *   <li>allowRedirects - Whether to follow redirects or not (bool)</li>
     *   <li>maxRedirects   - Max number of redirects to follow (integer)</li>
     *   <li>useBrackets    - Whether to append [] to array variable names (bool)</li>
     *   <li>saveBody       - Whether to save response body in response object property (bool)</li>
     *   <li>readTimeout    - Timeout for reading / writing data over the socket (array (seconds, microseconds))</li>
     *   <li>socketOptions  - Options to pass to stream context (array)</li>
     *   <li>ssrfProtection - Whether to enable SSRF protection (bool, default: true)</li>
     *   <li>sslVerify      - Whether to verify SSL certificates (bool, default: true)</li>
     * </ul>
     */
    public function __construct($url = '', $params = [])
    {
        $this->_method = HTTP_REQUEST_METHOD_GET;
        $this->_http = HTTP_REQUEST_HTTP_VER_1_1;
        $this->_requestHeaders = [];
        $this->_postData = [];
        $this->_body = null;

        $this->_user = null;
        $this->_pass = null;

        $this->_proxy_host = null;
        $this->_proxy_port = null;
        $this->_proxy_user = null;
        $this->_proxy_pass = null;

        $this->_allowRedirects = false;
        $this->_maxRedirects = 3;
        $this->_redirects = 0;

        $this->_timeout = null;
        $this->_response = null;

        $this->_ssrfProtection = true;
        $this->_sslVerify = true;

        foreach ($params as $key => $value) {
            // Handle snake_case aliases for new options
            if ($key === 'ssrf_protection' || $key === 'ssrfProtection') {
                $this->_ssrfProtection = (bool) $value;
                continue;
            }
            if ($key === 'ssl_verify' || $key === 'sslVerify') {
                $this->_sslVerify = (bool) $value;
                continue;
            }
            $this->{'_'.$key} = $value;
        }

        if (!empty($url)) {
            $this->setURL($url);
        }

        // Default useragent
        $this->addHeader('User-Agent', 'PEAR HTTP_Request class ( http://pear.php.net/ )');

        // We don't do keep-alives by default
        $this->addHeader('Connection', 'close');

        // Basic authentication
        if (!empty($this->_user)) {
            $this->addHeader('Authorization', 'Basic '.base64_encode($this->_user.':'.$this->_pass));
        }

        // Proxy authentication (see bug #5913)
        if (!empty($this->_proxy_user)) {
            $this->addHeader('Proxy-Authorization', 'Basic '.base64_encode($this->_proxy_user.':'.$this->_proxy_pass));
        }

        // Use gzip encoding if possible
        if (HTTP_REQUEST_HTTP_VER_1_1 == $this->_http && extension_loaded('zlib')) {
            $this->addHeader('Accept-Encoding', 'gzip');
        }
    }

    /**
     * Generates a Host header for HTTP/1.1 requests
     *
     * @return string
     */
    public function _generateHostHeader()
    {
        if ($this->_url->port != 80 && strcasecmp($this->_url->protocol, 'http') == 0) {
            $host = $this->_url->host.':'.$this->_url->port;
        } elseif ($this->_url->port != 443 && strcasecmp($this->_url->protocol, 'https') == 0) {
            $host = $this->_url->host.':'.$this->_url->port;
        } elseif ($this->_url->port == 443 && strcasecmp($this->_url->protocol, 'https') == 0 && str_contains($this->_url->url, ':443')) {
            $host = $this->_url->host.':'.$this->_url->port;
        } else {
            $host = $this->_url->host;
        }

        return $host;
    }

    /**
     * Resets the object to its initial state (DEPRECATED).
     *
     * @param string $url    The url to be requested
     * @param array  $params Associative array of parameters
     *
     * @deprecated deprecated since 1.2, call the constructor if this is necessary
     */
    public function reset($url, $params = [])
    {
        self::__construct($url, $params);
    }

    /**
     * Sets the URL to be requested
     *
     * @param string $url The url to be requested
     */
    public function setURL($url)
    {
        $this->_url = new Net_URL($url, $this->_useBrackets);

        if (!empty($this->_url->user) || !empty($this->_url->pass)) {
            $this->setBasicAuth($this->_url->user, $this->_url->pass);
        }

        if (HTTP_REQUEST_HTTP_VER_1_1 == $this->_http) {
            $this->addHeader('Host', $this->_generateHostHeader());
        }

        // set '/' instead of empty path rather than check later (see bug #8662)
        if (empty($this->_url->path)) {
            $this->_url->path = '/';
        }
    }

    /**
     * Returns the current request URL
     *
     * @return string Current request URL
     */
    public function getUrl()
    {
        return empty($this->_url) ? '' : $this->_url->getUrl();
    }

    /**
     * Sets a proxy to be used
     *
     * @param string $host Proxy host
     * @param int    $port Proxy port
     * @param string $user Proxy username
     * @param string $pass Proxy password
     */
    public function setProxy($host, $port = 8080, $user = null, $pass = null)
    {
        $this->_proxy_host = $host;
        $this->_proxy_port = $port;
        $this->_proxy_user = $user;
        $this->_proxy_pass = $pass;

        if (!empty($user)) {
            $this->addHeader('Proxy-Authorization', 'Basic '.base64_encode($user.':'.$pass));
        }
    }

    /**
     * Sets basic authentication parameters
     *
     * @param string $user Username
     * @param string $pass Password
     */
    public function setBasicAuth($user, $pass)
    {
        $this->_user = $user;
        $this->_pass = $pass;

        $this->addHeader('Authorization', 'Basic '.base64_encode($user.':'.$pass));
    }

    /**
     * Sets the method to be used, GET, POST etc.
     *
     * @param string $method Method to use
     */
    public function setMethod($method)
    {
        $this->_method = $method;
    }

    /**
     * Sets the HTTP version to use, 1.0 or 1.1
     *
     * @param string $http Version to use
     */
    public function setHttpVer($http)
    {
        $this->_http = $http;
    }

    /**
     * Adds a request header
     *
     * @param string $name  Header name
     * @param string $value Header value
     */
    public function addHeader($name, $value)
    {
        $this->_requestHeaders[strtolower($name)] = $value;
    }

    /**
     * Removes a request header
     *
     * @param string $name Header name to remove
     */
    public function removeHeader($name)
    {
        if (isset($this->_requestHeaders[strtolower($name)])) {
            unset($this->_requestHeaders[strtolower($name)]);
        }
    }

    /**
     * Adds a querystring parameter
     *
     * @param string $name       Querystring parameter name
     * @param string $value      Querystring parameter value
     * @param bool   $preencoded Whether the value is already urlencoded or not
     */
    public function addQueryString($name, $value, $preencoded = false)
    {
        $this->_url->addQueryString($name, $value, $preencoded);
    }

    /**
     * Sets the querystring to literally what you supply
     *
     * @param string $querystring The querystring data
     * @param bool   $preencoded  Whether data is already urlencoded or not
     */
    public function addRawQueryString($querystring, $preencoded = true)
    {
        $this->_url->addRawQueryString($querystring, $preencoded);
    }

    /**
     * Adds postdata items
     *
     * @param string $name       Post data name
     * @param mixed  $value      Post data value
     * @param bool   $preencoded Whether data is already urlencoded or not
     */
    public function addPostData($name, $value, $preencoded = false)
    {
        if ($preencoded) {
            $this->_postData[$name] = $value;
        } else {
            $this->_postData[$name] = $this->_arrayMapRecursive('urlencode', $value);
        }
    }

    /**
     * Adds multiple postdata items
     *
     * @param array $array      Array of post data
     * @param bool  $preencoded Whether data is already urlencoded or not
     */
    public function addPostDataArray($array, $preencoded = false)
    {
        foreach ($array as $key => $val) {
            $this->addPostData($key, $val, $preencoded);
        }
    }

    /**
     * Recursively applies the callback function to the value
     *
     * @param mixed $callback Callback function
     * @param mixed $value    Value to process
     *
     * @return mixed Processed value
     */
    public function _arrayMapRecursive($callback, $value)
    {
        if (!is_array($value)) {
            return call_user_func($callback, $value);
        } else {
            $map = [];
            foreach ($value as $k => $v) {
                $map[$k] = $this->_arrayMapRecursive($callback, $v);
            }

            return $map;
        }
    }

    /**
     * Adds a file to form-based file upload
     *
     * @param string $inputName   name of file-upload field
     * @param mixed  $fileName    file name(s)
     * @param mixed  $contentType content-type(s) of file(s) being uploaded
     *
     * @return bool|PEAR_Error true on success
     */
    public function addFile($inputName, $fileName, $contentType = 'application/octet-stream')
    {
        if (!is_array($fileName) && !is_readable($fileName)) {
            return PEAR::raiseError("File '{$fileName}' is not readable", HTTP_REQUEST_ERROR_FILE);
        } elseif (is_array($fileName)) {
            foreach ($fileName as $name) {
                if (!is_readable($name)) {
                    return PEAR::raiseError("File '{$name}' is not readable", HTTP_REQUEST_ERROR_FILE);
                }
            }
        }
        $this->addHeader('Content-Type', 'multipart/form-data');
        $this->_postFiles[$inputName] = [
            'name' => $fileName,
            'type' => $contentType,
        ];

        return true;
    }

    /**
     * Adds raw postdata (DEPRECATED)
     *
     * @param string $postdata   The data
     * @param bool   $preencoded Whether data is preencoded or not
     *
     * @deprecated deprecated since 1.3.0, method setBody() should be used instead
     */
    public function addRawPostData($postdata, $preencoded = true)
    {
        $this->_body = $preencoded ? $postdata : urlencode($postdata);
    }

    /**
     * Sets the request body (for POST, PUT and similar requests)
     *
     * @param string $body Request body
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }

    /**
     * Clears any postdata that has been added (DEPRECATED)
     *
     * @deprecated deprecated since 1.2
     */
    public function clearPostData()
    {
        $this->_postData = null;
    }

    /**
     * Appends a cookie to "Cookie:" header
     *
     * @param string $name  cookie name
     * @param string $value cookie value
     */
    public function addCookie($name, $value)
    {
        $cookies = isset($this->_requestHeaders['cookie']) ? $this->_requestHeaders['cookie'].'; ' : '';
        $this->addHeader('Cookie', $cookies.$name.'='.$value);
    }

    /**
     * Clears any cookies that have been added (DEPRECATED)
     *
     * @deprecated deprecated since 1.2
     */
    public function clearCookies()
    {
        $this->removeHeader('Cookie');
    }

    /**
     * Resolve hostname to IP address
     *
     * @param string $host Hostname or IP address
     *
     * @return string|null Resolved IP address or null if resolution failed
     */
    protected function _resolveHost($host)
    {
        // If already an IP address, return as-is
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $host;
        }

        // Try IPv4 resolution first
        $ip = gethostbyname($host);
        if ($ip !== $host) {
            return $ip;
        }

        // Try IPv6 (AAAA records)
        $aaaa = @dns_get_record($host, DNS_AAAA);
        if (!empty($aaaa) && isset($aaaa[0]['ipv6'])) {
            return $aaaa[0]['ipv6'];
        }

        return null;
    }

    /**
     * Check if an IP address is private/internal
     *
     * @param string $host Hostname or IP address
     *
     * @return bool true if the IP is private
     */
    protected function _isPrivateIP($host)
    {
        $ip = $this->_resolveHost($host);

        // Could not resolve, block for safety
        if ($ip === null) {
            return true;
        }

        // Check for IPv4 private ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $this->_isPrivateIPv4($ip);
        }

        // Check for IPv6 private ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->_isPrivateIPv6($ip);
        }

        return false;
    }

    /**
     * Check if an IPv4 address is private/internal
     *
     * @param string $ip IPv4 address
     *
     * @return bool true if the IP is private
     */
    protected function _isPrivateIPv4($ip)
    {
        // 10.0.0.0/8
        if (str_starts_with($ip, '10.')) {
            return true;
        }
        // 172.16.0.0/12
        if (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)) {
            return true;
        }
        // 192.168.0.0/16
        if (str_starts_with($ip, '192.168.')) {
            return true;
        }
        // 127.0.0.0/8 (localhost)
        if (str_starts_with($ip, '127.')) {
            return true;
        }
        // 169.254.0.0/16 (link-local)
        if (str_starts_with($ip, '169.254.')) {
            return true;
        }
        // 0.0.0.0/8
        if (str_starts_with($ip, '0.')) {
            return true;
        }
        // 240.0.0.0/4 (reserved for future use)
        if (preg_match('/^(24[0-9]|25[0-5])\./', $ip)) {
            return true;
        }
        // 255.255.255.255 (broadcast)
        if ($ip === '255.255.255.255') {
            return true;
        }

        return false;
    }

    /**
     * Check if an IPv6 address is private/internal
     *
     * @param string $ip IPv6 address
     *
     * @return bool true if the IP is private
     */
    protected function _isPrivateIPv6($ip)
    {
        $ip = strtolower($ip);
        // ::1 (localhost)
        if ($ip === '::1') {
            return true;
        }
        // fc00::/7 (unique local addresses)
        if (str_starts_with($ip, 'fc') || str_starts_with($ip, 'fd')) {
            return true;
        }
        // fe80::/10 (link-local)
        if (str_starts_with($ip, 'fe80')) {
            return true;
        }

        return false;
    }

    /**
     * Sends the request using Guzzle
     *
     * @param bool $saveBody Whether to store response body in Response object property
     *
     * @return bool|PEAR_Error PEAR error on error, true otherwise
     */
    public function sendRequest($saveBody = true)
    {
        if (!is_a($this->_url, 'Net_URL')) {
            return PEAR::raiseError('No URL given', HTTP_REQUEST_ERROR_URL);
        }

        // SSRF protection with DNS rebinding prevention
        // Resolve the IP once and reuse it to prevent TOCTOU attacks
        $this->_resolvedIP = null;
        if ($this->_ssrfProtection) {
            $this->_resolvedIP = $this->_resolveHost($this->_url->host);
            if ($this->_resolvedIP === null) {
                return PEAR::raiseError('Could not resolve hostname', HTTP_REQUEST_ERROR_URL);
            }
            if ($this->_isPrivateIPv4($this->_resolvedIP) || $this->_isPrivateIPv6($this->_resolvedIP)) {
                return PEAR::raiseError('Private IP addresses are not allowed', HTTP_REQUEST_ERROR_SSRF);
            }
        }

        // Legacy compatibility: HTTPS requests cannot be sent via proxy
        if (strcasecmp($this->_url->protocol, 'https') == 0 && isset($this->_proxy_host)) {
            return PEAR::raiseError('HTTPS proxies are not supported', HTTP_REQUEST_ERROR_PROXY);
        }

        $this->_notify('connect');

        try {
            // Build Guzzle options
            $options = $this->_buildGuzzleOptions();

            // Create Guzzle client
            $this->_client = new Client();

            // Get the URL
            $url = $this->_url->getUrl();

            // Make the request
            $response = $this->_client->request($this->_method, $url, $options);

            $this->_notify('sentRequest');

            // Build HTTP_Response from Guzzle response
            $this->_response = $this->_buildHttpResponse($response);

            $this->_notify('gotHeaders', $this->_response->_headers);

            if ($saveBody && $this->_saveBody) {
                $this->_notify('gotBody');
            }

            return true;
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                // We got a response, but it's an error (4xx, 5xx)
                $this->_response = $this->_buildHttpResponse($e->getResponse());

                return true;
            }

            return PEAR::raiseError($e->getMessage(), HTTP_REQUEST_ERROR_RESPONSE);
        } catch (GuzzleException $e) {
            return PEAR::raiseError($e->getMessage(), HTTP_REQUEST_ERROR_RESPONSE);
        }
    }

    /**
     * Build Guzzle request options from HTTP_Request state
     *
     * @return array Guzzle options array
     */
    protected function _buildGuzzleOptions()
    {
        $options = [
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::VERIFY => $this->_sslVerify,
        ];

        // SSRF protection: Use pre-resolved IP to prevent DNS rebinding (TOCTOU)
        if ($this->_ssrfProtection && $this->_resolvedIP !== null && !filter_var($this->_url->host, FILTER_VALIDATE_IP)) {
            $port = $this->_url->port;
            $host = $this->_url->host;
            // CURLOPT_RESOLVE format: "host:port:address"
            $options['curl'] = [
                CURLOPT_RESOLVE => ["{$host}:{$port}:{$this->_resolvedIP}"],
            ];
        }

        // HTTP version
        $options[RequestOptions::VERSION] = $this->_http;

        // Timeout
        if ($this->_timeout !== null) {
            $options[RequestOptions::TIMEOUT] = $this->_timeout;
            $options[RequestOptions::CONNECT_TIMEOUT] = $this->_timeout;
        }

        // Read timeout
        if (!empty($this->_readTimeout)) {
            $readTimeoutSeconds = $this->_readTimeout[0] + ($this->_readTimeout[1] / 1000000);
            $options[RequestOptions::READ_TIMEOUT] = $readTimeoutSeconds;
        }

        // Note: _socketOptions is not supported in Guzzle implementation
        // Legacy Net_Socket stream context options cannot be directly mapped to cURL options

        // Headers
        $headers = [];
        foreach ($this->_requestHeaders as $name => $value) {
            $canonicalName = implode('-', array_map('ucfirst', explode('-', $name)));
            $headers[$canonicalName] = $value;
        }
        $options[RequestOptions::HEADERS] = $headers;

        // Proxy
        if (!empty($this->_proxy_host)) {
            $proxyUrl = $this->_proxy_host.':'.$this->_proxy_port;
            if (!empty($this->_proxy_user)) {
                $proxyUrl = $this->_proxy_user.':'.$this->_proxy_pass.'@'.$proxyUrl;
            }
            $options[RequestOptions::PROXY] = [
                'http' => 'http://'.$proxyUrl,
                'https' => 'http://'.$proxyUrl,
            ];
        }

        // Handle redirects
        if ($this->_allowRedirects) {
            $self = $this;
            $options[RequestOptions::ALLOW_REDIRECTS] = [
                'max' => $this->_maxRedirects,
                'strict' => true,
                'referer' => true,
                'track_redirects' => true,
                'on_redirect' => function ($request, $response, $uri) use ($self) {
                    // Increment redirect counter
                    $self->_redirects++;

                    // Get redirect target URL
                    $redirectUrl = (string) $uri;
                    $parsedUrl = parse_url($redirectUrl);
                    $redirectHost = $parsedUrl['host'] ?? '';

                    // SSRF protection on redirect target
                    if ($self->_ssrfProtection && !empty($redirectHost)) {
                        $resolvedIP = $self->_resolveHost($redirectHost);
                        if ($resolvedIP === null) {
                            throw new \GuzzleHttp\Exception\RequestException('Could not resolve redirect target hostname', $request);
                        }
                        $isPrivate = filter_var($resolvedIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
                            ? $self->_isPrivateIPv4($resolvedIP)
                            : $self->_isPrivateIPv6($resolvedIP);
                        if ($isPrivate) {
                            throw new \GuzzleHttp\Exception\RequestException('Redirect to private IP addresses is not allowed', $request);
                        }
                    }

                    // Update internal URL
                    if (!empty($redirectUrl)) {
                        $self->_url = new Net_URL($redirectUrl);
                    }

                    // Fire legacy notification event
                    $self->_notify('redirect', $self->_url);
                },
            ];
        }

        // Handle body/post data
        if (!in_array($this->_method, $this->_bodyDisallowed)) {
            // Explicit body takes precedence
            if (!empty($this->_body)) {
                $options[RequestOptions::BODY] = $this->_body;
            } elseif (!empty($this->_postFiles)) {
                // Multipart form data with files
                $multipart = [];

                // Add post data
                if (!empty($this->_postData)) {
                    $flatData = $this->_flattenArray('', $this->_postData);
                    foreach ($flatData as $item) {
                        $multipart[] = [
                            'name' => $item[0],
                            'contents' => urldecode($item[1]),
                        ];
                    }
                }

                // Add files
                foreach ($this->_postFiles as $name => $value) {
                    if (is_array($value['name'])) {
                        $varname = $name.($this->_useBrackets ? '[]' : '');
                        foreach ($value['name'] as $key => $filename) {
                            $type = is_array($value['type']) ? ($value['type'][$key] ?? 'application/octet-stream') : $value['type'];
                            $multipart[] = [
                                'name' => $varname,
                                'contents' => Utils::tryFopen($filename, 'r'),
                                'filename' => basename($filename),
                                'headers' => ['Content-Type' => $type],
                            ];
                        }
                    } else {
                        $multipart[] = [
                            'name' => $name,
                            'contents' => Utils::tryFopen($value['name'], 'r'),
                            'filename' => basename($value['name']),
                            'headers' => ['Content-Type' => $value['type']],
                        ];
                    }
                }

                $options[RequestOptions::MULTIPART] = $multipart;
                // Remove Content-Type header as Guzzle will set it with boundary
                unset($options[RequestOptions::HEADERS]['Content-Type']);
            } elseif (!empty($this->_postData) && $this->_method === HTTP_REQUEST_METHOD_POST) {
                // Regular form data
                $postdata = implode('&', array_map(
                    function ($a) {
                        return $a[0].'='.$a[1];
                    },
                    $this->_flattenArray('', $this->_postData)
                ));
                $options[RequestOptions::BODY] = $postdata;

                if (empty($options[RequestOptions::HEADERS]['Content-Type'])) {
                    $options[RequestOptions::HEADERS]['Content-Type'] = 'application/x-www-form-urlencoded';
                }
            }
        }

        // Decode gzip automatically
        $options[RequestOptions::DECODE_CONTENT] = true;

        return $options;
    }

    /**
     * Build HTTP_Response object from Guzzle PSR-7 response
     *
     * @param ResponseInterface $guzzleResponse Guzzle response
     *
     * @return HTTP_Response
     */
    protected function _buildHttpResponse(ResponseInterface $guzzleResponse)
    {
        $response = new HTTP_Response();

        $response->_protocol = 'HTTP/'.$guzzleResponse->getProtocolVersion();
        $response->_code = $guzzleResponse->getStatusCode();
        $response->_reason = $guzzleResponse->getReasonPhrase();

        // Headers
        $response->_headers = [];
        foreach ($guzzleResponse->getHeaders() as $name => $values) {
            $headerName = strtolower($name);
            $headerValue = implode(', ', $values);

            if ($headerName === 'set-cookie') {
                // Parse cookies
                foreach ($values as $cookieValue) {
                    $response->_parseCookie($cookieValue);
                }
            } else {
                $response->_headers[$headerName] = $headerValue;
            }
        }

        // Body
        $response->_body = (string) $guzzleResponse->getBody();

        return $response;
    }

    /**
     * Disconnect (no-op for Guzzle, kept for API compatibility)
     */
    public function disconnect()
    {
        $this->_notify('disconnect');
        // Guzzle handles connection pooling internally
    }

    /**
     * Returns the response code
     *
     * @return int|false Response code, false if not set
     */
    public function getResponseCode()
    {
        return $this->_response->_code ?? false;
    }

    /**
     * Returns the response reason phrase
     *
     * @return string|false Response reason phrase, false if not set
     */
    public function getResponseReason()
    {
        return $this->_response->_reason ?? false;
    }

    /**
     * Returns either the named header or all if no name given
     *
     * @param string $headername The header name to return
     *
     * @return mixed either the value of $headername or an array of all headers
     */
    public function getResponseHeader($headername = null)
    {
        if (!isset($headername)) {
            return $this->_response->_headers ?? [];
        } else {
            $headername = strtolower($headername);

            return $this->_response->_headers[$headername] ?? false;
        }
    }

    /**
     * Returns the body of the response
     *
     * @return string|false response body, false if not set
     */
    public function getResponseBody()
    {
        return $this->_response->_body ?? false;
    }

    /**
     * Returns cookies set in response
     *
     * @return array|false array of response cookies, false if none are present
     */
    public function getResponseCookies()
    {
        return $this->_response->_cookies ?? false;
    }

    /**
     * Helper function to change the (probably multidimensional) associative array
     * into the simple one.
     *
     * @param string $name   name for item
     * @param mixed  $values item's values
     *
     * @return array array with the following items: array('item name', 'item value');
     */
    public function _flattenArray($name, $values)
    {
        if (!is_array($values)) {
            return [[$name, $values]];
        } else {
            $ret = [];
            foreach ($values as $k => $v) {
                if (empty($name)) {
                    $newName = $k;
                } elseif ($this->_useBrackets) {
                    $newName = $name.'['.$k.']';
                } else {
                    $newName = $name;
                }
                $ret = array_merge($ret, $this->_flattenArray($newName, $v));
            }

            return $ret;
        }
    }

    /**
     * Adds a Listener to the list of listeners that are notified of
     * the object's events
     *
     * Events sent by HTTP_Request object
     * - 'connect': on connection to server
     * - 'sentRequest': after the request was sent
     * - 'disconnect': on disconnection from server
     *
     * Events sent by HTTP_Response object
     * - 'gotHeaders': after receiving response headers (headers are passed in $data)
     * - 'tick': on receiving a part of response body (the part is passed in $data)
     * - 'gzTick': on receiving a gzip-encoded part of response body (ditto)
     * - 'gotBody': after receiving the response body
     *
     * @param HTTP_Request_Listener $listener listener to attach
     *
     * @return bool whether the listener was successfully attached
     */
    public function attach(&$listener)
    {
        if (!is_a($listener, 'HTTP_Request_Listener')) {
            return false;
        }
        $this->_listeners[$listener->getId()] = &$listener;

        return true;
    }

    /**
     * Removes a Listener from the list of listeners
     *
     * @param HTTP_Request_Listener $listener listener to detach
     *
     * @return bool whether the listener was successfully detached
     */
    public function detach(&$listener)
    {
        if (!is_a($listener, 'HTTP_Request_Listener')
            || !isset($this->_listeners[$listener->getId()])) {
            return false;
        }
        unset($this->_listeners[$listener->getId()]);

        return true;
    }

    /**
     * Notifies all registered listeners of an event.
     *
     * @param string $event Event name
     * @param mixed  $data  Additional data
     *
     * @see HTTP_Request::attach()
     */
    public function _notify($event, $data = null)
    {
        foreach (array_keys($this->_listeners) as $id) {
            $this->_listeners[$id]->update($this, $event, $data);
        }
    }
}

/**
 * Response class to complement the Request class
 *
 * @category    HTTP
 *
 * @author      Richard Heyes <richard@phpguru.org>
 * @author      Alexey Borzov <avb@php.net>
 */
class HTTP_Response
{
    /**
     * Protocol
     *
     * @var string
     */
    public $_protocol;

    /**
     * Return code
     *
     * @var int
     */
    public $_code;

    /**
     * Response reason phrase
     *
     * @var string
     */
    public $_reason;

    /**
     * Response headers
     *
     * @var array
     */
    public $_headers = [];

    /**
     * Cookies set in response
     *
     * @var array
     */
    public $_cookies = [];

    /**
     * Response body
     *
     * @var string
     */
    public $_body = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_headers = [];
        $this->_cookies = [];
        $this->_body = '';
    }

    /**
     * Parse a Set-Cookie header to fill $_cookies array
     *
     * @param string $headervalue value of Set-Cookie header
     */
    public function _parseCookie($headervalue)
    {
        $cookie = [
            'expires' => null,
            'domain' => null,
            'path' => null,
            'secure' => false,
        ];

        // Only a name=value pair
        if (!strpos($headervalue, ';')) {
            $pos = strpos($headervalue, '=');
            $cookie['name'] = trim(substr($headervalue, 0, $pos));
            $cookie['value'] = trim(substr($headervalue, $pos + 1));
        // Some optional parameters are supplied
        } else {
            $elements = explode(';', $headervalue);
            $pos = strpos($elements[0], '=');
            $cookie['name'] = trim(substr($elements[0], 0, $pos));
            $cookie['value'] = trim(substr($elements[0], $pos + 1));

            for ($i = 1; $i < count($elements); $i++) {
                if (!str_contains($elements[$i], '=')) {
                    $elName = trim($elements[$i]);
                    $elValue = null;
                } else {
                    [$elName, $elValue] = array_map('trim', explode('=', $elements[$i]));
                }
                $elName = strtolower($elName);
                if ('secure' == $elName) {
                    $cookie['secure'] = true;
                } elseif ('expires' == $elName) {
                    $cookie['expires'] = str_replace('"', '', $elValue);
                } elseif ('path' == $elName || 'domain' == $elName) {
                    $cookie[$elName] = urldecode($elValue);
                } else {
                    $cookie[$elName] = $elValue;
                }
            }
        }
        $this->_cookies[] = $cookie;
    }
}
