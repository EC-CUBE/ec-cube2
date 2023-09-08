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
                    'total' => 65160,
                    'tax' => 4827
                ],
                10 => [
                    'total' => 717868,
                    'tax' => 65261
                ]
            ],
            $actual
        );

        self::assertSame(
            '(8%対象: 65,160円 内消費税: 4,827円)'.PHP_EOL.
                '(10%対象: 717,868円 内消費税: 65,261円)'.PHP_EOL,
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
                    'total' => 65160,
                    'tax' => 4826
                ],
                10 => [
                    'total' => 717867,
                    'tax' => 65260
                ]
            ],
            $actual
        );
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
                    'total' => 65161,
                    'tax' => 4827
                ],
                10 => [
                    'total' => 717868,
                    'tax' => 65261
                ]
            ],
            $actual
        );
    }

    protected function setUpTaxRule(array $taxs = [])
    {
        $this->objQuery->delete('dtb_tax_rule');
        foreach ($taxs as $key => $item) {
            $this->objQuery->insert('dtb_tax_rule', $item);
        }
    }
}
