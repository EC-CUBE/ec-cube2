<?php

class SC_CheckError_KANABLANK_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'KANABLANK_CHECK';
    }

    public function testKANABLANK_CHECK()
    {
        $this->arrForm = [self::FORM_NAME => 'ア'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }


    public function testKANABLANK_CHECKWithNumber()
    {
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '※ KANABLANK_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANK_CHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANK_CHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANK_CHECKWithKanaOfHarf()
    {
        $this->arrForm = [self::FORM_NAME => 'ｱ'];
        $this->expected = '※ KANABLANK_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify('半角カナはエラー');
    }

    public function testKANABLANK_CHECKWithHiragana()
    {
        $this->arrForm = [self::FORM_NAME => 'あ'];
        $this->expected = '※ KANABLANK_CHECKはカタカナで入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANK_CHECKWithKanaAndBlank()
    {
        $this->arrForm = [self::FORM_NAME => 'アイ ウエオ'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANK_CHECKWithKanaAndWideBlank()
    {
        $this->arrForm = [self::FORM_NAME => 'アイ　ウエオ'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testKANABLANK_CHECKWithTabAndLinefield()
    {
        $this->arrForm = [self::FORM_NAME => "アイ\tウ\nエ\r\nオ"];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }
}

