<?php

require_once __DIR__.'/SC_Helper_Delivery_TestBase.php';

/**
 * SC_Helper_Delivery::getDelivFeeList()のテストクラス.
 */
class SC_Helper_Delivery_getDelivFeeListTest extends SC_Helper_Delivery_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の配送料金を作成（pref順でソートされることを確認するため）
        $fees = [
            ['fee_id' => 1, 'deliv_id' => 1, 'pref' => 27, 'fee' => 800],
            ['fee_id' => 2, 'deliv_id' => 1, 'pref' => 13, 'fee' => 500],
            ['fee_id' => 3, 'deliv_id' => 1, 'pref' => 14, 'fee' => 600],
            ['fee_id' => 4, 'deliv_id' => 2, 'pref' => 13, 'fee' => 1000],
        ];

        foreach ($fees as $fee) {
            $this->objQuery->insert('dtb_delivfee', $fee);
        }
    }

    public function testGetDelivFeeList配送料金一覧を取得()
    {
        $result = SC_Helper_Delivery::getDelivFeeList(1);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function testGetDelivFeeListソート順がpref昇順()
    {
        $result = SC_Helper_Delivery::getDelivFeeList(1);

        $this->assertEquals(13, $result[0]['pref'], '1番目は東京(13)');
        $this->assertEquals(14, $result[1]['pref'], '2番目は神奈川(14)');
        $this->assertEquals(27, $result[2]['pref'], '3番目は大阪(27)');
    }

    public function testGetDelivFeeList必要なカラムが含まれる()
    {
        $result = SC_Helper_Delivery::getDelivFeeList(1);

        $this->assertArrayHasKey('fee_id', $result[0]);
        $this->assertArrayHasKey('fee', $result[0]);
        $this->assertArrayHasKey('pref', $result[0]);
    }

    public function testGetDelivFeeList配送方法IDで正しくフィルタリングされる()
    {
        $result1 = SC_Helper_Delivery::getDelivFeeList(1);
        $result2 = SC_Helper_Delivery::getDelivFeeList(2);

        $this->assertCount(3, $result1);
        $this->assertCount(1, $result2);
        $this->assertEquals(1000, $result2[0]['fee']);
    }

    public function testGetDelivFeeList配送料金が存在しない場合()
    {
        $result = SC_Helper_Delivery::getDelivFeeList(9999);

        $this->assertIsArray($result);
        $this->assertEmpty($result, '配送料金が存在しない場合は空配列');
    }
}
