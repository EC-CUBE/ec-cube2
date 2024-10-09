<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_Product/SC_Product_TestBase.php';

class SC_Product_findProductCountTest extends SC_Product_TestBase
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

    public function testFindProductCount全ての商品数を返す()
    {
        $this->expected = 3;

        $this->actual = $this->objProducts->findProductCount($this->objQuery);

        $this->verify('商品数');
    }

    public function testFindProductCount検索条件に一致する商品数を返す()
    {
        $this->objQuery->setWhere('product_id = ?');
        $arrVal = [1001];

        $this->expected = 1;

        $this->actual = $this->objProducts->findProductCount($this->objQuery, $arrVal);

        $this->verify('検索商品数');
    }
}
