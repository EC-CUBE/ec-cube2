<?php

require_once __DIR__.'/SC_Helper_Holiday_TestBase.php';

/**
 * SC_Helper_Holiday::getList()のテストクラス.
 */
class SC_Helper_Holiday_getListTest extends SC_Helper_Holiday_TestBase
{
    public function testGetList休日一覧を取得()
    {
        $this->createHolidayData(['holiday_id' => 1, 'title' => '元旦', 'month' => 1, 'day' => 1, 'rank' => 3]);
        $this->createHolidayData(['holiday_id' => 2, 'title' => '成人の日', 'month' => 1, 'day' => 8, 'rank' => 2]);
        $this->createHolidayData(['holiday_id' => 3, 'title' => '建国記念日', 'month' => 2, 'day' => 11, 'rank' => 1]);

        $result = $this->objHelper->getList();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function testGetListランク降順でソートされる()
    {
        $this->createHolidayData(['holiday_id' => 1, 'rank' => 1]);
        $this->createHolidayData(['holiday_id' => 2, 'rank' => 5]);
        $this->createHolidayData(['holiday_id' => 3, 'rank' => 3]);

        $result = $this->objHelper->getList();

        $this->assertEquals(2, $result[0]['holiday_id'], '最初はrank=5の休日');
        $this->assertEquals(3, $result[1]['holiday_id'], '次はrank=3の休日');
        $this->assertEquals(1, $result[2]['holiday_id'], '最後はrank=1の休日');
    }

    public function testGetList削除済み休日は含まれない()
    {
        $this->createHolidayData(['holiday_id' => 1, 'del_flg' => 0]);
        $this->createHolidayData(['holiday_id' => 2, 'del_flg' => 1]);
        $this->createHolidayData(['holiday_id' => 3, 'del_flg' => 0]);

        $result = $this->objHelper->getList();

        $this->assertCount(2, $result, '削除済み休日は除外される');
        $this->assertEquals(1, $result[0]['holiday_id']);
        $this->assertEquals(3, $result[1]['holiday_id']);
    }

    public function testGetList削除済み休日をHasDeletedで取得()
    {
        $this->createHolidayData(['holiday_id' => 1, 'del_flg' => 0]);
        $this->createHolidayData(['holiday_id' => 2, 'del_flg' => 1]);
        $this->createHolidayData(['holiday_id' => 3, 'del_flg' => 0]);

        $result = $this->objHelper->getList(true);

        $this->assertCount(3, $result, 'has_deleted=trueで削除済みも含む');
    }

    public function testGetList指定カラムのみ取得()
    {
        $this->createHolidayData(['holiday_id' => 1, 'title' => '元旦', 'month' => 1, 'day' => 1]);

        $result = $this->objHelper->getList();

        $this->assertArrayHasKey('holiday_id', $result[0]);
        $this->assertArrayHasKey('title', $result[0]);
        $this->assertArrayHasKey('month', $result[0]);
        $this->assertArrayHasKey('day', $result[0]);
        $this->assertArrayNotHasKey('creator_id', $result[0], 'creator_idは含まれない');
    }

    public function testGetList休日が存在しない場合()
    {
        $result = $this->objHelper->getList();

        $this->assertIsArray($result);
        $this->assertEmpty($result, '休日が存在しない場合は空配列');
    }
}
