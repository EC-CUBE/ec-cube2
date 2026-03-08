<?php

require_once __DIR__.'/SC_Helper_Holiday_TestBase.php';

/**
 * SC_Helper_Holiday::isDateExist()のテストクラス.
 */
class SC_Helper_Holiday_isDateExistTest extends SC_Helper_Holiday_TestBase
{
    public function testIsDateExist同日付の休日が存在する()
    {
        $this->createHolidayData([
            'holiday_id' => 1,
            'title' => '元旦',
            'month' => 1,
            'day' => 1,
        ]);

        $result = $this->objHelper->isDateExist(1, 1);

        $this->assertTrue($result, '同日付の休日が存在する場合はtrue');
    }

    public function testIsDateExist同日付の休日が存在しない()
    {
        $this->createHolidayData([
            'holiday_id' => 1,
            'month' => 1,
            'day' => 1,
        ]);

        $result = $this->objHelper->isDateExist(12, 31);

        $this->assertFalse($result, '同日付の休日が存在しない場合はfalse');
    }

    public function testIsDateExist削除済み休日は除外される()
    {
        $this->createHolidayData([
            'holiday_id' => 1,
            'month' => 1,
            'day' => 1,
            'del_flg' => 1,
        ]);

        $result = $this->objHelper->isDateExist(1, 1);

        $this->assertFalse($result, '削除済み休日は存在しないと判定される');
    }

    public function testIsDateExist自身の休日IDを除外()
    {
        $this->createHolidayData([
            'holiday_id' => 1,
            'month' => 1,
            'day' => 1,
        ]);

        $result = $this->objHelper->isDateExist(1, 1, 1);

        $this->assertFalse($result, '自身の休日IDは除外される');
    }

    public function testIsDateExist自身以外の同日付休日が存在()
    {
        $this->createHolidayData(['holiday_id' => 1, 'month' => 1, 'day' => 1]);
        $this->createHolidayData(['holiday_id' => 2, 'month' => 1, 'day' => 1]);

        $result = $this->objHelper->isDateExist(1, 1, 1);

        $this->assertTrue($result, '自身以外の同日付休日が存在する場合はtrue');
    }

    public function testIsDateExist月のみ一致()
    {
        $this->createHolidayData(['month' => 1, 'day' => 1]);

        $result = $this->objHelper->isDateExist(1, 15);

        $this->assertFalse($result, '月のみ一致、日が異なる場合はfalse');
    }

    public function testIsDateExist日のみ一致()
    {
        $this->createHolidayData(['month' => 1, 'day' => 1]);

        $result = $this->objHelper->isDateExist(12, 1);

        $this->assertFalse($result, '日のみ一致、月が異なる場合はfalse');
    }
}
