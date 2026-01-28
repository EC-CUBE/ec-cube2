<?php

require_once __DIR__.'/SC_Helper_Delivery_TestBase.php';

/**
 * SC_Helper_Delivery::save()のテストクラス.
 */
class SC_Helper_Delivery_saveTest extends SC_Helper_Delivery_TestBase
{
    public function testSave新規登録()
    {
        $sqlval = [
            'product_type_id' => 1,
            'service_name' => '新規配送',
            'confirm_url' => '',
            'status' => 1,
            'del_flg' => 0,
            'creator_id' => 1,
            'deliv_time' => [1 => '午前中', 2 => '12-14時'],
            'deliv_fee' => [
                ['fee_id' => 1, 'pref' => 13, 'fee' => 500],
                ['fee_id' => 2, 'pref' => 27, 'fee' => 800],
            ],
            'payment_ids' => [1, 2],
        ];

        $deliv_id = $this->objHelper->save($sqlval);

        $this->assertGreaterThan(0, $deliv_id, '配送IDが返される');

        $result = $this->objQuery->select('*', 'dtb_deliv', 'deliv_id = ?', [$deliv_id]);
        $this->assertCount(1, $result);
        $this->assertEquals('新規配送', $result[0]['service_name']);
    }

    public function testSave新規登録で配送時間が登録される()
    {
        $sqlval = [
            'product_type_id' => 1,
            'service_name' => '新規配送',
            'status' => 1,
            'del_flg' => 0,
            'creator_id' => 1,
            'deliv_time' => [1 => '午前中', 2 => '12-14時'],
            'deliv_fee' => [],
            'payment_ids' => [],
        ];

        $deliv_id = $this->objHelper->save($sqlval);

        $times = $this->objQuery->select('*', 'dtb_delivtime', 'deliv_id = ?', [$deliv_id]);
        $this->assertCount(2, $times);
        $this->assertEquals('午前中', $times[0]['deliv_time']);
        $this->assertEquals('12-14時', $times[1]['deliv_time']);
    }

    public function testSave新規登録で支払方法が登録される()
    {
        $sqlval = [
            'product_type_id' => 1,
            'service_name' => '新規配送',
            'status' => 1,
            'del_flg' => 0,
            'creator_id' => 1,
            'deliv_time' => [],
            'deliv_fee' => [],
            'payment_ids' => [1, 2, 3],
        ];

        $deliv_id = $this->objHelper->save($sqlval);

        $payments = $this->objQuery->select('*', 'dtb_payment_options', 'deliv_id = ?', [$deliv_id]);
        $this->assertCount(3, $payments);
        $this->assertEquals(1, $payments[0]['payment_id']);
        $this->assertEquals(2, $payments[1]['payment_id']);
        $this->assertEquals(3, $payments[2]['payment_id']);
    }

    public function testSave新規登録でランクが自動設定される()
    {
        // 既存の配送方法を作成
        $this->createDelivData(['deliv_id' => 1, 'rank' => 5]);

        $sqlval = [
            'product_type_id' => 1,
            'service_name' => '新規配送',
            'status' => 1,
            'del_flg' => 0,
            'creator_id' => 1,
            'deliv_time' => [],
            'deliv_fee' => [],
            'payment_ids' => [],
        ];

        $deliv_id = $this->objHelper->save($sqlval);

        $rank = $this->objQuery->get('rank', 'dtb_deliv', 'deliv_id = ?', [$deliv_id]);
        $this->assertEquals(6, $rank, '既存の最大ランク+1が設定される');
    }

    public function testSave更新()
    {
        // 既存の配送方法を作成
        $existing = $this->createDelivData(['deliv_id' => 1, 'service_name' => '既存配送']);

        $sqlval = [
            'deliv_id' => 1,
            'product_type_id' => 1,
            'service_name' => '更新後配送',
            'status' => 1,
            'del_flg' => 0,
            'deliv_time' => [],
            'deliv_fee' => [],
            'payment_ids' => [],
        ];

        $deliv_id = $this->objHelper->save($sqlval);

        $this->assertEquals(1, $deliv_id, '同じ配送IDが返される');

        $result = $this->objQuery->select('*', 'dtb_deliv', 'deliv_id = ?', [1]);
        $this->assertEquals('更新後配送', $result[0]['service_name'], 'サービス名が更新される');
    }

    public function testSave更新で配送時間を追加()
    {
        $this->createDelivData(['deliv_id' => 1, 'service_name' => '既存配送']);

        $sqlval = [
            'deliv_id' => 1,
            'product_type_id' => 1,
            'service_name' => '既存配送',
            'status' => 1,
            'del_flg' => 0,
            'deliv_time' => [1 => '午前中', 2 => '12-14時'],
            'deliv_fee' => [],
            'payment_ids' => [],
        ];

        $this->objHelper->save($sqlval);

        $times = $this->objQuery->select('*', 'dtb_delivtime', 'deliv_id = ?', [1]);
        $this->assertCount(2, $times);
    }

    public function testSave更新で配送時間を更新()
    {
        $this->createDelivData(['deliv_id' => 1, 'service_name' => '既存配送']);
        $this->createDelivTimeData(1, [1 => '午前中']);

        $sqlval = [
            'deliv_id' => 1,
            'product_type_id' => 1,
            'service_name' => '既存配送',
            'status' => 1,
            'del_flg' => 0,
            'deliv_time' => [1 => '午後'],
            'deliv_fee' => [],
            'payment_ids' => [],
        ];

        $this->objHelper->save($sqlval);

        $time = $this->objQuery->get('deliv_time', 'dtb_delivtime', 'deliv_id = ? AND time_id = ?', [1, 1]);
        $this->assertEquals('午後', $time);
    }

    public function testSave更新で配送時間を削除()
    {
        $this->createDelivData(['deliv_id' => 1, 'service_name' => '既存配送']);
        $this->createDelivTimeData(1, [1 => '午前中', 2 => '12-14時']);

        $sqlval = [
            'deliv_id' => 1,
            'product_type_id' => 1,
            'service_name' => '既存配送',
            'status' => 1,
            'del_flg' => 0,
            'deliv_time' => [1 => '午前中'],  // time_id=2を削除
            'deliv_fee' => [],
            'payment_ids' => [],
        ];

        $this->objHelper->save($sqlval);

        $times = $this->objQuery->select('*', 'dtb_delivtime', 'deliv_id = ?', [1]);
        $this->assertCount(1, $times, '配送時間が1件に減る');
        $this->assertEquals('午前中', $times[0]['deliv_time']);
    }

    public function testSave更新で支払方法を変更()
    {
        $this->createDelivData(['deliv_id' => 1, 'service_name' => '既存配送']);
        $this->createPaymentOptionsData(1, [1, 2]);

        $sqlval = [
            'deliv_id' => 1,
            'product_type_id' => 1,
            'service_name' => '既存配送',
            'status' => 1,
            'del_flg' => 0,
            'deliv_time' => [],
            'deliv_fee' => [],
            'payment_ids' => [3, 4, 5],
        ];

        $this->objHelper->save($sqlval);

        $payments = $this->objQuery->getCol('payment_id', 'dtb_payment_options', 'deliv_id = ? ORDER BY rank', [1]);
        $this->assertEquals([3, 4, 5], $payments, '支払方法が置き換わる');
    }
}
