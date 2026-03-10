<?php

require_once __DIR__.'/SC_Helper_Payment_TestBase.php';

/**
 * SC_Helper_Payment::save()のテストクラス.
 */
class SC_Helper_Payment_saveTest extends SC_Helper_Payment_TestBase
{
    public function testSave新規登録()
    {
        $sqlval = [
            'payment_id' => '',
            'payment_method' => '新規支払方法',
            'charge' => 300,
            'status' => 1,
            'del_flg' => 0,
            'creator_id' => 1,
        ];

        $this->objHelper->save($sqlval);

        $result = $this->objQuery->select('*', 'dtb_payment', 'payment_method = ?', ['新規支払方法']);
        $this->assertCount(1, $result);
        $this->assertEquals('新規支払方法', $result[0]['payment_method']);
        $this->assertEquals(300, $result[0]['charge']);
    }

    public function testSave新規登録でランクが自動設定される()
    {
        $this->createPaymentData(['payment_id' => 1, 'rank' => 5]);

        $sqlval = [
            'payment_id' => '',
            'payment_method' => '新規支払方法',
            'status' => 1,
            'del_flg' => 0,
            'creator_id' => 1,
        ];

        $this->objHelper->save($sqlval);

        $result = $this->objQuery->select('*', 'dtb_payment', 'payment_method = ?', ['新規支払方法']);
        $this->assertEquals(6, $result[0]['rank'], '既存の最大ランク+1が設定される');
    }

    public function testSave更新()
    {
        $this->createPaymentData(['payment_id' => 1, 'payment_method' => '既存支払方法', 'charge' => 100]);

        $sqlval = [
            'payment_id' => 1,
            'payment_method' => '更新後支払方法',
            'charge' => 200,
            'status' => 1,
            'del_flg' => 0,
        ];

        $this->objHelper->save($sqlval);

        $result = $this->objQuery->getRow('*', 'dtb_payment', 'payment_id = ?', [1]);
        $this->assertEquals('更新後支払方法', $result['payment_method']);
        $this->assertEquals(200, $result['charge']);
    }

    public function testSave更新時にcreatorIdとcreateDateは変更されない()
    {
        $this->createPaymentData([
            'payment_id' => 1,
            'creator_id' => 1,
            'create_date' => '2020-01-01 00:00:00',
        ]);

        $sqlval = [
            'payment_id' => 1,
            'payment_method' => '更新後',
            'creator_id' => 999,  // この値は無視される
            'create_date' => '2025-01-01 00:00:00',  // この値も無視される
            'status' => 1,
            'del_flg' => 0,
        ];

        $this->objHelper->save($sqlval);

        $result = $this->objQuery->getRow('*', 'dtb_payment', 'payment_id = ?', [1]);
        $this->assertEquals(1, $result['creator_id'], 'creator_idは変更されない');
        $this->assertEquals('2020-01-01 00:00:00', $result['create_date'], 'create_dateは変更されない');
    }
}
