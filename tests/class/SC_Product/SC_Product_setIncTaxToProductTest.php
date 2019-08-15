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

    private function wrapperToFindProductClassIdByRule($col, $rules, $type)
    {
        $method = self::getMethod('findProductClassIdByRule');
        return $method->invoke(null, $col, $rules, $type);
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
