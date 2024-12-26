<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_CartSession/SC_CartSession_TestBase.php';

/**
 * SC_CartSession_getAllCartList
 *
 * @version $id$
 *
 * @copyright
 * @author Nobuhiko Kimoto <info@nob-log.info>
 * @license
 */
class SC_CartSession_getAllCartListTest extends SC_CartSession_TestBase
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

    // ///////////////////////////////////////

    public function testGetAllCartList商品を追加していなければ空の配列を返す()
    {
        $this->setUpProductClass();
        $this->expected = 0;
        $this->actual = count($this->objCartSession->getAllCartList());

        $this->verify('商品数');
    }

    public function testGetAllCartList商品を1つ追加した場合1つの配列を返す()
    {
        $this->setUpProductClass();
        $this->expected = 1;
        $this->objCartSession->addProduct('1001', 1);

        $cartList = $this->objCartSession->getAllCartList();
        $this->actual = count($cartList);

        $this->verify('カート数');
    }

    public function testGetAllCartList違う商品種別の商品を追加した場合用品種別分の配列を返す()
    {
        $this->setUpProductClass();
        $this->expected = 2;
        $this->objCartSession->addProduct('1001', 1);
        $this->objCartSession->addProduct('1002', 1);

        $cartList = $this->objCartSession->getAllCartList();
        $this->actual = count($cartList);

        $this->verify('カート数');
    }

    public function testGetAllCartList複数回呼んでも同じ内容が返される()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);
        $this->objCartSession->addProduct('1002', 1);

        $this->expected = $this->objCartSession->getAllCartList();
        $this->actual = $this->objCartSession->getAllCartList();

        $this->verify('カートの内容');
    }
}
