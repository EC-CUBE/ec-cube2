<?php

class SC_CheckError_KANA_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'KANA_CHECK';
    }

    public function testKANA_CHECK()
    {
        $this->arrForm = [self::FORM_NAME => 'ア'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }


    public function testKANA_CHECKWithNumber()
    {
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '※ KANA_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testKANA_CHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANA_CHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANA_CHECKWithKanaOfHarf()
    {
        $this->arrForm = [self::FORM_NAME => 'ｱ'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANA_CHECKWithHiragana()
    {
        $this->arrForm = [self::FORM_NAME => 'あ'];
        $this->expected = '※ KANA_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testKANA_CHECKWithKanaAndBlank()
    {
        $this->arrForm = [self::FORM_NAME => 'アイ ウエオ'];
        $this->expected = '※ KANA_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testKANA_CHECKWithKanaAndWideBlank()
    {
        $this->arrForm = [self::FORM_NAME => 'アイ　ウエオ'];
        $this->expected = '※ KANA_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}

