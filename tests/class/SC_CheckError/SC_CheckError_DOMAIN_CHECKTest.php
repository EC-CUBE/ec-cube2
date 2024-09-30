<?php

class SC_CheckError_DOMAIN_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'DOMAIN_CHECK';
        $this->faker = Faker\Factory::create('ja_JP');
    }

    public function testDOMAINCHECK()
    {
        $this->arrForm = [
            self::FORM_NAME => '.'.$this->faker->domainName // 行頭に . を含める必要がある
        ];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testDOMAINCHECKWithInvalid()
    {
        $this->arrForm = [self::FORM_NAME => 'aaaaa'];
        $this->expected = '※ DOMAIN_CHECKの形式が不正です。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testDOMAINCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testDOMAINCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }
}
