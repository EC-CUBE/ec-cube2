<?php

require_once __DIR__.'/SC_Helper_Holiday_TestBase.php';

/**
 * SC_Helper_Holiday::delete(), rankUp(), rankDown()のテストクラス.
 */
class SC_Helper_Holiday_deleteAndRankTest extends SC_Helper_Holiday_TestBase
{
    public function testDelete休日が物理削除される()
    {
        $this->createHolidayData(['holiday_id' => 1, 'title' => '削除対象']);

        $this->objHelper->delete(1);

        $count = $this->objQuery->count('dtb_holiday', 'holiday_id = ?', [1]);
        $this->assertEquals(0, $count, '休日が物理削除される');
    }

    public function testDelete複数の休日がある場合()
    {
        $this->createHolidayData(['holiday_id' => 1, 'rank' => 3]);
        $this->createHolidayData(['holiday_id' => 2, 'rank' => 2]);
        $this->createHolidayData(['holiday_id' => 3, 'rank' => 1]);

        $this->objHelper->delete(2);

        $count = $this->objQuery->count('dtb_holiday');
        $this->assertEquals(2, $count, '1件削除されて2件残る');

        $exists1 = $this->objQuery->count('dtb_holiday', 'holiday_id = ?', [1]);
        $exists2 = $this->objQuery->count('dtb_holiday', 'holiday_id = ?', [2]);
        $exists3 = $this->objQuery->count('dtb_holiday', 'holiday_id = ?', [3]);

        $this->assertEquals(1, $exists1, '休日1は残る');
        $this->assertEquals(0, $exists2, '休日2は削除される');
        $this->assertEquals(1, $exists3, '休日3は残る');
    }

    public function testRankUp()
    {
        $this->createHolidayData(['holiday_id' => 1, 'rank' => 1]);
        $this->createHolidayData(['holiday_id' => 2, 'rank' => 2]);
        $this->createHolidayData(['holiday_id' => 3, 'rank' => 3]);

        $this->objHelper->rankUp(1);

        $rank1 = $this->objQuery->get('rank', 'dtb_holiday', 'holiday_id = ?', [1]);
        $rank2 = $this->objQuery->get('rank', 'dtb_holiday', 'holiday_id = ?', [2]);

        $this->assertEquals(2, $rank1, 'holiday_id=1のrankが2に上がる');
        $this->assertEquals(1, $rank2, 'holiday_id=2のrankが1に下がる');
    }

    public function testRankDown()
    {
        $this->createHolidayData(['holiday_id' => 1, 'rank' => 1]);
        $this->createHolidayData(['holiday_id' => 2, 'rank' => 2]);
        $this->createHolidayData(['holiday_id' => 3, 'rank' => 3]);

        $this->objHelper->rankDown(3);

        $rank2 = $this->objQuery->get('rank', 'dtb_holiday', 'holiday_id = ?', [2]);
        $rank3 = $this->objQuery->get('rank', 'dtb_holiday', 'holiday_id = ?', [3]);

        $this->assertEquals(3, $rank2, 'holiday_id=2のrankが3に上がる');
        $this->assertEquals(2, $rank3, 'holiday_id=3のrankが2に下がる');
    }

    public function testRankUp最上位の場合()
    {
        $this->createHolidayData(['holiday_id' => 1, 'rank' => 1]);
        $this->createHolidayData(['holiday_id' => 2, 'rank' => 2]);

        $this->objHelper->rankUp(2);

        $rank2 = $this->objQuery->get('rank', 'dtb_holiday', 'holiday_id = ?', [2]);
        $this->assertEquals(2, $rank2, '最上位の場合はrankが変わらない');
    }

    public function testRankDown最下位の場合()
    {
        $this->createHolidayData(['holiday_id' => 1, 'rank' => 1]);
        $this->createHolidayData(['holiday_id' => 2, 'rank' => 2]);

        $this->objHelper->rankDown(1);

        $rank1 = $this->objQuery->get('rank', 'dtb_holiday', 'holiday_id = ?', [1]);
        $this->assertEquals(1, $rank1, '最下位の場合はrankが変わらない');
    }
}
