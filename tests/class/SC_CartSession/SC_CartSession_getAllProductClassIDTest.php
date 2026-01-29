<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_CartSession/SC_CartSession_TestBase.php';

/**
 * SC_CartSession::getAllProductClassID()のテストクラス.
 */
class SC_CartSession_getAllProductClassIDTest extends SC_CartSession_TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->objCartSession = new SC_CartSession_Ex();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetAllProductClassID空のカートは空配列()
    {
        $this->setUpProductClass();

        $this->assertEmpty($this->objCartSession->getAllProductClassID(1), '空のカートは空配列');
    }

    public function testGetAllProductClassID商品を追加すると商品規格IDが取得できる()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);

        $this->assertEquals(['1001'], $this->objCartSession->getAllProductClassID(1), '商品規格ID 1001');
    }

    public function testGetAllProductClassID複数商品の商品規格IDが取得できる()
    {
        $this->setUpBigProductClass();
        $this->objCartSession->addProduct('3000', 1);
        $this->objCartSession->addProduct('3003', 1);
        $this->objCartSession->addProduct('3006', 1);

        $productClassIds = $this->objCartSession->getAllProductClassID(1);
        $this->assertCount(3, $productClassIds, '3つの商品規格ID');
        $this->assertContains('3000', $productClassIds);
        $this->assertContains('3003', $productClassIds);
        $this->assertContains('3006', $productClassIds);
    }

    public function testGetAllProductClassID異なる商品種別は別々()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);
        $this->objCartSession->addProduct('1002', 1);

        $this->assertEquals(['1001'], $this->objCartSession->getAllProductClassID(1), '商品種別1は1001のみ');
        $this->assertEquals(['1002'], $this->objCartSession->getAllProductClassID(2), '商品種別2は1002のみ');
    }
}
