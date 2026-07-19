<?php

require __DIR__.'/SC_Helper_TaxRule_TestBase.php';

class SC_Helper_TaxRule_getTaxDetailTest extends SC_Helper_TaxRule_TestBase
{
    private $taxs = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
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
     *
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
     *
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
     * 値引きを税率で按分し丸めた合計が、実際の値引額と ±1円 ずれるケース.
     *
     * 8%対象 755円 / 10%対象 165円 / 値引 92円 のとき、按分前の値引額は
     * 8%: 75.5円, 10%: 16.5円 となり、四捨五入で両方繰り上がって 76 + 17 = 93円。
     * このままでは支払額とインボイス合計に誤差が出るため、誤差(-1円)を既定税率
     * (10%)側へ寄せて合計を一致させる必要がある。
     *
     * @see https://github.com/EC-CUBE/ec-cube2/issues/6335 (ec-cube 本体)
     * @see https://github.com/EC-CUBE/ec-cube2/pull/762#issuecomment-1897935881
     */
    public function testGetTaxPerTaxRateWithRoundingDiff()
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
            10 => 165,
            8 => 755,
        ];
        $discount_total = 92;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxableTotal, $discount_total);

        // 按分後の値引額の合計は、必ず実際の値引額と一致しなければならない
        self::assertSame(
            $discount_total,
            $actual[8]['discount'] + $actual[10]['discount'],
            '按分後の値引額の合計が実際の値引額と一致していません'
        );

        // 値引後合計の総和は、(税込合計 - 値引額) と一致しなければならない
        self::assertSame(
            array_sum($arrTaxableTotal) - $discount_total,
            $actual[8]['total'] + $actual[10]['total'],
            '値引後合計の総和が支払額と一致していません'
        );

        self::assertSame(
            [
                8 => [
                    'discount' => 76,
                    'total' => 679,
                    'tax' => 50,
                ],
                10 => [
                    'discount' => 16,
                    'total' => 149,
                    'tax' => 14,
                ],
            ],
            $actual
        );
    }

    /**
     * 少額ポイント(1ポイント=1円)利用時の按分.
     *
     * 8%対象 5,000円 / 10%対象 5,000円 に 1ポイント利用すると、按分前の値引額は
     * 両税率とも 0.5円 で、四捨五入すると 1 + 1 = 2円 となり実際の値引額(1円)とずれる。
     * 誤差(-1円)を既定税率(10%)側へ寄せ、合計を一致させる。
     *
     * EC-CUBE2 の値引按分は calc_rule に依存せず常に四捨五入のため、基本税率が
     * 切り捨て設定でも合計はずれない(ec-cube 本体 4.2 系の事象との差異)。
     *
     * @see https://github.com/EC-CUBE/ec-cube/issues/6335#issuecomment-2626666163
     */
    public function testGetTaxPerTaxRateWithSmallPoint1()
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
            10 => 5000,
            8 => 5000,
        ];
        $discount_total = 1;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxableTotal, $discount_total);

        self::assertSame(
            $discount_total,
            $actual[8]['discount'] + $actual[10]['discount'],
            '按分後の値引額の合計が実際の値引額と一致していません'
        );
        self::assertSame(
            array_sum($arrTaxableTotal) - $discount_total,
            $actual[8]['total'] + $actual[10]['total'],
            '値引後合計の総和が支払額と一致していません'
        );
        self::assertSame(
            [
                8 => [
                    'discount' => 1,
                    'total' => 4999,
                    'tax' => 370,
                ],
                10 => [
                    'discount' => 0,
                    'total' => 5000,
                    'tax' => 455,
                ],
            ],
            $actual
        );
    }

    /**
     * 少額ポイント(2ポイント=2円)利用時の按分.
     *
     * 8%対象 7,500円 / 10%対象 2,500円 に 2ポイント利用すると、按分前の値引額は
     * 8%: 1.5円, 10%: 0.5円 で、四捨五入すると 2 + 1 = 3円 となり実際の値引額(2円)とずれる。
     * 誤差(-1円)を既定税率(10%)側へ寄せ、合計を一致させる。
     *
     * @see https://github.com/EC-CUBE/ec-cube/issues/6335#issuecomment-2626666163
     */
    public function testGetTaxPerTaxRateWithSmallPoint2()
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
            10 => 2500,
            8 => 7500,
        ];
        $discount_total = 2;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxableTotal, $discount_total);

        self::assertSame(
            $discount_total,
            $actual[8]['discount'] + $actual[10]['discount'],
            '按分後の値引額の合計が実際の値引額と一致していません'
        );
        self::assertSame(
            array_sum($arrTaxableTotal) - $discount_total,
            $actual[8]['total'] + $actual[10]['total'],
            '値引後合計の総和が支払額と一致していません'
        );
        self::assertSame(
            [
                8 => [
                    'discount' => 2,
                    'total' => 7498,
                    'tax' => 555,
                ],
                10 => [
                    'discount' => 0,
                    'total' => 2500,
                    'tax' => 227,
                ],
            ],
            $actual
        );
    }

    /**
     * @see https://github.com/EC-CUBE/ec-cube2/pull/762#issuecomment-1897799676
     *
     * @runInSeparateProcess
     *
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
     *
     * @runInSeparateProcess
     *
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

    /**
     * 空の税込み合計金額を渡した場合は空配列を返す.
     */
    public function testGetTaxPerTaxRateWithEmptyArray()
    {
        $this->setUpDefaultTaxRule();

        self::assertSame([], SC_Helper_TaxRule_Ex::getTaxPerTaxRate([], 100));
        self::assertSame('', SC_Helper_TaxRule_Ex::getTaxDetail([], 100));
    }

    /**
     * 既定税率(10%)の商品が無い受注では、誤差を金額が最大のバケットへ寄せる.
     *
     * 8%対象 1,000円 / 5%対象 3,000円 / 値引 2円 のとき、按分前の値引額は
     * 5%: 1.5円, 8%: 0.5円 となり、四捨五入で両方繰り上がって 2 + 1 = 3円。
     * 既定税率(10%)のバケットが存在しないため、誤差(-1円)は金額が最大の
     * 5%(3,000円)側へ寄せられ、5%の値引額は 2 - 1 = 1円 となる。
     */
    public function testGetTaxPerTaxRateWithoutDefaultTaxRate()
    {
        $this->setUpDefaultTaxRule();

        $arrTaxInclusiveTotalByRate = [
            8 => 1000,
            5 => 3000,
        ];
        $discount_total = 2;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxInclusiveTotalByRate, $discount_total);

        self::assertSame(
            [
                5 => [
                    'discount' => 1,
                    'total' => 2999,
                    'tax' => 143,
                ],
                8 => [
                    'discount' => 1,
                    'total' => 999,
                    'tax' => 74,
                ],
            ],
            $actual
        );

        self::assertSame(
            $discount_total,
            array_sum(array_column($actual, 'discount')),
            '按分後の値引額の合計が実際の値引額と一致していません'
        );
        self::assertSame(
            array_sum($arrTaxInclusiveTotalByRate) - $discount_total,
            array_sum(array_column($actual, 'total')),
            '値引後合計の総和が支払額と一致していません'
        );
    }

    /**
     * 丸め誤差が正方向(+1円)に出る場合の補正.
     *
     * 8%対象 3,000円 / 10%対象 3,000円 / 5%対象 3,000円 に 1円値引すると、
     * 按分前の値引額は各 0.333円 で、四捨五入すると全て 0円 に落ち、
     * 合計 0円 が実際の値引額(1円)より 1円 少なくなる。
     * 誤差(+1円)を既定税率(10%)側へ寄せて合計を一致させる。
     *
     * 3 税率が同一受注に現れることは実運用では無い。2 税率では按分前の端数の和が
     * 必ず整数になるため丸め誤差は 0 か -1円 にしかならず、正方向の補正を通せない。
     * ここでは getTaxPerTaxRate() が税率数に依存しない実装であることを利用して、
     * 3 税率を入力することで正方向の補正のみを検証する。
     */
    public function testGetTaxPerTaxRateWithPositiveDiff()
    {
        $this->setUpDefaultTaxRule();

        $arrTaxInclusiveTotalByRate = [
            8 => 3000,
            10 => 3000,
            5 => 3000,
        ];
        $discount_total = 1;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxInclusiveTotalByRate, $discount_total);

        self::assertSame(
            [
                5 => [
                    'discount' => 0,
                    'total' => 3000,
                    'tax' => 143,
                ],
                8 => [
                    'discount' => 0,
                    'total' => 3000,
                    'tax' => 222,
                ],
                10 => [
                    'discount' => 1,
                    'total' => 2999,
                    'tax' => 273,
                ],
            ],
            $actual
        );

        self::assertSame(
            $discount_total,
            array_sum(array_column($actual, 'discount')),
            '按分後の値引額の合計が実際の値引額と一致していません'
        );
        self::assertSame(
            array_sum($arrTaxInclusiveTotalByRate) - $discount_total,
            array_sum(array_column($actual, 'total')),
            '値引後合計の総和が支払額と一致していません'
        );
    }

    /**
     * 単一税率の受注では、値引額がそのまま単一のバケットに割り当てられる.
     */
    public function testGetTaxPerTaxRateWithSingleTaxRate()
    {
        $this->setUpDefaultTaxRule();

        $arrTaxInclusiveTotalByRate = [
            8 => 1000,
        ];
        $discount_total = 3;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxInclusiveTotalByRate, $discount_total);

        self::assertSame(
            [
                8 => [
                    'discount' => 3,
                    'total' => 997,
                    'tax' => 74,
                ],
            ],
            $actual
        );

        self::assertSame(
            '(8%対象: 997円 内消費税: 74円)'.PHP_EOL,
            SC_Helper_TaxRule_Ex::getTaxDetail($arrTaxInclusiveTotalByRate, $discount_total)
        );
    }

    /**
     * 値引が無い受注では、値引額は 0円 で税込み合計金額がそのまま残る.
     */
    public function testGetTaxPerTaxRateWithoutDiscount()
    {
        $this->setUpDefaultTaxRule();

        $arrTaxInclusiveTotalByRate = [
            10 => 5000,
            8 => 5000,
        ];

        // $discount_total は既定値(0)を使用する
        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxInclusiveTotalByRate);

        self::assertSame(
            [
                8 => [
                    'discount' => 0,
                    'total' => 5000,
                    'tax' => 370,
                ],
                10 => [
                    'discount' => 0,
                    'total' => 5000,
                    'tax' => 455,
                ],
            ],
            $actual
        );
    }

    /**
     * 誤差の寄せ先が複数該当する場合は、税率が最も小さいバケットへ寄せる.
     *
     * 5%対象 5,000円 / 10%対象以外の 8%対象 5,000円 に 1円値引すると、
     * 按分前の値引額は両税率とも 0.5円 で、四捨五入すると 1 + 1 = 2円 となり
     * 誤差(-1円)が生じる。既定税率(10%)のバケットが無く、かつ金額が同額のため
     * 寄せ先の候補が 2 つになるが、ksort 後の先頭(=最も小さい税率)に寄せる.
     */
    public function testGetTaxPerTaxRateWithSameTotals()
    {
        $this->setUpDefaultTaxRule();

        $arrTaxInclusiveTotalByRate = [
            8 => 5000,
            5 => 5000,
        ];
        $discount_total = 1;

        $actual = SC_Helper_TaxRule_Ex::getTaxPerTaxRate($arrTaxInclusiveTotalByRate, $discount_total);

        self::assertSame(
            [
                5 => [
                    'discount' => 0,
                    'total' => 5000,
                    'tax' => 238,
                ],
                8 => [
                    'discount' => 1,
                    'total' => 4999,
                    'tax' => 370,
                ],
            ],
            $actual
        );

        self::assertSame(
            $discount_total,
            array_sum(array_column($actual, 'discount')),
            '按分後の値引額の合計が実際の値引額と一致していません'
        );
        self::assertSame(
            array_sum($arrTaxInclusiveTotalByRate) - $discount_total,
            array_sum(array_column($actual, 'total')),
            '値引後合計の総和が支払額と一致していません'
        );
    }

    protected function setUpTaxRule(array $taxs = [])
    {
        $this->objQuery->delete('dtb_tax_rule');
        foreach ($taxs as $key => $item) {
            $this->objQuery->insert('dtb_tax_rule', $item);
        }
    }

    /**
     * 既定の税率設定(10% / 四捨五入)を登録する.
     */
    protected function setUpDefaultTaxRule()
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
    }
}
