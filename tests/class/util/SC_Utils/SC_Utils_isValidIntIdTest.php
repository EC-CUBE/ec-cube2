<?php

$HOME = realpath(__DIR__).'/../../../..';
require_once $HOME.'/tests/class/Common_TestCase.php';

/**
 * SC_Helper_Purchase::isValidIntId()のテストクラス.
 *
 * @author Seasoft 塚田将久 (新規作成)
 */
class SC_Utils_isValidIntIdTest extends Common_TestCase
{
    public function test典型例()
    {
        $this->assertTrue(SC_Utils::isValidIntId(123));
        $this->assertTrue(SC_Utils::isValidIntId('123'));
    }

    public function test0バイト文字列()
    {
        $this->assertFalse(SC_Utils::isValidIntId(''));
    }

    public function test不正な型()
    {
        $this->assertFalse(SC_Utils::isValidIntId(null));
        $this->assertFalse(SC_Utils::isValidIntId(1.0));
        $this->assertFalse(SC_Utils::isValidIntId(true));
        $this->assertFalse(SC_Utils::isValidIntId([]));
        $this->assertFalse(SC_Utils::isValidIntId(new stdClass()));
    }

    public function testINTLENの最大長()
    {
        $this->assertTrue(SC_Utils::isValidIntId(999999999));
        $this->assertTrue(SC_Utils::isValidIntId('999999999'));
    }

    public function testINTLENの最大長を超える()
    {
        $this->assertFalse(SC_Utils::isValidIntId(10000000000));
        $this->assertFalse(SC_Utils::isValidIntId('10000000000'));
    }

    public function testゼロ()
    {
        $this->assertTrue(SC_Utils::isValidIntId(0));
        $this->assertTrue(SC_Utils::isValidIntId('0'));
    }

    public function test数値でない()
    {
        $this->assertFalse(SC_Utils::isValidIntId('HELLO123'));
        $this->assertFalse(SC_Utils::isValidIntId('123HELLO'));
        $this->assertFalse(SC_Utils::isValidIntId('0x12'));
    }

    public function test小数()
    {
        $this->assertFalse(SC_Utils::isValidIntId(123.0));
        $this->assertFalse(SC_Utils::isValidIntId('123.0'));
        $this->assertFalse(SC_Utils::isValidIntId(123.456));
        $this->assertFalse(SC_Utils::isValidIntId('123.456'));
    }

    public function test負の数()
    {
        $this->assertFalse(SC_Utils::isValidIntId(-123));
        $this->assertFalse(SC_Utils::isValidIntId('-123'));
        $this->assertFalse(SC_Utils::isValidIntId(-123.456));
        $this->assertFalse(SC_Utils::isValidIntId('-123.456'));
    }
}
