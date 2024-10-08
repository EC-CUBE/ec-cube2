<?php

require __DIR__.'/SC_Helper_TaxRule_TestBase.php';

class SC_Helper_TaxRule_getDetailTest extends SC_Helper_TaxRule_TestBase
{
    private $taxs = [];

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testGetTaxPerTaxRateWithRound()
    {
        $this->setUpTaxRule([
            [
                'tax_rule_id' => 1004,
                'apply_date' => '2019-10-01 00:00:00',
                'tax_rate' => '10',
                'calc_rule' => '1',
                'product_id' => '0',
                'product_class_id' => '0',
                'del_flg' => '0',
                'member_id' => 1,
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
            ],
        ]);

        $arrTaxableTotal = [
            10 => 724431,
            8 => 65756,
        ];
        $discount_total = 7159;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxableTotal, $discount_total);
        self::assertSame(
            [
                8 => [
                    'discount' => 596,
                    'total' => 65160,
                    'tax' => 4827,
                ],
                10 => [
                    'discount' => 6563,
                    'total' => 717868,
                    'tax' => 65261,
                ],
            ],
            $actual
        );

        self::assertSame(
            '(8%対象: 65,160円 内消費税: 4,827円)'.PHP_EOL.
                '(10%対象: 717,868円 内消費税: 65,261円)'.PHP_EOL,
            SC_Helper_TaxRule_Ex::getTaxDetail($arrTaxableTotal, $discount_total)
        );
    }

    public function testGetTaxPerTaxRateWithZero()
    {
        $this->setUpTaxRule([
            [
                'tax_rule_id' => 1004,
                'apply_date' => '2019-10-01 00:00:00',
                'tax_rate' => '10',
                'calc_rule' => '1',
                'product_id' => '0',
                'product_class_id' => '0',
                'del_flg' => '0',
                'member_id' => 1,
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
            ],
        ]);

        $arrTaxableTotal = [
            10 => 0,
            8 => 0,
        ];
        $discount_total = 0;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxableTotal, $discount_total);
        self::assertSame(
            [
                8 => [
                    'discount' => 0,
                    'total' => 0,
                    'tax' => 0,
                ],
                10 => [
                    'discount' => 0,
                    'total' => 0,
                    'tax' => 0,
                ],
            ],
            $actual
        );

        self::assertSame(
            '(8%対象: 0円 内消費税: 0円)'.PHP_EOL.
                '(10%対象: 0円 内消費税: 0円)'.PHP_EOL,
            SC_Helper_TaxRule_Ex::getTaxDetail($arrTaxableTotal, $discount_total)
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetTaxPerTaxRateWithFloor()
    {
        self::markTestSkipped('Skip this test because @runInSeparateProcess does not work properly');

        $this->setUpTaxRule([
            [
                'tax_rule_id' => 1004,
                'apply_date' => '2019-10-01 00:00:00',
                'tax_rate' => '10',
                'calc_rule' => '2', // floor
                'product_id' => '0',
                'product_class_id' => '0',
                'del_flg' => '0',
                'member_id' => 1,
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
            ],
        ]);

        $arrTaxableTotal = [
            10 => 724431,
            8 => 65756,
        ];
        $discount_total = 7159;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxableTotal, $discount_total);
        self::assertSame(
            [
                8 => [
                    'discount' => 596,
                    'total' => 65160,
                    'tax' => 4826,
                ],
                10 => [
                    'discount' => 6563,
                    'total' => 717868,
                    'tax' => 65260,
                ],
            ],
            $actual
        );

        self::assertSame(array_sum($arrTaxableTotal) - $discount_total, $actual[8]['total'] + $actual[10]['total']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetTaxPerTaxRateWithCeil()
    {
        self::markTestSkipped('Skip this test because @runInSeparateProcess does not work properly');

        $this->setUpTaxRule([
            [
                'tax_rule_id' => 1004,
                'apply_date' => '2019-10-01 00:00:00',
                'tax_rate' => '10',
                'calc_rule' => '3', // ceil
                'product_id' => '0',
                'product_class_id' => '0',
                'del_flg' => '0',
                'member_id' => 1,
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
            ],
        ]);

        $arrTaxableTotal = [
            10 => 724431,
            8 => 65756,
        ];
        $discount_total = 7159;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxableTotal, $discount_total);
        self::assertSame(
            [
                8 => [
                    'discount' => 596,
                    'total' => 65160,
                    'tax' => 4827,
                ],
                10 => [
                    'discount' => 6563,
                    'total' => 717868,
                    'tax' => 65261,
                ],
            ],
            $actual
        );

        self::assertSame(array_sum($arrTaxableTotal) - $discount_total, $actual[8]['total'] + $actual[10]['total']);
    }

    /**
     * @see https://github.com/EC-CUBE/ec-cube2/pull/762#issuecomment-1897799676
     */
    public function testGetTaxPerTaxRateWithRound2()
    {
        $this->setUpTaxRule([
            [
                'tax_rule_id' => 1004,
                'apply_date' => '2019-10-01 00:00:00',
                'tax_rate' => '10',
                'calc_rule' => '1',
                'product_id' => '0',
                'product_class_id' => '0',
                'del_flg' => '0',
                'member_id' => 1,
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
            ],
        ]);

        $arrTaxableTotal = [
            10 => 1595,
            8 => 7398,
        ];
        $discount_total = 92;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxableTotal, $discount_total);
        self::assertSame(
            [
                8 => [
                    'discount' => 76,
                    'total' => 7322,
                    'tax' => 542,
                ],
                10 => [
                    'discount' => 16,
                    'total' => 1579,
                    'tax' => 144,
                ],
            ],
            $actual
        );

        self::assertSame(
            '(8%対象: 7,322円 内消費税: 542円)'.PHP_EOL.
                '(10%対象: 1,579円 内消費税: 144円)'.PHP_EOL,
            SC_Helper_TaxRule_Ex::getTaxDetail($arrTaxableTotal, $discount_total)
        );

        self::assertSame(array_sum($arrTaxableTotal) - $discount_total, $actual[8]['total'] + $actual[10]['total']);
    }

    /**
     * @see https://github.com/EC-CUBE/ec-cube2/pull/762#issuecomment-1897799676
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetTaxPerTaxRateWithFloor2()
    {
        self::markTestSkipped('Skip this test because @runInSeparateProcess does not work properly');

        $this->setUpTaxRule([
            [
                'tax_rule_id' => 1004,
                'apply_date' => '2019-10-01 00:00:00',
                'tax_rate' => '10',
                'calc_rule' => '2', // floor
                'product_id' => '0',
                'product_class_id' => '0',
                'del_flg' => '0',
                'member_id' => 1,
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
            ],
        ]);

        $arrTaxableTotal = [
            10 => 1595,
            8 => 7398,
        ];
        $discount_total = 92;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxableTotal, $discount_total);
        self::assertSame(
            [
                8 => [
                    'discount' => 76,
                    'total' => 7322,
                    'tax' => 542,
                ],
                10 => [
                    'discount' => 16,
                    'total' => 1579,
                    'tax' => 143,
                ],
            ],
            $actual
        );

        self::assertSame(array_sum($arrTaxableTotal) - $discount_total, $actual[8]['total'] + $actual[10]['total']);
    }

    /**
     * @see https://github.com/EC-CUBE/ec-cube2/pull/762#issuecomment-1897799676
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetTaxPerTaxRateWithCeil2()
    {
        self::markTestSkipped('Skip this test because @runInSeparateProcess does not work properly');

        $this->setUpTaxRule([
            [
                'tax_rule_id' => 1004,
                'apply_date' => '2019-10-01 00:00:00',
                'tax_rate' => '10',
                'calc_rule' => '3', // ceil
                'product_id' => '0',
                'product_class_id' => '0',
                'del_flg' => '0',
                'member_id' => 1,
                'create_date' => '2000-01-01 00:00:00',
                'update_date' => '2000-01-01 00:00:00',
            ],
        ]);

        $arrTaxableTotal = [
            10 => 1595,
            8 => 7398,
        ];
        $discount_total = 92;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxableTotal, $discount_total);
        self::assertSame(
            [
                8 => [
                    'discount' => 76,
                    'total' => 7322,
                    'tax' => 543,
                ],
                10 => [
                    'discount' => 16,
                    'total' => 1579,
                    'tax' => 144,
                ],
            ],
            $actual
        );

        self::assertSame(array_sum($arrTaxableTotal) - $discount_total, $actual[8]['total'] + $actual[10]['total']);
    }

    protected function setUpTaxRule(array $taxs = [])
    {
        $this->objQuery->delete('dtb_tax_rule');
        foreach ($taxs as $key => $item) {
            $this->objQuery->insert('dtb_tax_rule', $item);
        }
    }
}
