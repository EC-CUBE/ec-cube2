<?php

class SC_ResponseSendRedirectWithHeaderTest extends Common_TestCase
{
    /** @var resource|bool */
    private static $server;
    const FIXTURES_DIR = '../fixtures/server';

    public static function setUpBeforeClass(): void
    {
        $spec = [
            1 => ['file', '/dev/null', 'w'],
            2 => ['file', '/dev/null', 'w']
        ];

        if (!self::$server = @proc_open('exec php -S 127.0.0.1:8085', $spec, $pipes, __DIR__.'/'.self::FIXTURES_DIR)) {
            self::markTestSkipped('PHP server unable to start.');
        }
        sleep(1);
    }

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$server)) {
            proc_terminate(self::$server);
            proc_close(self::$server);
        }
    }

    /**
     * @param array $arrPostData
     * @param array $arrTestHeader エスケープせず HTTP ヘッダーに埋め込むので注意。
     * @param array|null $arrPostData
     * @return void
     */
    private function request($arrQuery = [], $arrTestHeader = [], $arrPostData = null)
    {
        $netUrl = new Net_URL('http://127.0.0.1:8085/sc_response_sendRedirect.php');
        $netUrl->querystring = $arrQuery;
        $url = $netUrl->getUrl();

        $arrOptions = [
            'http' => [
                'follow_location' => 0,
                'header' => [],
            ],
        ];

        if (isset($arrPostData)) {
            $arrOptions['http']['method'] = 'POST';
            $arrOptions['http']['header'][] = 'Content-Type: application/x-www-form-urlencoded';
            $arrOptions['http']['content'] = http_build_query($arrPostData, '', '&');
        }
        foreach ($arrTestHeader as $key => $value) {
            $arrOptions['http']['header'][] = "X-Test-{$key}: {$value}";
        }

        $contents = file_get_contents($url, false, stream_context_create($arrOptions));

        return $contents;
    }

    /**
     * @param array $arrQuerystring
     * @return string
     */
    private function getExpectedContents($arrQuerystring = [])
    {
        $netUrl = new Net_URL('http://127.0.0.1:8085/redirect_url.php');
        $netUrl->querystring = $arrQuerystring;
        $url = $netUrl->getUrl();

        $contents = file_get_contents(__DIR__ . '/' . self::FIXTURES_DIR . '/sc_response_sendRedirect.expected');
        $contents = str_replace('{url}', $url, $contents);

        return $contents;
    }

    /**
     * 以下は、sendRedirect で transactionid が付加されないパターン。
     */
    public function testSendRedirect_Admin_GRG_transactionidなし_遷移先にmode()
    {
        $arrQuery = [
        ];
        $arrTestHeader = [
            'function' => 'admin',
            'dst_mode' => 'hoge',
        ];
        $actual = $this->request($arrQuery, $arrTestHeader);

        $expected = $this->getExpectedContents([
            'mode' => 'hoge',
        ]);

        self::assertSame($expected, $actual);
    }

    public function testSendRedirect_Admin_PRG_リクエストにtransactionid_modeなし()
    {
        $arrQuery = [
            TRANSACTION_ID_NAME => 'on_reqest_query',
        ];
        $arrTestHeader = [
            'function' => 'admin',
        ];
        $arrPostData    = [
            'foo' => 'bar',
            TRANSACTION_ID_NAME => 'on_reqest_post',
        ];
        $actual = $this->request($arrQuery, $arrTestHeader, $arrPostData);

        $expected = $this->getExpectedContents();

        self::assertSame($expected, $actual);
    }

    public function testSendRedirect_Front_GRG_リクエストにtransactionid_遷移先にmode()
    {
        $arrQuery = [
            TRANSACTION_ID_NAME => 'on_reqest_query',
        ];
        $arrTestHeader = [
            'function' => 'front',
            'dst_mode' => 'hoge',
        ];
        $actual = $this->request($arrQuery, $arrTestHeader);

        $expected = $this->getExpectedContents([
            'mode' => 'hoge',
        ]);

        self::assertSame($expected, $actual);
    }

    public function testSendRedirect_Front_PRG_リクエストにtransactionid_遷移先にmode()
    {
        $arrQuery = [
            TRANSACTION_ID_NAME => 'on_reqest_query',
        ];
        $arrTestHeader = [
            'function' => 'front',
            'dst_mode' => 'hoge',
        ];
        $arrPostData    = [
            'foo' => 'bar',
            TRANSACTION_ID_NAME => 'on_reqest_post',
        ];
        $actual = $this->request($arrQuery, $arrTestHeader, $arrPostData);

        $expected = $this->getExpectedContents([
            'mode' => 'hoge',
        ]);

        self::assertSame($expected, $actual);
    }

    /**
     * 以下は、sendRedirect で リクエストの transactionid がリダイレクト先に引き継がれるパターン。
     */
    public function testSendRedirect_Admin_GRG_リクエストにtransactionid_遷移先にmode()
    {
        $arrQuery = [
            TRANSACTION_ID_NAME => 'on_reqest_query',
        ];
        $arrTestHeader = [
            'function' => 'admin',
            'dst_mode' => 'hoge',
        ];
        $actual = $this->request($arrQuery, $arrTestHeader);

        $expected = $this->getExpectedContents([
            'mode' => 'hoge',
            TRANSACTION_ID_NAME => 'on_reqest_query',
        ]);

        self::assertSame($expected, $actual);
    }

    public function testSendRedirect_Admin_PRG_リクエストにtransactionid_遷移先にmode()
    {
        $arrQuery = [
            TRANSACTION_ID_NAME => 'on_reqest_query',
        ];
        $arrTestHeader = [
            'function' => 'admin',
            'dst_mode' => 'hoge',
        ];
        $arrPostData    = [
            'foo' => 'bar',
            TRANSACTION_ID_NAME => 'on_reqest_post',
        ];
        $actual = $this->request($arrQuery, $arrTestHeader, $arrPostData);

        $expected = $this->getExpectedContents([
            'mode' => 'hoge',
            TRANSACTION_ID_NAME => 'on_reqest_post',
        ]);

        self::assertSame($expected, $actual);
    }

    public function testSendRedirect_Admin_GRG_リクエストにtransactionid_modeなし_クエリ継承()
    {
        $arrQuery = [
            TRANSACTION_ID_NAME => 'on_reqest_query',
        ];
        $arrTestHeader = [
            'function' => 'admin',
            'inherit_query_string' => '1',
        ];
        $actual = $this->request($arrQuery, $arrTestHeader);

        $expected = $this->getExpectedContents([
            TRANSACTION_ID_NAME => 'on_reqest_query',
        ]);

        self::assertSame($expected, $actual);
    }

    public function testSendRedirect_Admin_PRG_リクエストにtransactionid_modeなし_クエリ継承()
    {
        $arrQuery = [
            TRANSACTION_ID_NAME => 'on_reqest_query',
        ];
        $arrTestHeader = [
            'function' => 'admin',
            'inherit_query_string' => '1',
        ];
        $arrPostData    = [
            'foo' => 'bar',
            TRANSACTION_ID_NAME => 'on_reqest_post',
        ];
        $actual = $this->request($arrQuery, $arrTestHeader, $arrPostData);

        $expected = $this->getExpectedContents([
            TRANSACTION_ID_NAME => 'on_reqest_query',
        ]);

        self::assertSame($expected, $actual);
    }

    /**
     * 以下は、sendRedirect で ロジックの transactionid がリダイレクト先に渡るパターン。
     *
     * 通常無さそうなケースだが、仕様として持っている動作。リダイレクトのタイミングで transactionid を更新する用途を想定。
     */
    public function testSendRedirect_Admin_GRG_ロジック・リクエストにtransactionid_遷移先にmode()
    {
        $arrQuery = [
            TRANSACTION_ID_NAME => 'on_reqest_query',
        ];
        $arrTestHeader = [
            'function' => 'admin',
            'dst_mode' => 'hoge',
            'logic_transaction_id' => 'on_logic',
        ];
        $actual = $this->request($arrQuery, $arrTestHeader);

        $expected = $this->getExpectedContents([
            'mode' => 'hoge',
            TRANSACTION_ID_NAME => 'on_logic',
        ]);

        self::assertSame($expected, $actual);
    }

    public function testSendRedirect_Admin_PRG_ロジック・リクエストにtransactionid_遷移先にmode()
    {
        $arrQuery = [
            TRANSACTION_ID_NAME => 'on_reqest_query',
        ];
        $arrTestHeader = [
            'function' => 'admin',
            'dst_mode' => 'hoge',
            'logic_transaction_id' => 'on_logic',
        ];
        $arrPostData    = [
            'foo' => 'bar',
            TRANSACTION_ID_NAME => 'on_reqest_post',
        ];
        $actual = $this->request($arrQuery, $arrTestHeader, $arrPostData);

        $expected = $this->getExpectedContents([
            'mode' => 'hoge',
            TRANSACTION_ID_NAME => 'on_logic',
        ]);

        self::assertSame($expected, $actual);
    }
}
