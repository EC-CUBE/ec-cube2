<?php

class SC_CheckError_KANABLANK_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'KANABLANK_CHECK';
    }

    public function testKANABLANKCHECK()
    {
        $this->arrForm = [self::FORM_NAME => 'ア'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANKCHECKWithNumber()
    {
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '※ KANABLANK_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANKCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANKCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANKCHECKWithKanaOfHarf()
    {
        $this->arrForm = [self::FORM_NAME => 'ｱ'];
        $this->expected = '※ KANABLANK_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify('半角カナはエラー');
    }

    public function testKANABLANKCHECKWithHiragana()
    {
        $this->arrForm = [self::FORM_NAME => 'あ'];
        $this->expected = '※ KANABLANK_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANKCHECKWithKanaAndBlank()
    {
        $this->arrForm = [self::FORM_NAME => 'アイ ウエオ'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANKCHECKWithKanaAndWideBlank()
    {
        $this->arrForm = [self::FORM_NAME => 'アイ　ウエオ'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANKCHECKWithTabAndLinefield()
    {
        $this->arrForm = [self::FORM_NAME => "アイ\tウ\nエ\r\nオ"];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }
}
