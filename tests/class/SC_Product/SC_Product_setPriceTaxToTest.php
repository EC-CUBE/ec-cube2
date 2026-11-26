<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_Product/SC_Product_TestBase.php';

class SC_Product_setPriceTaxToTest extends SC_Product_TestBase
{
    public function testSetPriceTaxTo通常価格がNULLの場合は0を設定する()
    {
        $arrProducts = [
            [
                'product_id' => '2001',
                'price01_min' => null,
                'price01_max' => null,
                'price02_min' => '2000',
                'price02_max' => '2000',
            ],
        ];

        $deprecations = $this->collectDeprecations(function () use (&$arrProducts) {
            SC_Product_Ex::setPriceTaxTo($arrProducts);
        });

        $this->assertSame('0', $arrProducts[0]['price01_min_format']);
        $this->assertSame('0', $arrProducts[0]['price01_max_format']);
        $this->assertSame('0', $arrProducts[0]['price01_min_inctax_format']);
        $this->assertSame('0', $arrProducts[0]['price01_max_inctax_format']);
        $this->assertSame('2,000', $arrProducts[0]['price02_min_format']);
        $this->assertSame(number_format(SC_Helper_TaxRule_Ex::sfCalcIncTax(2000, '2001')), $arrProducts[0]['price02_min_inctax_format']);
        $this->assertSame([], $deprecations);
    }
}
