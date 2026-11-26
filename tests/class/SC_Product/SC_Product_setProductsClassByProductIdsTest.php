<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_Product/SC_Product_TestBase.php';

class SC_Product_setProductsClassByProductIdsTest extends SC_Product_TestBase
{
    public $objProducts;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpProductClass();
        $this->objProducts = new SC_Product_Ex();
    }

    public function testSetProductsClassByProductIds通常価格がNULLの場合は空文字を設定する()
    {
        // 商品 2001 は price01 が NULL (規格は削除済みのため $has_deleted = true で取得する)
        $deprecations = $this->collectDeprecations(function () {
            $this->objProducts->setProductsClassByProductIds(['2001'], true);
        });

        $arrClassCat = $this->objProducts->classCategories['2001']['__unselected2']['#0'];
        $this->assertSame('', $arrClassCat['price01']);
        $this->assertSame(number_format(SC_Helper_TaxRule_Ex::sfCalcIncTax(2000, '2001')), $arrClassCat['price02']);
        $this->assertSame([], $deprecations);
    }
}
