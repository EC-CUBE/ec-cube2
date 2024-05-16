<?php

class SC_ResponseWithHeaderTest extends Common_TestCase
{
    /** @var resource|bool */
    private static $server;
    const FIXTURES_DIR = '../fixtures/server';

    public static function setUpBeforeClass()
    {
        $spec = [
            1 => ['file', '/dev/null', 'w'],
            2 => ['file', '/dev/null', 'w']
        ];

        if (!self::$server = @proc_open('exec php -S 127.0.0.1:8053', $spec, $pipes, __DIR__.'/'.self::FIXTURES_DIR)) {
            self::markTestSkipped('PHP server unable to start.');
        }
        sleep(1);
    }

    public static function tearDownAfterClass()
    {
        if (is_resource(self::$server)) {
            proc_terminate(self::$server);
            proc_close(self::$server);
        }
    }

    private function file_get_contents($url)
    {
        $context = stream_context_create(
            [
                'http' => [
                    'follow_location' => 0,
                ],
            ]
        );

        $contents = file_get_contents($url, false, $context);

        return $contents;
    }

    private function getExpectedContents($url, $additional_query_strings = '')
    {
        $contents = file_get_contents(__DIR__ . '/' . self::FIXTURES_DIR . '/sc_response_reload.expected');

        $url .= '';

        if (strlen($additional_query_strings) >= 1) {
            $url .= '&' . $additional_query_strings;
        }

        $contents = str_replace('{url}', $url, $contents);

        return $contents;
    }

    public function testReload_transactionidが絡まない()
    {
        $request_url    = 'http://127.0.0.1:8053/sc_response_reload.php?debug=' . urlencode('テスト');
        $expected_url   = $request_url . '&redirect=1';
        $expected = $this->getExpectedContents($expected_url);

        $actual = $this->file_get_contents($request_url);
        self::assertSame($expected, $actual);
    }

    public function testReload_リクエストにtransactionidを含む()
    {
        $request_url    = 'http://127.0.0.1:8053/sc_response_reload.php?debug=' . urlencode('テスト') . '&' . TRANSACTION_ID_NAME . '=on_reqest';
        $expected_url   = $request_url . '&redirect=1';
        $expected = $this->getExpectedContents($expected_url);

        $actual = $this->file_get_contents($request_url);
        self::assertSame($expected, $actual);
    }

    public function testReload_ロジックにtransactionidを含む()
    {
        $request_url    = 'http://127.0.0.1:8053/sc_response_reload_add_transactionid.php?debug=' . urlencode('テスト');
        $expected_url   = $request_url . '&redirect=1&' . TRANSACTION_ID_NAME . '=on_logic';
        $expected = $this->getExpectedContents($expected_url);

        $actual = $this->file_get_contents($request_url);
        self::assertSame($expected, $actual);
    }

    public function testReload_ロジック・リクエストにtransactionidを含む()
    {
        $base_url       = 'http://127.0.0.1:8053/sc_response_reload_add_transactionid.php?debug=' . urlencode('テスト');
        $request_url    = $base_url;
        $request_url    .= '&' . TRANSACTION_ID_NAME . '=on_reqest';
        $expected_url   = $base_url;
        $expected_url   .= '&' . TRANSACTION_ID_NAME . '=on_logic';
        $expected_url   .= '&redirect=1';
        $expected = $this->getExpectedContents($expected_url);

        $actual = $this->file_get_contents($request_url);
        self::assertSame($expected, $actual);
    }
}
