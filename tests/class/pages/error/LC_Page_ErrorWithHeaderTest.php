<?php

class LC_Page_ErrorWithHeaderTest extends Common_TestCase
{
    /** @var resource|bool */
    private static $server;
    public const FIXTURES_DIR = '../../fixtures/server';

    public static function setUpBeforeClass(): void
    {
        $spec = [
            1 => ['file', '/dev/null', 'w'],
            2 => ['file', '/dev/null', 'w'],
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
     * @return array<string, array{0: string, 1: int}>
     */
    public static function errorTypeProvider()
    {
        return [
            // お客様の操作によるエラーは 403
            'PAGE_ERROR' => ['PAGE_ERROR', 403],
            'CART_EMPTY' => ['CART_EMPTY', 403],
            'CART_ADD_ERROR' => ['CART_ADD_ERROR', 403],
            'CANCEL_PURCHASE' => ['CANCEL_PURCHASE', 403],
            'SITE_LOGIN_ERROR' => ['SITE_LOGIN_ERROR', 403],
            'TEMP_LOGIN_ERROR' => ['TEMP_LOGIN_ERROR', 403],
            'CUSTOMER_ERROR' => ['CUSTOMER_ERROR', 403],
            'SOLD_OUT' => ['SOLD_OUT', 403],
            'CART_NOT_FOUND' => ['CART_NOT_FOUND', 403],
            'LACK_POINT' => ['LACK_POINT', 403],
            'FAVORITE_ERROR' => ['FAVORITE_ERROR', 403],
            // 存在しないページは 404
            'PRODUCT_NOT_FOUND' => ['PRODUCT_NOT_FOUND', 404],
            'CATEGORY_NOT_FOUND' => ['CATEGORY_NOT_FOUND', 404],
            // サーバー側の失敗を含むものは 500 のまま
            'FREE_ERROR_MSG' => ['FREE_ERROR_MSG', 500],
            'DOWNFILE_NOT_FOUND' => ['DOWNFILE_NOT_FOUND', 500],
            'WRITE_FILE_ERROR' => ['WRITE_FILE_ERROR', 500],
        ];
    }

    /**
     * @dataProvider errorTypeProvider
     *
     * @param string $type 定数名
     * @param int $expected 期待する HTTP ステータスコード
     */
    public function testHttpStatus($type, $expected)
    {
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'header' => ['X-Test-type: '.$type],
            ],
        ]);
        file_get_contents('http://127.0.0.1:8085/lc_page_error.php', false, $context);

        $arrHeaders = function_exists('http_get_last_response_headers')
            ? http_get_last_response_headers()
            : $http_response_header;

        self::assertMatchesRegularExpression('/\AHTTP\/1\.[01] '.$expected.' /', $arrHeaders[0]);
    }
}
