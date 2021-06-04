<?php

class SC_CheckError_ALPHA_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'ALPHA_CHECK';
    }

    public function testALPHA_CHECK()
    {
        $this->arrForm = [self::FORM_NAME => 'a'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }


    public function testALPHA_CHECKWithNumber()
    {
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '※ ALPHA_CHECKは半角英字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testALPHA_CHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testALPHA_CHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testALPHA_CHECKWithNumbeOfString()
    {
        $this->arrForm = [self::FORM_NAME => '5'];
        $this->expected = '※ ALPHA_CHECKは半角英字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testALPHA_CHECKWithFloat()
    {
        $this->arrForm = [self::FORM_NAME => 1.1];
        $this->expected = '※ ALPHA_CHECKは半角英字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testALPHA_CHECKWithWideAlpha()
    {
        $this->arrForm = [self::FORM_NAME => 'ａ'];
        $this->expected = '※ ALPHA_CHECKは半角英字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testALPHA_CHECKWithSpecial()
    {
        $this->arrForm = [self::FORM_NAME => '.'];
        $this->expected = '※ ALPHA_CHECKは半角英字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}

