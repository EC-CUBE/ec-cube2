<?php

class SC_CheckError_GRAPH_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'GRAPH_CHECK';
    }

    public function testGRAPHCHECK()
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
     */
    public function testGRAPHCHECKWithNumber()
    {
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testGRAPHCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testGRAPHCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testGRAPHCHECKWithSentence()
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');

        $this->arrForm = [self::FORM_NAME => $faker->sentence()];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testGRAPHCHECKWithWide()
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');

        $this->arrForm = [self::FORM_NAME => $faker->company];
        $this->expected = '※ GRAPH_CHECKは英数記号で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}
