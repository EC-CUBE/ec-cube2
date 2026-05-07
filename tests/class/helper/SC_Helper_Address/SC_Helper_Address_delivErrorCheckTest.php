<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/helper/SC_Helper_Address/SC_Helper_Address_TestBase.php';
/**
 * SC_Helper_Address::delivErrorCheck() のテスト
 *
 * delivErrorCheck() の戻り値は、「false: 正常」「true: エラー」である。
 */
class SC_Helper_Address_delivErrorCheckTest extends SC_Helper_Address_TestBase
{
    protected SC_Helper_Address $objAddress;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objAddress = new SC_Helper_Address_Ex();
    }

    public function test典型パターン()
    {
        $this->assertFalse($this->objAddress->delivErrorCheck([
            'customer_id' => '9',
            'other_deliv_id' => '102',
        ]));
    }

    public function testInt()
    {
        $this->assertFalse($this->objAddress->delivErrorCheck([
            'customer_id' => 9,
            'other_deliv_id' => 102,
        ]));
    }

    public function testFloat()
    {
        $this->assertTrue($this->objAddress->delivErrorCheck([
            'customer_id' => 9.1,
        ]));
    }

    public function test空配列()
    {
        $this->assertTrue($this->objAddress->delivErrorCheck([]));
    }

    public function testCustomerIdのみ()
    {
        $this->assertFalse($this->objAddress->delivErrorCheck([
            'customer_id' => '9',
        ]));
    }

    public function testOtherDelivIdのみ()
    {
        $this->assertTrue($this->objAddress->delivErrorCheck([
            'other_deliv_id' => '102',
        ]));
    }
}
