<?php

class SC_CheckError_ALPHA_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'ALPHA_CHECK';
    }

    public function testALPHACHECK()
    {
        $this->arrForm = [self::FORM_NAME => 'a'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testALPHACHECKWithNumber()
    {
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '※ ALPHA_CHECKは半角英字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testALPHACHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testALPHACHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testALPHACHECKWithNumbeOfString()
    {
        $this->arrForm = [self::FORM_NAME => '5'];
        $this->expected = '※ ALPHA_CHECKは半角英字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testALPHACHECKWithFloat()
    {
        $this->arrForm = [self::FORM_NAME => 1.1];
        $this->expected = '※ ALPHA_CHECKは半角英字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testALPHACHECKWithWideAlpha()
    {
        $this->arrForm = [self::FORM_NAME => 'ａ'];
        $this->expected = '※ ALPHA_CHECKは半角英字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testALPHACHECKWithSpecial()
    {
        $this->arrForm = [self::FORM_NAME => '.'];
        $this->expected = '※ ALPHA_CHECKは半角英字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}
