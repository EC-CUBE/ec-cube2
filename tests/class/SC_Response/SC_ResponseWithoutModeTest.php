<?php

class SC_ResponseWithoutModeTest extends Common_TestCase
{
    public function testSendRedirectWithPost()
    {
        $phpunit = $this;

        $objPlugin = SC_Helper_Plugin_Ex::getSingletonInstance();
        $_POST['mode'] = 'post';
        $objPlugin->arrRegistedPluginActions['SC_ResponseWithoutModeTest_action_post'][] = [['function' => function ($instance) use ($phpunit) {

            $phpunit->assertInstanceOf('SC_ResponseWithoutModeTest', $instance, 'backtrace から取得した呼び出し元のインスタンスが渡ってくるはず');

        }]];

        SC_Response_Wrapper2::sendRedirect(HTTP_URL);
    }

    public function testSendRedirectWithGet()
    {
        $phpunit = $this;

        $objPlugin = SC_Helper_Plugin_Ex::getSingletonInstance();
        $_GET['mode'] = 'get';
        $objPlugin->arrRegistedPluginActions['SC_ResponseWithoutModeTest_action_get'][] = [['function' => function ($instance) use ($phpunit) {

            $phpunit->assertInstanceOf('SC_ResponseWithoutModeTest', $instance, 'backtrace から取得した呼び出し元のインスタンスが渡ってくるはず');

        }]];

        SC_Response_Wrapper2::sendRedirect(HTTP_URL);
    }
}

class SC_Response_Wrapper2 extends SC_Response_Ex
{
    protected static function exitWrapper()
    {
        // quiet
    }
}
