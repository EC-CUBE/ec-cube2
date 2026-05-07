<?php

require_once __DIR__.'/SC_Helper_Payment_TestBase.php';

/**
 * SC_Helper_Payment::useModule(), getIDValueList()のテストクラス.
 */
class SC_Helper_Payment_staticMethodsTest extends SC_Helper_Payment_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の支払方法を作成
        $this->createPaymentData([
            'payment_id' => 1,
            'payment_method' => '通常支払',
            'memo03' => '',  // 決済モジュールなし
        ]);
        $this->createPaymentData([
            'payment_id' => 2,
            'payment_method' => 'モジュール支払',
            'memo03' => 'payment_module_name',  // 決済モジュールあり
        ]);
        $this->createPaymentData([
            'payment_id' => 3,
            'payment_method' => 'クレジットカード',
        ]);
    }

    public function testUseModule決済モジュールを使用しない()
    {
        $result = SC_Helper_Payment::useModule(1);

        $this->assertFalse($result, 'memo03が空の場合はfalse');
    }

    public function testUseModule決済モジュールを使用する()
    {
        $result = SC_Helper_Payment::useModule(2);

        $this->assertTrue($result, 'memo03に値がある場合はtrue');
    }

    public function testUseModule存在しない支払方法ID()
    {
        $result = SC_Helper_Payment::useModule(9999);

        $this->assertFalse($result, '存在しない支払方法IDの場合はfalse');
    }

    public function testGetIDValueListID値リストを取得()
    {
        $result = SC_Helper_Payment::getIDValueList();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals('通常支払', $result[1]);
        $this->assertEquals('モジュール支払', $result[2]);
        $this->assertEquals('クレジットカード', $result[3]);
    }

    public function testGetIDValueListカスタムタイプ()
    {
        $result = SC_Helper_Payment::getIDValueList('payment_id');

        $this->assertIsArray($result);
        $this->assertEquals(1, $result[1]);
        $this->assertEquals(2, $result[2]);
        $this->assertEquals(3, $result[3]);
    }
}
