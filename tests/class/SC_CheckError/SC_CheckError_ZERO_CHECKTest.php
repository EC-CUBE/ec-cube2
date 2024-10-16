<?php

class SC_CheckError_ZERO_CHECKTest extends SC_CheckError_AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'ZERO_CHECK';
    }

    public function testZEROCHECK()
    {
        $this->arrForm = [self::FORM_NAME => '0'];
        $this->expected = '※ ZERO_CHECKは1以上を入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testZEROCHECKWithNumber()
    {
        $this->arrForm = [self::FORM_NAME => 0];
        $this->expected = '※ ZERO_CHECKは1以上を入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testZEROCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testZEROCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testZEROCHECKWithSentence()
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');

        $this->arrForm = [self::FORM_NAME => $faker->sentence()];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testZEROCHECKWithWide()
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');

        $this->arrForm = [self::FORM_NAME => $faker->company];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    protected function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME], [$this->target_func]);
    }
}
