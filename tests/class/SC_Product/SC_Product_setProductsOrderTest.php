<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_Product/SC_Product_TestBase.php';

class SC_Product_setProductsOrderTest extends SC_Product_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->objProducts = new SC_Product_Ex();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////
    public function testSetProductsOrderデフォルト引数()
    {
        $this->objProducts->setProductsOrder('name');

        $this->actual = $this->objProducts->arrOrderData;
        $this->expected = ['col' => 'name', 'table' => 'dtb_products', 'order' => 'ASC'];

        $this->verify('デフォルト引数');
    }

    public function testSetProductsOrder引数指定()
    {
        $this->objProducts->setProductsOrder('name', 'dtb_products_class', 'DESC');

        $this->actual = $this->objProducts->arrOrderData;
        $this->expected = ['col' => 'name', 'table' => 'dtb_products_class', 'order' => 'DESC'];

        $this->verify('デフォルト引数');
    }
}
