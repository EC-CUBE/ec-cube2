<?php

class SC_Helper_DB_sfHasProductClassTest extends SC_Helper_DB_TestBase
{
    /** @var SC_Helper_DB_Ex */
    protected $objDb;

    protected function setUp()
    {
        parent::setUp();
        $this->objDb = new SC_Helper_DB_Ex();
    }

    public function testSfHasProductClass()
    {
        $product_id = $this->objGenerator->createProduct();
        $this->assertTrue($this->objDb->sfHasProductClass($product_id));
    }

    public function testSfHasProductClassWithIlligalArgument()
    {
        $this->assertFalse($this->objDb->sfHasProductClass(999999999));
    }

    public function testSfHasProductClassWithFalse()
    {
        $product_id = $this->objGenerator->createProduct('', 0);
        $this->assertFalse($this->objDb->sfHasProductClass($product_id));
    }
}
