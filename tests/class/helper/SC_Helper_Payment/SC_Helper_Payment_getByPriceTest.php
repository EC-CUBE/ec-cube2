<?php

require_once __DIR__.'/SC_Helper_Payment_TestBase.php';

/**
 * SC_Helper_Payment::getByPrice()のテストクラス.
 */
class SC_Helper_Payment_getByPriceTest extends SC_Helper_Payment_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の支払方法を作成（金額制限あり・なし）
        $this->createPaymentData([
            'payment_id' => 1,
            'payment_method' => '制限なし',
            'rule_max' => null,
            'upper_rule' => null,
        ]);
        $this->createPaymentData([
            'payment_id' => 2,
            'payment_method' => '下限のみ（10000円以上）',
            'rule_max' => 10000,
            'upper_rule' => null,
        ]);
        $this->createPaymentData([
            'payment_id' => 3,
            'payment_method' => '上限のみ（50000円以下）',
            'rule_max' => null,
            'upper_rule' => 50000,
        ]);
        $this->createPaymentData([
            'payment_id' => 4,
            'payment_method' => '範囲指定（10000-50000円）',
            'rule_max' => 10000,
            'upper_rule' => 50000,
        ]);
    }

    public function testGetByPrice制限なしの支払方法は常に取得される()
    {
        $result = $this->objHelper->getByPrice(5000);

        $methods = array_column($result, 'payment_method');
        $this->assertContains('制限なし', $methods);
    }

    public function testGetByPrice下限未満の場合は取得されない()
    {
        $result = $this->objHelper->getByPrice(5000);

        $methods = array_column($result, 'payment_method');
        $this->assertNotContains('下限のみ（10000円以上）', $methods);
        $this->assertNotContains('範囲指定（10000-50000円）', $methods);
    }

    public function testGetByPrice下限以上の場合は取得される()
    {
        $result = $this->objHelper->getByPrice(10000);

        $methods = array_column($result, 'payment_method');
        $this->assertContains('下限のみ（10000円以上）', $methods);
        $this->assertContains('範囲指定（10000-50000円）', $methods);
    }

    public function testGetByPrice上限を超えた場合は取得されない()
    {
        $result = $this->objHelper->getByPrice(60000);

        $methods = array_column($result, 'payment_method');
        $this->assertNotContains('上限のみ（50000円以下）', $methods);
        $this->assertNotContains('範囲指定（10000-50000円）', $methods);
    }

    public function testGetByPrice上限以下の場合は取得される()
    {
        $result = $this->objHelper->getByPrice(50000);

        $methods = array_column($result, 'payment_method');
        $this->assertContains('上限のみ（50000円以下）', $methods);
        $this->assertContains('範囲指定（10000-50000円）', $methods);
    }

    public function testGetByPrice範囲内の場合は取得される()
    {
        $result = $this->objHelper->getByPrice(30000);

        $this->assertCount(4, $result, '全ての支払方法が取得される');
    }

    public function testGetByPrice0円の場合()
    {
        $result = $this->objHelper->getByPrice(0);

        $methods = array_column($result, 'payment_method');
        $this->assertContains('制限なし', $methods);
        $this->assertContains('上限のみ（50000円以下）', $methods);
        $this->assertCount(2, $result);
    }
}
