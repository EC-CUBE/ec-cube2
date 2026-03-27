<?php

require_once __DIR__.'/SC_Helper_Delivery_TestBase.php';

/**
 * SC_Helper_Delivery::get()のテストクラス.
 */
class SC_Helper_Delivery_getTest extends SC_Helper_Delivery_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の配送方法を作成
        $this->createDelivData(['deliv_id' => 1, 'service_name' => '配送A', 'del_flg' => 0]);
        $this->createDelivTimeData(1, [1 => '午前中', 2 => '14-16時']);
        $this->createDelivFeeData(1, [13 => 500, 27 => 800]);
        $this->createPaymentOptionsData(1, [1, 2, 3]);

        // 削除済みの配送方法を作成
        $this->createDelivData(['deliv_id' => 2, 'service_name' => '配送B削除済み', 'del_flg' => 1]);
    }

    public function testGet配送方法の詳細を取得()
    {
        $result = $this->objHelper->get(1);

        $this->assertEquals('配送A', $result['service_name']);
        $this->assertArrayHasKey('deliv_time', $result, '配送時間が含まれる');
        $this->assertArrayHasKey('deliv_fee', $result, '配送料金が含まれる');
        $this->assertArrayHasKey('payment_ids', $result, '支払方法が含まれる');
    }

    public function testGet配送時間が正しく取得される()
    {
        $result = $this->objHelper->get(1);

        $this->assertIsArray($result['deliv_time']);
        $this->assertCount(2, $result['deliv_time']);
        $this->assertEquals('午前中', $result['deliv_time'][1]);
        $this->assertEquals('14-16時', $result['deliv_time'][2]);
    }

    public function testGet配送料金が正しく取得される()
    {
        $result = $this->objHelper->get(1);

        $this->assertIsArray($result['deliv_fee']);
        $this->assertCount(2, $result['deliv_fee']);
        $this->assertEquals(500, $result['deliv_fee'][0]['fee']);
        $this->assertEquals(800, $result['deliv_fee'][1]['fee']);
    }

    public function testGet支払方法が正しく取得される()
    {
        $result = $this->objHelper->get(1);

        $this->assertIsArray($result['payment_ids']);
        $this->assertCount(3, $result['payment_ids']);
        $this->assertEquals([1, 2, 3], $result['payment_ids']);
    }

    public function testGet削除済みの配送方法は取得されない()
    {
        $result = $this->objHelper->get(2);

        $this->assertEmpty($result, '削除済みの配送方法は空配列');
    }

    public function testGet削除済みを含む場合は取得される()
    {
        $result = $this->objHelper->get(2, true);

        $this->assertNotEmpty($result);
        $this->assertEquals('配送B削除済み', $result['service_name']);
    }

    public function testGet存在しない配送方法IDの場合()
    {
        $result = $this->objHelper->get(9999);

        $this->assertEmpty($result, '存在しない配送方法IDの場合は空配列');
    }
}
