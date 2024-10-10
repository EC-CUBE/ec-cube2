<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_Product/SC_Product_TestBase.php';

class SC_Product_getProductsClassTest extends SC_Product_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpProductClass();
        $this->objProducts = new SC_Product_Ex();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////

    public function testGetProductsClass商品規格IDから規格情報を返す()
    {
        $this->expected = [
                'product_id' => '1001', 'del_flg' => '0', 'point_rate' => '0', 'stock' => '99', 'stock_unlimited' => '0', 'sale_limit' => null, 'price01' => '1500', 'price02' => '1500', 'product_code' => 'code1001', 'product_class_id' => '1001', 'product_type_id' => '1', 'down_filename' => null, 'down_realfilename' => null, 'classcategory_name1' => 'cat1001', 'rank1' => '1', 'class_name1' => '味', 'class_id1' => '1', 'classcategory_id1' => '1001', 'classcategory_id2' => '1002', 'classcategory_name2' => 'cat1002', 'rank2' => '2', 'class_name2' => '味', 'class_id2' => '1', 'price01_inctax' => SC_Helper_TaxRule_Ex::sfCalcIncTax(1500), 'price02_inctax' => SC_Helper_TaxRule_Ex::sfCalcIncTax(1500),
        ];

        $this->actual = $this->objProducts->getProductsClass('1001');

        $this->verify('商品規格');
    }
}
