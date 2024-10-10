<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_Product/SC_Product_TestBase.php';

class SC_Product_findProductsOrderTest extends SC_Product_TestBase
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

    public function testFindProductIdsOrder商品ID降順()
    {
        // 商品ID降順で商品IDを取得する
        $this->objQuery->setOrder('product_id DESC');
        $this->expected = ['1002', '1001'];

        $this->actual = $this->objProducts->findProductIdsOrder($this->objQuery);

        $this->verify('商品ID降順');
    }

    public function testFindProductIdsOrder商品名昇順()
    {
        // 商品名昇順で商品IDを取得する
        $this->objQuery->setOrder('product_id ASC');
        $this->expected = ['1001', '1002'];

        $this->actual = $this->objProducts->findProductIdsOrder($this->objQuery);

        $this->verify('商品ID昇順');
    }

    public function testFindProductIdsOrderArrOrderDataの設定による並び順()
    {
        // setProductsOrderを行う
        $this->objProducts->setProductsOrder('product_id');
        $this->expected = ['1001', '1002'];

        $this->actual = $this->objProducts->findProductIdsOrder($this->objQuery);

        $this->verify('arrOrderData設定順');
    }
}
