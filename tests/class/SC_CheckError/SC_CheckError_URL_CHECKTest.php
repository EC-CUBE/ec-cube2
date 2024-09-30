<?php

class SC_CheckError_URL_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'URL_CHECK';
        $this->faker = Faker\Factory::create('ja_JP');
    }

    public function testURLCHECK()
    {
        $this->arrForm = [self::FORM_NAME => $this->faker->url];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testURLCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testURLCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testURLCHECKWithError()
    {
        $this->arrForm = [
            self::FORM_NAME => 'ftp://'.$this->faker->safeEmailDomain.'/'
        ];
        $this->expected = '※ URL_CHECKを正しく入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}
