<?php

class SC_CheckError_ALNUM_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'ALNUM_CHECK';
    }

    public function testALNUMCHECK()
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');

        $this->arrForm = [self::FORM_NAME => $faker->bothify('##??')];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    /**
     * 数値型入力のテスト.
     *
     * フォームからの入力で数値型(int)が入力されることは無いが
     * 入力があるとエラーになってしまう
     */
    public function testALNUMCHECKWithNumber()
    {
        $this->markTestIncomplete('数値型はサポートされていません');
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '※ ALNUM_CHECKは英数字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testALNUMCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testALNUMCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testALNUMCHECKWithSpecial()
    {
        $this->arrForm = [self::FORM_NAME => 'a1.'];
        $this->expected = '※ ALNUM_CHECKは英数字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}
