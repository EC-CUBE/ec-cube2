<?php

class SC_ResponseWithHeaderTest extends Common_TestCase
{
    /** @var resource|bool */
    private static $server;
    public const FIXTURES_DIR = '../fixtures/server';

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

    public function testReload()
    {
        $context = stream_context_create(
            [
                'http' => [
                    'follow_location' => false,
                ],
            ]
        );
        $actual = file_get_contents('http://127.0.0.1:8085/sc_response_reload.php', false, $context);
        self::assertStringEqualsFile(__DIR__.'/'.self::FIXTURES_DIR.'/sc_response_reload.expected', $actual);
    }
}
