<?php

class SC_ResponseTest extends Common_TestCase
{
    public function testActionExit()
    {
        $phpunit = $this;

        $objPlugin = SC_Helper_Plugin_Ex::getSingletonInstance();
        $objPlugin->arrRegistedPluginActions['SC_ResponseTest_action_mode'][] = [['function' => function ($instance) use ($phpunit) {
            $phpunit->assertInstanceOf('SC_ResponseTest', $instance, 'backtrace から取得した呼び出し元のインスタンスが渡ってくるはず');
        }]];

        SC_Response_Wrapper::actionExit();
    }

    public function testSendRedirect()
    {
        $phpunit = $this;

        $objPlugin = SC_Helper_Plugin_Ex::getSingletonInstance();
        $objPlugin->arrRegistedPluginActions['SC_ResponseTest_action_mode'][] = [['function' => function ($instance) use ($phpunit) {
            $phpunit->assertInstanceOf('SC_ResponseTest', $instance, 'backtrace から取得した呼び出し元のインスタンスが渡ってくるはず');
        }]];

        SC_Response_Wrapper::sendRedirect(HTTP_URL);
    }

    public function getMode()
    {
        return 'mode';
    }
}

class SC_Response_Wrapper extends SC_Response_Ex
{
    protected static function exitWrapper()
    {
        // quiet
    }
}
