<?php

class SC_CheckError_PASSWORD_CHAR_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'PASSWORD_CHAR_CHECK';
        $this->faker = Faker\Factory::create('ja_JP');
    }

    public function testPASSWORDCHARCHECK()
    {
        $this->arrForm = [self::FORM_NAME => 'aaaa1111'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testPASSWORDCHARCHECKWithSymbol()
    {
        $this->arrForm = [self::FORM_NAME => 'aaaa.1111'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testPASSWORDCHARCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testPASSWORDCHARCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testPASSWORDCHARCHECKWithFaker()
    {
        $this->arrForm = [
            self::FORM_NAME => $this->faker->password(8, 99).'1',
        ];
        $this->expected = '';
        $this->scenario();
        $this->verify($this->arrForm[self::FORM_NAME].' はパスワードに使用できない文字列です');
    }

    public function testPASSWORDCHARCHECKWithAlphabetOnly()
    {
        $this->arrForm = [
            self::FORM_NAME => 'password',
        ];
        $this->expected = '※ PASSWORD_CHAR_CHECKは英数字をそれぞれ1種類使用し、8文字以上で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testPASSWORDCHARCHECKWithNumberOnly()
    {
        $this->arrForm = [
            self::FORM_NAME => '12345678',
        ];
        $this->expected = '※ PASSWORD_CHAR_CHECKは英数字をそれぞれ1種類使用し、8文字以上で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testPASSWORDCHARCHECKWithMinute()
    {
        $this->arrForm = [
            self::FORM_NAME => $this->faker->password(6, 7),
        ];
        $this->expected = '※ PASSWORD_CHAR_CHECKは英数字をそれぞれ1種類使用し、8文字以上で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}
