<?php

require_once __DIR__.'/SC_Helper_Delivery_TestBase.php';

/**
 * SC_Helper_Delivery::getDelivFee()のテストクラス.
 */
class SC_Helper_Delivery_getDelivFeeTest extends SC_Helper_Delivery_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の配送方法を作成
        $this->createDelivData(['deliv_id' => 1, 'del_flg' => 0]);
        $this->createDelivData(['deliv_id' => 2, 'del_flg' => 1]);  // 削除済み

        // テスト用の配送料金を作成
        $fee_id = 1;
        foreach ([13 => 500, 14 => 600, 27 => 800] as $pref => $fee) {
            $this->objQuery->insert('dtb_delivfee', [
                'fee_id' => $fee_id++,
                'deliv_id' => 1,
                'pref' => $pref,
                'fee' => $fee,
            ]);
        }

        // 削除済み配送方法の料金
        $this->objQuery->insert('dtb_delivfee', [
            'fee_id' => $fee_id++,
            'deliv_id' => 2,
            'pref' => 13,
            'fee' => 1000,
        ]);
    }

    public function testGetDelivFee単一都道府県の配送料を取得()
    {
        $result = SC_Helper_Delivery::getDelivFee(13, 1);

        $this->assertEquals(500, $result);
    }

    public function testGetDelivFee配列で複数都道府県の配送料を取得()
    {
        $result = SC_Helper_Delivery::getDelivFee([13, 14], 1);

        $this->assertEquals(1100, $result, '東京(500) + 神奈川(600) = 1100');
    }

    public function testGetDelivFee配列で3都道府県の配送料を取得()
    {
        $result = SC_Helper_Delivery::getDelivFee([13, 14, 27], 1);

        $this->assertEquals(1900, $result, '東京(500) + 神奈川(600) + 大阪(800) = 1900');
    }

    public function testGetDelivFee削除済み配送方法の料金は取得されない()
    {
        $result = SC_Helper_Delivery::getDelivFee(13, 2);

        $this->assertEquals(0, $result, '削除済み配送方法の料金は0');
    }

    public function testGetDelivFee存在しない都道府県の場合()
    {
        $result = SC_Helper_Delivery::getDelivFee(99, 1);

        $this->assertEquals(0, $result, '存在しない都道府県の場合は0');
    }

    public function testGetDelivFee存在しない配送方法IDの場合()
    {
        $result = SC_Helper_Delivery::getDelivFee(13, 9999);

        $this->assertEquals(0, $result, '存在しない配送方法IDの場合は0');
    }

    public function testGetDelivFee空配列を渡した場合()
    {
        $result = SC_Helper_Delivery::getDelivFee([], 1);

        $this->assertEquals(0, $result, '空配列の場合は0');
    }
}
