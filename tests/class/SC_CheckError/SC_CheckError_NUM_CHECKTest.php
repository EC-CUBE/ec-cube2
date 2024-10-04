<?php

class SC_CheckError_NUM_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'NUM_CHECK';
    }

    public function testNUM_CHECK()
    {
        $this->arrForm = [self::FORM_NAME => 'a'];
        $this->expected = '※ NUM_CHECKは数字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }


    public function testNUM_CHECKWithNoError()
    {
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_CHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_CHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_CHECKWithString()
    {
        $this->arrForm = [self::FORM_NAME => '5'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_CHECKWithFloat()
    {
        $this->arrForm = [self::FORM_NAME => 1.1];
        $this->expected = '※ NUM_CHECKは数字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_CHECKWithIntmax()
    {
        $this->arrForm = [self::FORM_NAME => PHP_INT_MAX];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }
}

