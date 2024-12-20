<?php

$HOME = realpath(__DIR__) . '/../../../..';
require_once $HOME . '/tests/class/helper/SC_Helper_Kiyaku/SC_Helper_Kiyaku_TestBase.php';

class SC_Helper_Kiyaku_getListTest extends SC_Helper_Kiyaku_TestBase
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

    public function testgetListTest削除した商品も含んだ一覧を取得できた場合一覧のarrayを返す()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $this->setUpKiyaku();
        $has_deleted = true;
        // 期待値
        $this->expected = [
            [
                'kiyaku_id' => '1000',
                'kiyaku_title' => 'test1',
                'kiyaku_text' => 'test_text',
            ],
            [
                'kiyaku_id' => '1001',
                'kiyaku_title' => 'test2',
                'kiyaku_text' => 'test_text2',
            ],
            [
                'kiyaku_id' => '1002',
                'kiyaku_title' => 'test3',
                'kiyaku_text' => 'test_text',
            ],
        ];

        $this->actual = $this->objKiyaku->getList($has_deleted);
        $this->verify('規約一覧取得');
    }

    public function testgetListTest一覧を取得できた場合削除した商品は取得しない一覧のarrayを返す()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $this->setUpKiyaku();
        $has_deleted = false;
        // 期待値
        $this->expected = [
            [
                'kiyaku_id' => '1000',
                'kiyaku_title' => 'test1',
                'kiyaku_text' => 'test_text',
            ],
            [
                'kiyaku_id' => '1001',
                'kiyaku_title' => 'test2',
                'kiyaku_text' => 'test_text2',
            ],
        ];

        $this->actual = $this->objKiyaku->getList($has_deleted);
        $this->verify('規約一覧取得');
    }
}
