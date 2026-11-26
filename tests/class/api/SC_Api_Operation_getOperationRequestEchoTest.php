<?php

class SC_Api_Operation_getOperationRequestEchoTest extends Common_TestCase
{
    private $hasUserAgent;

    private $userAgent;

    protected function setUp(): void
    {
        $this->hasUserAgent = array_key_exists('HTTP_USER_AGENT', $_SERVER);
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->hasUserAgent) {
            $_SERVER['HTTP_USER_AGENT'] = $this->userAgent;
        } else {
            unset($_SERVER['HTTP_USER_AGENT']);
        }
    }

    public function testDoesNotAddHeaderWhenUserAgentIsNotSet()
    {
        unset($_SERVER['HTTP_USER_AGENT']);

        $actual = SC_Api_Operation_getOperationRequestEchoAccessor::invoke([], 123.0);

        $this->assertSame([], $actual['HTTPHeaders']);
    }

    public function testKeepsUserAgentUnescaped()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Browser <test> & "quoted"';

        $actual = SC_Api_Operation_getOperationRequestEchoAccessor::invoke([], 123.0);

        $this->assertSame(
            $_SERVER['HTTP_USER_AGENT'],
            $actual['HTTPHeaders']['Header'][0]['_attributes']['Value']
        );
    }

    public function testAddsUnescapedArgumentsUnderArgumentElement()
    {
        $actual = SC_Api_Operation_getOperationRequestEchoAccessor::invoke(
            ['parameter<1>' => 'value & "quoted"'],
            123.0
        );

        $this->assertSame(
            [
                'Argument' => [
                    [
                        '_attributes' => [
                            'Name' => 'parameter<1>',
                            'Value' => 'value & "quoted"',
                        ],
                    ],
                ],
            ],
            $actual['Arguments']
        );
    }
}

class SC_Api_Operation_getOperationRequestEchoAccessor extends SC_Api_Operation
{
    public static function invoke($arrParam, $startTime)
    {
        return parent::getOperationRequestEcho($arrParam, $startTime);
    }
}
