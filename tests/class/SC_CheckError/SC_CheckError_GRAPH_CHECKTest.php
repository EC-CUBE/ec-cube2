<?php

class SC_CheckError_GRAPH_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'GRAPH_CHECK';
    }

    public function testGRAPH_CHECK()
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');

        $this->arrForm = [self::FORM_NAME => $faker->asciify('***')];
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
    public function testGRAPH_CHECKWithNumber()
    {
        $this->markTestIncomplete('数値型はサポートされていません');
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '※ GRAPH_CHECKは英数字で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testGRAPH_CHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testGRAPH_CHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testGRAPH_CHECKWithSentence()
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');

        $this->arrForm = [self::FORM_NAME => $faker->sentence()];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testGRAPH_CHECKWithWide()
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');

        $this->arrForm = [self::FORM_NAME => $faker->company];
        $this->expected = '※ GRAPH_CHECKは英数記号で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}

