<?php

class SC_CheckError_NUM_POINT_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'NUM_POINT_CHECK';
    }

    public function testNUMPOINTCHECK()
    {
        $this->arrForm = [self::FORM_NAME => 'a'];
        $this->expected = '※ NUM_POINT_CHECKは数字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testNUMPOINTCHECKWithNoError()
    {
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMPOINTCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMPOINTCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMPOINTCHECKWithString()
    {
        $this->arrForm = [self::FORM_NAME => '5'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMPOINTCHECKWithFloat()
    {
        $this->arrForm = [self::FORM_NAME => 1.1];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMPOINTCHECKWithIntmax()
    {
        $this->arrForm = [self::FORM_NAME => PHP_INT_MAX];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }
}
