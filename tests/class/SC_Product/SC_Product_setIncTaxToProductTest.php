<?php

class SC_Product_setIncTaxToProductTest extends SC_Product_TestBase
{
    protected function setUp()
    {
        parent::setUp();
        // $this->setUpProductClass();
        // $this->objProducts = new SC_Product_Ex();
    }

    public function checkPriceAndTaxRateProvider()
    {
        return [
            // operator to max
            [1, 2, 8, 10, 'max', true],
            [1, 1, 8, 8, 'max', true],
            [1, 0, 8, 8, 'max', false],
            [1, 1, 8, 5, 'max', false],
            // operator to min
            [1, 2, 8, 10, 'min', false],
            [1, 1, 8, 8, 'min', true],
            [1, 0, 8, 8, 'min', true],
            [1, 1, 8, 5, 'min', true],
            // operator to unknown
            [1, 2, 8, 10, 'n', true],
            [1, 1, 8, 8, 'n', true],
            [1, 0, 8, 8, 'n', false],
            [1, 1, 8, 5, 'n', false],
            // operator to empty
            [1, 2, 8, 10, '', true],
            [1, 1, 8, 8, '', true],
            [1, 0, 8, 8, '', false],
            [1, 1, 8, 5, '', false],
        ];
    }

    /**
     * @dataProvider checkPriceAndTaxRateProvider
     */
    public function testCheckPriceAndTaxRate($carry_price, $price, $carry_rate, $rate, $operator = 'max', $expected)
    {
        $this->expected = $expected;

        $this->actual = $this->wrapperToCheckPriceAndTaxRate($carry_price, $price, $carry_rate, $rate, $operator);
        $this->verify();
    }

    public function findProductClassIdByRuleProvider()
    {
        return [
            ['price01',
             [
                 1 => ['price01' => 100, 'tax_rate' => 10, 'product_class_id' => 1],
                 2 => ['price01' => 103, 'tax_rate' => 8, 'product_class_id' => 2],
                 3 => ['price01' => 102, 'tax_rate' => 8, 'product_class_id' => 3],
             ],
             'max', 2],
            ['price02',
             [
                 1 => ['price02' => 100, 'tax_rate' => 10, 'product_class_id' => 1],
                 2 => ['price02' => 103, 'tax_rate' => 8, 'product_class_id' => 2],
                 3 => ['price02' => 102, 'tax_rate' => 8, 'product_class_id' => 3],
             ],
             'min', 1],
            ['price01',
             [
                 1 => ['price01' => null, 'tax_rate' => 10, 'product_class_id' => 1],
                 2 => ['price01' => 103, 'tax_rate' => 8, 'product_class_id' => 2],
                 3 => ['price01' => 102, 'tax_rate' => 8, 'product_class_id' => 3],
             ],
             'min', 3],
            ['price01',
             [
                 1 => ['price01' => null, 'tax_rate' => 10, 'product_class_id' => 1],
                 2 => ['price01' => 103, 'tax_rate' => 8, 'product_class_id' => 2],
                 3 => ['price01' => 102, 'tax_rate' => 8, 'product_class_id' => 3],
             ],
             'max', 2],
            ['price01',
             [
                 1 => ['price01' => null, 'tax_rate' => 10, 'product_class_id' => 1],
                 2 => ['price01' => null, 'tax_rate' => 8, 'product_class_id' => 2],
                 3 => ['price01' => null, 'tax_rate' => 8, 'product_class_id' => 3],
             ],
             'max', 0],
            ['price01',
             [
                 1 => ['price01' => null, 'tax_rate' => 10, 'product_class_id' => 1],
                 2 => ['price01' => null, 'tax_rate' => 8, 'product_class_id' => 2],
                 3 => ['price01' => null, 'tax_rate' => 8, 'product_class_id' => 3],
             ],
             'min', 0],
            // see https://github.com/EC-CUBE/eccube-2_13/pull/298#issuecomment-522072546
            // ['price02',
            //  [
            //      1 => ['price02' => 1000, 'tax_rate' => 10, 'product_class_id' => 1],
            //      2 => ['price02' => 1010, 'tax_rate' => 8, 'product_class_id' => 2],
            //  ],
            //  'min', 1],
        ];
    }

    /**
     * @dataProvider findProductClassIdByRuleProvider
     */
    public function testFindProductClassIdByRule($col, $rules, $type, $expected)
    {
        $this->expected = $expected;
        $this->actual = $this->wrapperToFindProductClassIdByRule($col, $rules, $type);
        $this->verify();
    }

    public function testGetProductsClassRelateTaxRule()
    {
        $_SESSION['member_id'] = '1';
        $objGenerator = new FixtureGenerator($this->objQuery);
        $product_ids = [];
        for ($i = 0; $i < 3; $i++) {
            $product_ids[] = $objGenerator->createProduct();
        }

        // 0 番目商品の規格に商品別税率を設定する
        $now = new \DateTime();
        $now->modify('-1 days');
        $product_class_ids = $this->objQuery->getCol('product_class_id', 'dtb_products_class', 'product_id = ? AND del_flg = 0', [$product_ids[0]]);
        foreach ($product_class_ids as $product_class_id) {
            SC_Helper_TaxRule_Ex::setTaxRule(1, 10, $now->format('Y/m/d H:i:s'), null, 0, $product_ids[0], $product_class_id);
        }

        $arrTaxRules = $this->wrapperToGetProductsClassRelateTaxRule($product_ids, 1);
        $this->actual = [];
        foreach ($arrTaxRules as $product_id => $rules) {
            foreach ($rules as $product_class_id => $rule) {
                $this->actual[] = $rule['tax_rate'];
            }
        }

        $this->expected = [10, 10, 10, 8, 8, 8, 8, 8, 8];
        $this->verify();
    }

    /**
     * @param array $product_ids 取得対象の商品ID
     * @param int $option_product_tax_rule 商品別税率オプション
     * @return array 税率を含む商品ID, 商品規格IDごとの配列. $option_product_tax_rule が 0 の場合は空の配列を返す
     */
    private function wrapperToGetProductsClassRelateTaxRule(array $product_ids, $option_product_tax_rule = OPTION_PRODUCT_TAX_RULE)
    {
        $method = self::getMethod('getProductsClassRelateTaxRule');
        return $method->invoke(null, $product_ids, $option_product_tax_rule);
    }

    /**
     * @param string $col 比較対象のカラム
     * @param array|null $rules 商品規格IDを添字とした商品規格別の税率
     * @param string $operator max or min
     * @return int product_class_id
     */
    private function wrapperToFindProductClassIdByRule($col, $rules, $operator)
    {
        $method = self::getMethod('findProductClassIdByRule');
        return $method->invoke(null, $col, $rules, $operator);
    }

    /**
     * @param int $carry_price 現在の金額
     * @param int $price 比較対象の金額
     * @param int $carry_rate 現在の税率
     * @param int $rate 比較対象の税率
     * @param string $operator max or min
     * @return bool
     */
    private function wrapperToCheckPriceAndTaxRate($carry_price, $price, $carry_rate, $rate, $operator = 'max')
    {
        $method = self::getMethod('checkPriceAndTaxRate');
        return $method->invoke(null, $carry_price, $price, $carry_rate, $rate, $operator);
    }

    /**
     * @param string $name
     * @return ReflectionMethod
     */
    private static function getMethod($name)
    {
        $class = new \ReflectionClass('SC_Product_Ex');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
