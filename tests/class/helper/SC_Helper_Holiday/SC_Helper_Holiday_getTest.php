<?php

require_once __DIR__.'/SC_Helper_Holiday_TestBase.php';

/**
 * SC_Helper_Holiday::get()のテストクラス.
 */
class SC_Helper_Holiday_getTest extends SC_Helper_Holiday_TestBase
{
    public function testGet休日情報を取得()
    {
        $this->createHolidayData([
            'holiday_id' => 1,
            'title' => '元旦',
            'month' => 1,
            'day' => 1,
            'rank' => 10,
        ]);

        $result = $this->objHelper->get(1);

        $this->assertIsArray($result);
        $this->assertEquals('元旦', $result['title']);
        $this->assertEquals(1, $result['month']);
        $this->assertEquals(1, $result['day']);
        $this->assertEquals(10, $result['rank']);
    }

    public function testGet存在しない休日ID()
    {
        $result = $this->objHelper->get(9999);

        $this->assertNull($result, '存在しない休日IDの場合はnull');
    }

    public function testGet削除済み休日はデフォルトで取得されない()
    {
        $this->createHolidayData([
            'holiday_id' => 1,
            'title' => '削除済み',
            'del_flg' => 1,
        ]);

        $result = $this->objHelper->get(1);

        $this->assertNull($result, '削除済み休日は取得されない');
    }

    public function testGet削除済み休日をHasDeletedで取得()
    {
        $this->createHolidayData([
            'holiday_id' => 1,
            'title' => '削除済み',
            'del_flg' => 1,
        ]);

        $result = $this->objHelper->get(1, true);

        $this->assertIsArray($result);
        $this->assertEquals('削除済み', $result['title']);
        $this->assertEquals(1, $result['del_flg']);
    }
}
