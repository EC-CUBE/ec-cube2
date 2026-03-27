<?php

require_once __DIR__.'/SC_Helper_Holiday_TestBase.php';

/**
 * SC_Helper_Holiday::save()のテストクラス.
 */
class SC_Helper_Holiday_saveTest extends SC_Helper_Holiday_TestBase
{
    public function testSave新規登録()
    {
        $sqlval = [
            'holiday_id' => '',
            'title' => '新規休日',
            'month' => 3,
            'day' => 15,
            'creator_id' => 1,
        ];

        $holiday_id = $this->objHelper->save($sqlval);

        $this->assertNotFalse($holiday_id, '登録成功時は休日IDが返る');
        $this->assertGreaterThan(0, $holiday_id);

        $result = $this->objQuery->getRow('*', 'dtb_holiday', 'holiday_id = ?', [$holiday_id]);
        $this->assertEquals('新規休日', $result['title']);
        $this->assertEquals(3, $result['month']);
        $this->assertEquals(15, $result['day']);
    }

    public function testSave新規登録でランクが自動設定される()
    {
        $this->createHolidayData(['holiday_id' => 1, 'rank' => 5]);

        $sqlval = [
            'holiday_id' => '',
            'title' => '新規休日',
            'month' => 3,
            'day' => 15,
            'creator_id' => 1,
        ];

        $holiday_id = $this->objHelper->save($sqlval);

        $result = $this->objQuery->getRow('*', 'dtb_holiday', 'holiday_id = ?', [$holiday_id]);
        $this->assertEquals(6, $result['rank'], '既存の最大ランク+1が設定される');
    }

    public function testSave更新()
    {
        $this->createHolidayData([
            'holiday_id' => 1,
            'title' => '既存休日',
            'month' => 1,
            'day' => 1,
        ]);

        $sqlval = [
            'holiday_id' => 1,
            'title' => '更新後休日',
            'month' => 12,
            'day' => 31,
        ];

        $result = $this->objHelper->save($sqlval);

        $this->assertEquals(1, $result, '更新成功時は休日IDが返る');

        $holiday = $this->objQuery->getRow('*', 'dtb_holiday', 'holiday_id = ?', [1]);
        $this->assertEquals('更新後休日', $holiday['title']);
        $this->assertEquals(12, $holiday['month']);
        $this->assertEquals(31, $holiday['day']);
    }

    public function testSave更新時にcreatorIdとcreateDateは変更されない()
    {
        $this->createHolidayData([
            'holiday_id' => 1,
            'creator_id' => 1,
            'create_date' => '2020-01-01 00:00:00',
        ]);

        $sqlval = [
            'holiday_id' => 1,
            'title' => '更新後',
            'month' => 5,
            'day' => 5,
            'creator_id' => 999,  // この値は無視される
            'create_date' => '2025-01-01 00:00:00',  // この値も無視される
        ];

        $this->objHelper->save($sqlval);

        $result = $this->objQuery->getRow('*', 'dtb_holiday', 'holiday_id = ?', [1]);
        $this->assertEquals(1, $result['creator_id'], 'creator_idは変更されない');
        $this->assertEquals('2020-01-01 00:00:00', $result['create_date'], 'create_dateは変更されない');
    }
}
