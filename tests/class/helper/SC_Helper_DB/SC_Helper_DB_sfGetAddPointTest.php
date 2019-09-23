<?php

class SC_Helper_DB_sfGetAddPointTest extends SC_Helper_DB_TestBase
{
    /** @var int */
    const POINT_RATE = 4;

    protected function setUp()
    {
        parent::setUp();
        $this->objQuery->update('dtb_baseinfo', ['point_rate' => self::POINT_RATE], 'id = 1');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSfGetAddPoint()
    {
        $totalpoint = 100;
        $use_point = 2000;

        $this->expected = 20;   // 100 - (2000 * (POINT_RATE / 100))
        $this->actual = SC_Helper_DB_Ex::sfGetAddPoint($totalpoint, $use_point);

        $this->verify();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSfGetAddPointWithMinus()
    {
        $totalpoint = 70;
        $use_point = 2000;

        $this->expected = 0;    // -10 = 100 - (2000 * (POINT_RATE / 100))
        $this->actual = SC_Helper_DB_Ex::sfGetAddPoint($totalpoint, $use_point);

        $this->verify();
    }
}
