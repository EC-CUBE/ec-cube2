<?php

require_once __DIR__.'/SC_Helper_Delivery_TestBase.php';

/**
 * SC_Helper_Delivery::getDelivTime()のテストクラス.
 */
class SC_Helper_Delivery_getDelivTimeTest extends SC_Helper_Delivery_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の配送時間を作成
        $this->createDelivTimeData(1, [
            1 => '午前中',
            2 => '12-14時',
            3 => '14-16時',
            5 => '18-20時',  // time_idが連続していない場合
        ]);
    }

    public function testGetDelivTime配送時間を取得()
    {
        $result = SC_Helper_Delivery::getDelivTime(1);

        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertEquals('午前中', $result[1]);
        $this->assertEquals('12-14時', $result[2]);
        $this->assertEquals('14-16時', $result[3]);
        $this->assertEquals('18-20時', $result[5]);
    }

    public function testGetDelivTime配送時間が存在しない場合()
    {
        $result = SC_Helper_Delivery::getDelivTime(9999);

        $this->assertIsArray($result);
        $this->assertEmpty($result, '配送時間が存在しない場合は空配列');
    }

    public function testGetDelivTimeソート順がtime_id昇順()
    {
        $result = SC_Helper_Delivery::getDelivTime(1);

        $keys = array_keys($result);
        $this->assertEquals([1, 2, 3, 5], $keys, 'time_id昇順でソートされる');
    }
}
