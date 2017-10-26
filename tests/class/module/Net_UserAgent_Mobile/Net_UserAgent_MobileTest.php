<?php
$HOME = realpath(dirname(__FILE__)) . "/../../../..";
require_once($HOME . "/tests/class/Common_TestCase.php");

/**
 * Net_UserAgent_MobileTest
 *
 * @copyright
 * @author Nobuhiko Kimoto <info@nob-log.info>
 * @license
 */
class Net_UserAgent_MobileTest extends Common_TestCase
{
    public function testConstructorSetsDefaults()
    {
        $nu = new Net_UserAgent_Mobile();
        $this->assertEquals(false, $nu->isMobile());
    }

    public function testMobile()
    {
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $_SERVER['HTTP_USER_AGENT'] = 'DoCoMo/2.0 F901iC(c100;TB;W23H12)';

        $nu = new Net_UserAgent_Mobile();
        $this->assertEquals(true, $nu->isMobile());
        $this->assertEquals(true, $nu->isDocomo());

        // 念のため戻す
        $_SERVER['HTTP_USER_AGENT'] = $ua;
    }
}
