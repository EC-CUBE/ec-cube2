<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_CartSession/SC_CartSession_TestBase.php';

/**
 * SC_CartSession 数量関連メソッドのテストクラス.
 */
class SC_CartSession_quantityTest extends SC_CartSession_TestBase
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

    public function testGetQuantity数量を取得できる()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 5);

        $this->assertEquals(5, $this->objCartSession->getQuantity(1, 1), 'cart_no=1の数量は5');
    }

    public function testGetQuantity存在しないカート番号は0を返す()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 5);

        $this->assertEquals(0, $this->objCartSession->getQuantity(999, 1), '存在しないcart_noは0');
    }

    public function testSetQuantity数量を設定できる()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);

        $this->assertEquals(1, $this->objCartSession->getQuantity(1, 1));

        $this->objCartSession->setQuantity(10, 1, 1);

        $this->assertEquals(10, $this->objCartSession->getQuantity(1, 1), '数量が10に変更された');
    }

    public function testSetQuantity数量を0に設定できる()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 5);

        $this->objCartSession->setQuantity(0, 1, 1);

        $this->assertEquals(0, $this->objCartSession->getQuantity(1, 1), '数量が0に設定された');
    }

    public function testUpQuantity数量を1増やせる()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);

        $this->objCartSession->upQuantity(1, 1);

        $this->assertEquals(2, $this->objCartSession->getQuantity(1, 1), '数量が2に増えた');
    }

    public function testUpQuantity複数回増やせる()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);

        $this->objCartSession->upQuantity(1, 1);
        $this->objCartSession->upQuantity(1, 1);
        $this->objCartSession->upQuantity(1, 1);

        $this->assertEquals(4, $this->objCartSession->getQuantity(1, 1), '数量が4に増えた');
    }

    public function testDownQuantity数量を1減らせる()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 5);

        $this->objCartSession->downQuantity(1, 1);

        $this->assertEquals(4, $this->objCartSession->getQuantity(1, 1), '数量が4に減った');
    }

    public function testDownQuantity数量1の時は減らしても1のまま()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);

        $this->objCartSession->downQuantity(1, 1);

        $this->assertEquals(1, $this->objCartSession->getQuantity(1, 1), '数量は1のまま');
    }

    public function testGetTotalQuantity合計数量を取得できる()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 3);

        $this->assertEquals(3, $this->objCartSession->getTotalQuantity(1), '合計数量は3');
    }

    public function testGetTotalQuantity複数商品の合計数量()
    {
        $this->setUpBigProductClass();
        $this->objCartSession->addProduct('3000', 2);
        $this->objCartSession->addProduct('3003', 5);
        $this->objCartSession->addProduct('3006', 1);

        $this->assertEquals(8, $this->objCartSession->getTotalQuantity(1), '商品種別1の合計数量は8');
    }

    public function testGetTotalQuantity空のカートは0()
    {
        $this->setUpProductClass();

        $this->assertEquals(0, $this->objCartSession->getTotalQuantity(1), '空のカートの合計数量は0');
    }
}
