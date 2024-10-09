<?php

class SC_CheckError_NUM_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'NUM_CHECK';
    }

    public function testNUMCHECK()
    {
        $this->arrForm = [self::FORM_NAME => 'a'];
        $this->expected = '※ NUM_CHECKは数字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testNUMCHECKWithNoError()
    {
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMCHECKWithString()
    {
        $this->arrForm = [self::FORM_NAME => '5'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMCHECKWithFloat()
    {
        $this->arrForm = [self::FORM_NAME => 1.1];
        $this->expected = '※ NUM_CHECKは数字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testNUMCHECKWithIntmax()
    {
        $this->arrForm = [self::FORM_NAME => PHP_INT_MAX];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }
}
