<?php

require_once __DIR__.'/SC_Helper_Delivery_TestBase.php';

/**
 * SC_Helper_Delivery::getList()のテストクラス.
 */
class SC_Helper_Delivery_getListTest extends SC_Helper_Delivery_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        // テスト用の配送方法を作成
        $this->createDelivData(['deliv_id' => 1, 'service_name' => '配送A', 'product_type_id' => 1, 'rank' => 3, 'del_flg' => 0]);
        $this->createDelivData(['deliv_id' => 2, 'service_name' => '配送B', 'product_type_id' => 1, 'rank' => 2, 'del_flg' => 0]);
        $this->createDelivData(['deliv_id' => 3, 'service_name' => '配送C', 'product_type_id' => 2, 'rank' => 1, 'del_flg' => 0]);
        $this->createDelivData(['deliv_id' => 4, 'service_name' => '配送D削除済み', 'product_type_id' => 1, 'rank' => 4, 'del_flg' => 1]);
    }

    public function testGetList全配送方法を取得()
    {
        $result = $this->objHelper->getList();

        $this->assertCount(3, $result, '削除されていない配送方法が3件取得される');
        $this->assertEquals('配送A', $result[0]['service_name'], 'rankの降順で取得される（最初）');
        $this->assertEquals('配送B', $result[1]['service_name'], 'rankの降順で取得される（2番目）');
        $this->assertEquals('配送C', $result[2]['service_name'], 'rankの降順で取得される（3番目）');
    }

    public function testGetList商品種別でフィルタリング()
    {
        $result = $this->objHelper->getList(1);

        $this->assertCount(2, $result, '商品種別1の配送方法が2件取得される');
        $this->assertEquals('配送A', $result[0]['service_name']);
        $this->assertEquals('配送B', $result[1]['service_name']);
    }

    public function testGetList削除済みを含む()
    {
        $result = $this->objHelper->getList(null, true);

        $this->assertCount(4, $result, '削除済みを含めて4件取得される');
    }

    public function testGetList削除済みを含み商品種別でフィルタリング()
    {
        $result = $this->objHelper->getList(1, true);

        $this->assertCount(3, $result, '商品種別1の配送方法が削除済みを含めて3件取得される');
    }

    public function testGetList配送方法が存在しない場合()
    {
        $this->objQuery->delete('dtb_deliv');
        $result = $this->objHelper->getList();

        $this->assertIsArray($result);
        $this->assertEmpty($result, '配送方法が存在しない場合は空配列');
    }
}
