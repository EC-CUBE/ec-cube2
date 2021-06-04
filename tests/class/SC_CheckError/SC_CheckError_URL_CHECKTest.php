<?php

class SC_CheckError_URL_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'URL_CHECK';
        $this->faker = Faker\Factory::create('ja_JP');
    }

    public function testURL_CHECK()
    {
        $this->arrForm = [self::FORM_NAME => $this->faker->url];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testURL_CHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testURL_CHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testURL_CHECKWithError()
    {
        $this->arrForm = [
            self::FORM_NAME => 'ftp://'.$this->faker->safeEmailDomain.'/'
        ];
        $this->expected = '※ URL_CHECKを正しく入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}
