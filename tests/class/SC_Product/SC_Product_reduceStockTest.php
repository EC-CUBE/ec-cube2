<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_Product/SC_Product_TestBase.php';

class SC_Product_reduceStockTest extends SC_Product_TestBase
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

    public function testReduceStock減少数0はFalse()
    {
        $productClassId = '1001';
        $quantity = '0';
        $this->expected = false;
        $this->actual = $this->objProducts->reduceStock($productClassId, $quantity);

        $this->verify('減少数０');
    }

    public function testReduceStock減少数1はTrue()
    {
        $productClassId = '1001';
        $quantity = '1';
        $this->expected = true;
        $this->actual = $this->objProducts->reduceStock($productClassId, $quantity);

        $this->verify('減少数1');
    }

    public function testReduceStock在庫数をマイナスにする数はFalse()
    {
        $productClassId = '1001';
        $quantity = '100';
        $this->expected = false;
        $this->actual = $this->objProducts->reduceStock($productClassId, $quantity);

        $this->verify('在庫数マイナス');
    }

    public function testReduceStock在庫数無限の場合はTrue()
    {
        $productClassId = '1002';
        $quantity = '100';
        $this->expected = true;
        $this->actual = $this->objProducts->reduceStock($productClassId, $quantity);

        $this->verify('在庫数無限');
    }
}
