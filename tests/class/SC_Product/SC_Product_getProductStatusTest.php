<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_Product/SC_Product_TestBase.php';

class SC_Product_getProductStatusTest extends SC_Product_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpProductClass();
        $this->setUpProductStatus();
        $this->objProducts = new SC_Product_Ex();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    // ///////////////////////////////////////

    public function testGetProductStatus商品IDなしは空の配列を返す()
    {
        $this->expected = [];
        $productIds = null;

        $this->actual = $this->objProducts->getProductStatus($productIds);

        $this->verify('空の配列');
    }

    public function testGetProductStatus指定した商品IDの商品ステータスを返す()
    {
        $this->expected = ['1001' => ['1']];
        $productIds = ['1001'];

        $this->actual = $this->objProducts->getProductStatus($productIds);

        $this->verify('商品ステータス');
    }
}
