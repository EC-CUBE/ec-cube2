<?php

require_once __DIR__.'/SC_Helper_Delivery_TestBase.php';

/**
 * SC_Helper_Delivery::rankUp()とrankDown()のテストクラス.
 */
class SC_Helper_Delivery_rankTest extends SC_Helper_Delivery_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の配送方法を作成（rank順）
        $this->createDelivData(['deliv_id' => 1, 'service_name' => '配送A', 'rank' => 1]);
        $this->createDelivData(['deliv_id' => 2, 'service_name' => '配送B', 'rank' => 2]);
        $this->createDelivData(['deliv_id' => 3, 'service_name' => '配送C', 'rank' => 3]);
    }

    public function testRankUpランクが上がる()
    {
        $this->objHelper->rankUp(1);

        $rank1 = $this->objQuery->get('rank', 'dtb_deliv', 'deliv_id = ?', [1]);
        $rank2 = $this->objQuery->get('rank', 'dtb_deliv', 'deliv_id = ?', [2]);

        $this->assertEquals(2, $rank1, 'ランクが1→2に上がる');
        $this->assertEquals(1, $rank2, 'ランクが2→1に下がる（入れ替わる）');
    }

    public function testRankUp最上位のランクアップは変更なし()
    {
        $this->objHelper->rankUp(3);

        $rank3 = $this->objQuery->get('rank', 'dtb_deliv', 'deliv_id = ?', [3]);

        $this->assertEquals(3, $rank3, '最上位のランクアップは変更なし');
    }

    public function testRankDownランクが下がる()
    {
        $this->objHelper->rankDown(2);

        $rank1 = $this->objQuery->get('rank', 'dtb_deliv', 'deliv_id = ?', [1]);
        $rank2 = $this->objQuery->get('rank', 'dtb_deliv', 'deliv_id = ?', [2]);

        $this->assertEquals(2, $rank1, 'ランクが1→2に上がる（入れ替わる）');
        $this->assertEquals(1, $rank2, 'ランクが2→1に下がる');
    }

    public function testRankDown最下位のランクダウンは変更なし()
    {
        $this->objHelper->rankDown(1);

        $rank1 = $this->objQuery->get('rank', 'dtb_deliv', 'deliv_id = ?', [1]);

        $this->assertEquals(1, $rank1, '最下位のランクダウンは変更なし');
    }

    public function testRankUpAndRankDown連続操作()
    {
        $this->objHelper->rankUp(1);
        $this->objHelper->rankUp(1);

        $rank1 = $this->objQuery->get('rank', 'dtb_deliv', 'deliv_id = ?', [1]);

        $this->assertEquals(3, $rank1, '2回ランクアップして1→3になる');
    }
}
