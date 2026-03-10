<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_CartSession/SC_CartSession_TestBase.php';

/**
 * SC_CartSession::delProduct(), delAllProducts()のテストクラス.
 */
class SC_CartSession_delProductTest extends SC_CartSession_TestBase
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

    public function testDelProduct商品を削除できる()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);
        $this->objCartSession->addProduct('1002', 1);

        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(1), '商品種別1は1個');
        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(2), '商品種別2は1個');

        // cart_no=1の商品を削除
        $this->objCartSession->delProduct(1, 1);

        $this->assertEquals(0, $this->objCartSession->getTotalQuantity(1), '商品種別1は削除された');
        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(2), '商品種別2は残っている');
    }

    public function testDelProduct存在しないカート番号を指定しても何も起きない()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);

        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(1));

        // 存在しないcart_no=999を削除
        $this->objCartSession->delProduct(999, 1);

        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(1), '商品は残っている');
    }

    public function testDelProduct異なる商品種別を指定しても削除されない()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);

        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(1));

        // 商品種別2で削除を試みる（実際は商品種別1）
        $this->objCartSession->delProduct(1, 2);

        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(1), '商品は残っている');
    }

    public function testDelAllProducts全商品を削除できる()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);

        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(1));

        $this->objCartSession->delAllProducts(1);

        $this->assertEquals(0, $this->objCartSession->getTotalQuantity(1), '全商品が削除された');
    }

    public function testDelAllProducts複数商品を全削除できる()
    {
        $this->setUpBigProductClass();
        for ($i = 3000; $i < 3010; $i++) {
            $this->objCartSession->addProduct($i, 1);
        }

        $total = $this->objCartSession->getTotalQuantity(1)
            + $this->objCartSession->getTotalQuantity(2)
            + $this->objCartSession->getTotalQuantity(3);
        $this->assertEquals(10, $total, '10商品追加されている');

        // 商品種別1を全削除
        $this->objCartSession->delAllProducts(1);

        $this->assertEquals(0, $this->objCartSession->getTotalQuantity(1), '商品種別1は全削除');
        $this->assertGreaterThan(0, $this->objCartSession->getTotalQuantity(2) + $this->objCartSession->getTotalQuantity(3), '他の商品種別は残っている');
    }

    public function testDelAllProducts空のカートを削除しても何も起きない()
    {
        $this->setUpProductClass();

        $this->assertEquals(0, $this->objCartSession->getTotalQuantity(1));

        $this->objCartSession->delAllProducts(1);

        $this->assertEquals(0, $this->objCartSession->getTotalQuantity(1), '変化なし');
    }

    public function testDelAllProducts異なる商品種別は削除されない()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);
        $this->objCartSession->addProduct('1002', 1);

        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(1));
        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(2));

        // 商品種別1だけ削除
        $this->objCartSession->delAllProducts(1);

        $this->assertEquals(0, $this->objCartSession->getTotalQuantity(1), '商品種別1は削除');
        $this->assertEquals(1, $this->objCartSession->getTotalQuantity(2), '商品種別2は残っている');
    }
}
