<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_Product/SC_Product_TestBase.php';

class SC_Product_findProductsOrderTest extends SC_Product_TestBase
{
    public $objProducts;

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

    public function testFindProductIdsOrderArrOrderData設定時もLimitOffsetが維持される()
    {
        // サブクエリ生成時の SC_Query::getSql() による LIMIT/OFFSET のリセットが
        // 呼び出し元の SC_Query に影響しないことを確認する (#1420)
        $this->objProducts->setProductsOrder('product_id', 'dtb_products', 'DESC');
        $this->objQuery->setLimitOffset(1, 1);
        $this->expected = ['1001'];

        $this->actual = $this->objProducts->findProductIdsOrder($this->objQuery);

        $this->verify('arrOrderData設定時の LIMIT/OFFSET');
    }
}
