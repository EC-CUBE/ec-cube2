<?php

class SC_CheckError_EMAIL_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'EMAIL_CHECK';
        $this->faker = Faker\Factory::create('ja_JP');
    }

    public function testEMAIL_CHECK()
    {
        $this->arrForm = [self::FORM_NAME => $this->faker->safeEmail];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }


    public function testEMAIL_CHECKWithInvalid()
    {
        $this->arrForm = [self::FORM_NAME => 'aaaaa'];
        $this->expected = '※ EMAIL_CHECKの形式が不正です。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testEMAIL_CHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEMAIL_CHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEMAIL_CHECKWithLoose()
    {
        $this->arrForm = [
            self::FORM_NAME => $this->faker->userName.'.@'.$this->faker->safeEmailDomain
        ];
        $this->expected = '';

        $this->scenario();
        $this->verify('@の直前の . を許容する');
    }
    public function testEMAIL_CHECKWithLoose2()
    {
        $this->arrForm = [
            self::FORM_NAME => $this->faker->randomNumber().'..'.$this->faker->userName.'@'.$this->faker->safeEmailDomain
        ];
        $this->expected = '';

        $this->scenario();
        $this->verify('2個以上連続する . を許容する');
    }

    public function testEMAIL_CHECKWithMaxlength()
    {
        $maxlength = 256;
        $domainPart = '@'.$this->faker->safeEmailDomain;
        $localpartLength = $maxlength - strlen($domainPart);
        $localpart = $this->faker->regexify('[a-z]{'.$localpartLength.'}');

        $this->arrForm = [
            self::FORM_NAME => $localpart.$domainPart
        ];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEMAIL_CHECKWithMaxlengthError()
    {
        $maxlength = 257;
        $domainPart = '@'.$this->faker->safeEmailDomain;
        $localpartLength = $maxlength - strlen($domainPart);
        $localpart = $this->faker->regexify('[a-z]{'.$localpartLength.'}');

        $this->arrForm = [
            self::FORM_NAME => $localpart.$domainPart
        ];
        $this->expected = '※ EMAIL_CHECKは256字以下で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }
}
