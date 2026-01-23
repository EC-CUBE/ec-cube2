<?php

require_once __DIR__.'/SC_Helper_Bloc_TestBase.php';

/**
 * SC_Helper_Bloc::getDeviceTypeID()のテストクラス.
 */
class SC_Helper_Bloc_getDeviceTypeIDTest extends SC_Helper_Bloc_TestBase
{
    public function testGetDeviceTypeIDデフォルトはPC()
    {
        $helper = new SC_Helper_Bloc_Ex();

        $this->assertEquals(DEVICE_TYPE_PC, $helper->getDeviceTypeID());
    }

    public function testGetDeviceTypeIDコンストラクタで指定したデバイスタイプが返る()
    {
        $helper = new SC_Helper_Bloc_Ex(DEVICE_TYPE_MOBILE);

        $this->assertEquals(DEVICE_TYPE_MOBILE, $helper->getDeviceTypeID());
    }

    public function testGetDeviceTypeIDスマートフォンを指定()
    {
        $helper = new SC_Helper_Bloc_Ex(DEVICE_TYPE_SMARTPHONE);

        $this->assertEquals(DEVICE_TYPE_SMARTPHONE, $helper->getDeviceTypeID());
    }
}
