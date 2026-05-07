<?php

require_once __DIR__.'/SC_Helper_Payment_TestBase.php';

/**
 * SC_Helper_Payment::delete(), rankUp(), rankDown()のテストクラス.
 */
class SC_Helper_Payment_deleteAndRankTest extends SC_Helper_Payment_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の支払方法を作成（rank順）
        $this->createPaymentData(['payment_id' => 1, 'payment_method' => '支払A', 'rank' => 1]);
        $this->createPaymentData(['payment_id' => 2, 'payment_method' => '支払B', 'rank' => 2]);
        $this->createPaymentData(['payment_id' => 3, 'payment_method' => '支払C', 'rank' => 3]);
    }

    public function testDelete支払方法が論理削除される()
    {
        $this->objHelper->delete(2);

        $result = $this->objQuery->get('del_flg', 'dtb_payment', 'payment_id = ?', [2]);

        $this->assertEquals(1, $result, '支払方法が論理削除される（del_flg=1）');
    }

    public function testDelete他の支払方法のランクが調整される()
    {
        $this->objHelper->delete(2);

        $rank1 = $this->objQuery->get('rank', 'dtb_payment', 'payment_id = ?', [1]);
        $rank3 = $this->objQuery->get('rank', 'dtb_payment', 'payment_id = ?', [3]);

        $this->assertEquals(1, $rank1, '下位ランクは変更されない');
        $this->assertEquals(2, $rank3, '上位ランクは詰められる');
    }

    public function testRankUpランクが上がる()
    {
        $this->objHelper->rankUp(1);

        $rank1 = $this->objQuery->get('rank', 'dtb_payment', 'payment_id = ?', [1]);
        $rank2 = $this->objQuery->get('rank', 'dtb_payment', 'payment_id = ?', [2]);

        $this->assertEquals(2, $rank1, 'ランクが1→2に上がる');
        $this->assertEquals(1, $rank2, 'ランクが2→1に下がる（入れ替わる）');
    }

    public function testRankUp最上位のランクアップは変更なし()
    {
        $this->objHelper->rankUp(3);

        $rank3 = $this->objQuery->get('rank', 'dtb_payment', 'payment_id = ?', [3]);

        $this->assertEquals(3, $rank3, '最上位のランクアップは変更なし');
    }

    public function testRankDownランクが下がる()
    {
        $this->objHelper->rankDown(2);

        $rank1 = $this->objQuery->get('rank', 'dtb_payment', 'payment_id = ?', [1]);
        $rank2 = $this->objQuery->get('rank', 'dtb_payment', 'payment_id = ?', [2]);

        $this->assertEquals(2, $rank1, 'ランクが1→2に上がる（入れ替わる）');
        $this->assertEquals(1, $rank2, 'ランクが2→1に下がる');
    }

    public function testRankDown最下位のランクダウンは変更なし()
    {
        $this->objHelper->rankDown(1);

        $rank1 = $this->objQuery->get('rank', 'dtb_payment', 'payment_id = ?', [1]);

        $this->assertEquals(1, $rank1, '最下位のランクダウンは変更なし');
    }
}
