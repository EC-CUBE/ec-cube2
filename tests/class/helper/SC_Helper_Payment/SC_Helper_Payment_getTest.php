<?php

require_once __DIR__.'/SC_Helper_Payment_TestBase.php';

/**
 * SC_Helper_Payment::get(), getList()のテストクラス.
 */
class SC_Helper_Payment_getTest extends SC_Helper_Payment_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の支払方法を作成
        $this->createPaymentData(['payment_id' => 1, 'payment_method' => '銀行振込', 'rank' => 3, 'del_flg' => 0]);
        $this->createPaymentData(['payment_id' => 2, 'payment_method' => 'クレジットカード', 'rank' => 2, 'del_flg' => 0]);
        $this->createPaymentData(['payment_id' => 3, 'payment_method' => '代金引換', 'rank' => 1, 'del_flg' => 0]);
        $this->createPaymentData(['payment_id' => 4, 'payment_method' => '削除済み', 'rank' => 4, 'del_flg' => 1]);
    }

    public function testGet支払方法を取得()
    {
        $result = $this->objHelper->get(1);

        $this->assertEquals('銀行振込', $result['payment_method']);
        $this->assertEquals(0, $result['del_flg']);
    }

    public function testGet削除済みは取得されない()
    {
        $result = $this->objHelper->get(4);

        $this->assertEmpty($result, '削除済みの支払方法は取得されない');
    }

    public function testGet削除済みを含む場合は取得される()
    {
        $result = $this->objHelper->get(4, true);

        $this->assertEquals('削除済み', $result['payment_method']);
    }

    public function testGetList支払方法一覧を取得()
    {
        $result = $this->objHelper->getList();

        $this->assertCount(3, $result, '削除されていない支払方法が3件');
    }

    public function testGetListランク降順でソートされる()
    {
        $result = $this->objHelper->getList();

        $this->assertEquals('銀行振込', $result[0]['payment_method'], 'rank=3が最初');
        $this->assertEquals('クレジットカード', $result[1]['payment_method'], 'rank=2が2番目');
        $this->assertEquals('代金引換', $result[2]['payment_method'], 'rank=1が3番目');
    }

    public function testGetList削除済みを含む()
    {
        $result = $this->objHelper->getList(true);

        $this->assertCount(4, $result, '削除済みを含めて4件');
    }

    public function testGetList必要なカラムが含まれる()
    {
        $result = $this->objHelper->getList();

        $this->assertArrayHasKey('payment_id', $result[0]);
        $this->assertArrayHasKey('payment_method', $result[0]);
        $this->assertArrayHasKey('charge', $result[0]);
    }
}
