<?php

$HOME = realpath(__DIR__).'/../../..';
require_once $HOME.'/tests/class/SC_CartSession/SC_CartSession_TestBase.php';

/**
 * SC_CartSession::getNextCartID()のテストクラス.
 */
class SC_CartSession_getNextCartIDTest extends SC_CartSession_TestBase
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

    public function testGetNextCartID最初の商品追加後は次のIDが2()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);

        $this->assertEquals(2, $this->objCartSession->getNextCartID(1), '1商品追加後、次のcart_noは2');
    }

    public function testGetNextCartID商品追加後は次のIDを返す()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);

        $this->assertEquals(2, $this->objCartSession->getNextCartID(1), '次のcart_noは2');
    }

    public function testGetNextCartID複数商品追加後は最大値プラス1()
    {
        $this->setUpBigProductClass();
        for ($i = 3000; $i < 3005; $i++) {
            $this->objCartSession->addProduct($i, 1);
        }

        $nextId = $this->objCartSession->getNextCartID(1);
        $this->assertGreaterThanOrEqual(2, $nextId, '次のcart_noは2以上');
    }

    public function testGetNextCartID異なる商品種別は独立している()
    {
        $this->setUpProductClass();
        $this->objCartSession->addProduct('1001', 1);
        $this->objCartSession->addProduct('1002', 1);

        $nextId1 = $this->objCartSession->getNextCartID(1);
        $nextId2 = $this->objCartSession->getNextCartID(2);

        $this->assertEquals(2, $nextId1, '商品種別1の次のIDは2');
        $this->assertEquals(2, $nextId2, '商品種別2の次のIDは2');
    }
}
