<?php

require_once __DIR__.'/SC_Helper_Delivery_TestBase.php';

/**
 * SC_Helper_Delivery::getPayments()のテストクラス.
 */
class SC_Helper_Delivery_getPaymentsTest extends SC_Helper_Delivery_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の支払方法オプションを作成
        $this->objQuery->insert('dtb_payment_options', ['deliv_id' => 1, 'payment_id' => 3, 'rank' => 1]);
        $this->objQuery->insert('dtb_payment_options', ['deliv_id' => 1, 'payment_id' => 1, 'rank' => 2]);
        $this->objQuery->insert('dtb_payment_options', ['deliv_id' => 1, 'payment_id' => 2, 'rank' => 3]);
        $this->objQuery->insert('dtb_payment_options', ['deliv_id' => 2, 'payment_id' => 5, 'rank' => 1]);
    }

    public function testGetPayments支払方法IDを取得()
    {
        $result = SC_Helper_Delivery::getPayments(1);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function testGetPaymentsランク順でソートされる()
    {
        $result = SC_Helper_Delivery::getPayments(1);

        $this->assertEquals([3, 1, 2], $result, 'rank昇順でソートされる');
    }

    public function testGetPayments支払方法が存在しない場合()
    {
        $result = SC_Helper_Delivery::getPayments(9999);

        $this->assertIsArray($result);
        $this->assertEmpty($result, '支払方法が存在しない場合は空配列');
    }

    public function testGetPayments複数の配送方法を識別()
    {
        $result1 = SC_Helper_Delivery::getPayments(1);
        $result2 = SC_Helper_Delivery::getPayments(2);

        $this->assertNotEquals($result1, $result2, '異なる配送方法IDで異なる結果');
        $this->assertEquals([5], $result2);
    }
}
