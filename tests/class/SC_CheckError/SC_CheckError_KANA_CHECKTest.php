<?php

class SC_CheckError_KANA_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'KANA_CHECK';
    }

    public function testKANACHECK()
    {
        $this->arrForm = [self::FORM_NAME => 'ア'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANACHECKWithNumber()
    {
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '※ KANA_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testKANACHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANACHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANACHECKWithKanaOfHarf()
    {
        $this->arrForm = [self::FORM_NAME => 'ｱ'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANACHECKWithHiragana()
    {
        $this->arrForm = [self::FORM_NAME => 'あ'];
        $this->expected = '※ KANA_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testKANACHECKWithKanaAndBlank()
    {
        $this->arrForm = [self::FORM_NAME => 'アイ ウエオ'];
        $this->expected = '※ KANA_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testKANACHECKWithKanaAndWideBlank()
    {
        $this->arrForm = [self::FORM_NAME => 'アイ　ウエオ'];
        $this->expected = '※ KANA_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}
