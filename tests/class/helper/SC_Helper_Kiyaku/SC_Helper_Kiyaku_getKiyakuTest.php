<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_Kiyaku/SC_Helper_Kiyaku_TestBase.php';

class SC_Helper_Kiyaku_getKiyakuTest extends SC_Helper_Kiyaku_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->objKiyaku = new SC_Helper_Kiyaku_Ex();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////

    public function testgetKiyakuTest規約情報を取得できた場合規約のarrayを返す()
    {
        $objQuery = &SC_Query_Ex::getSingletonInstance();
        $this->setUpKiyaku();
        $has_deleted = false;
        $kiyaku_id = 1000;
        // 期待値
        $this->expected = [
            'kiyaku_id' => '1000',
            'kiyaku_title' => 'test1',
            'kiyaku_text' => 'test_text',
            'rank' => '12',
            'creator_id' => '0',
            'create_date' => '2000-01-01 00:00:00',
            'update_date' => '2000-01-01 00:00:00',
            'del_flg' => '0',
                                ];

        $this->actual = $this->objKiyaku->getKiyaku($kiyaku_id, $has_deleted);
        $this->verify('規約詳細取得');
    }

    public function testgetKiyakuTest規約情報を規約idから取得する際削除された規約を指定した場合Nullを返す()
    {
        $objQuery = &SC_Query_Ex::getSingletonInstance();
        $this->setUpKiyaku();
        $has_deleted = false;
        $kiyaku_id = 1002;
        // 期待値
        $this->expected = null;

        $this->actual = $this->objKiyaku->getKiyaku($kiyaku_id, $has_deleted);
        $this->verify('規約詳細取得');
    }

    public function testgetKiyakuTest削除された情報を含む規約情報を規約idから取得する際削除された規約を指定した場合Nullを返す()
    {
        $objQuery = &SC_Query_Ex::getSingletonInstance();
        $this->setUpKiyaku();
        $has_deleted = true;
        $kiyaku_id = 1002;
        // 期待値
        $this->expected = [
                'kiyaku_id' => '1002',
                'kiyaku_title' => 'test3',
                'kiyaku_text' => 'test_text',
                'rank' => '10',
                'creator_id' => '0',
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
                'del_flg' => '1',
                                ];

        $this->actual = $this->objKiyaku->getKiyaku($kiyaku_id, $has_deleted);
        $this->verify('規約詳細取得');
    }
}
