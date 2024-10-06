<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_Kiyaku/SC_Helper_Kiyaku_TestBase.php';

class SC_Helper_Kiyaku_rankUpTest extends SC_Helper_Kiyaku_TestBase
{
    protected function setUp()
    {
        parent::setUp();
        $this->objKiyaku = new SC_Helper_Kiyaku_Ex();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////

    public function testrankUpTestランクアップができた場合ランクを返す()
    {
        $objQuery = &SC_Query_Ex::getSingletonInstance();
        $this->setUpKiyaku();
        $kiyaku_id = 1001;

        // 期待値
        $this->expected = '12';

        $this->objKiyaku->rankUp($kiyaku_id);

        $col = 'rank';
        $from = 'dtb_kiyaku';
        $where = 'kiyaku_id = ?';
        $whereVal = [$kiyaku_id];
        $res = $objQuery->getCol($col, $from, $where, $whereVal);
        $this->actual = $res[0];
        $this->verify('ランクアップ');
    }
}
