<?php

require_once __DIR__.'/SC_Helper_Delivery_TestBase.php';

/**
 * SC_Helper_Delivery::delete()のテストクラス.
 */
class SC_Helper_Delivery_deleteTest extends SC_Helper_Delivery_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の配送方法を作成（rank順）
        $this->createDelivData(['deliv_id' => 1, 'service_name' => '配送A', 'rank' => 1]);
        $this->createDelivData(['deliv_id' => 2, 'service_name' => '配送B', 'rank' => 2]);
        $this->createDelivData(['deliv_id' => 3, 'service_name' => '配送C', 'rank' => 3]);
    }

    public function testDelete配送方法が削除される()
    {
        $this->objHelper->delete(2);

        $result = $this->objQuery->get('del_flg', 'dtb_deliv', 'deliv_id = ?', [2]);

        $this->assertEquals(1, $result, '配送方法が論理削除される（del_flg=1）');
    }

    public function testDelete他の配送方法のランクが調整される()
    {
        $this->objHelper->delete(2);

        $deliv1 = $this->objQuery->get('rank', 'dtb_deliv', 'deliv_id = ?', [1]);
        $deliv3 = $this->objQuery->get('rank', 'dtb_deliv', 'deliv_id = ?', [3]);

        $this->assertEquals(1, $deliv1, '下位ランクは変更されない');
        $this->assertEquals(2, $deliv3, '上位ランクは詰められる');
    }

    public function testDelete論理削除なので件数は変わらない()
    {
        $before = $this->objQuery->count('dtb_deliv');
        $this->objHelper->delete(2);
        $after = $this->objQuery->count('dtb_deliv');

        $this->assertEquals(3, $before);
        $this->assertEquals(3, $after, '論理削除なので件数は変わらない');

        $active_count = $this->objQuery->count('dtb_deliv', 'del_flg = 0');
        $this->assertEquals(2, $active_count, '有効な配送方法は2件になる');
    }
}
