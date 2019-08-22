<?php

class SC_CheckError_EMAIL_CHAR_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'EMAIL_CHAR_CHECK';
        $this->faker = Faker\Factory::create('ja_JP');
    }

    public function testEMAIL_CHAR_CHECK()
    {
        $this->arrForm = [self::FORM_NAME => $this->faker->safeEmail];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEMAIL_CHAR_CHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEMAIL_CHAR_CHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testEMAIL_CHAR_CHECKWithRegex()
    {
        $this->arrForm = [
            self::FORM_NAME => $this->faker->regexify('^[a-zA-Z0-9_\.@\+\?-]+$')
        ];
        $this->expected = '';
        $this->scenario();
        $this->verify('');
    }

    public function testEMAIL_CHAR_CHECKWithError()
    {
        $email = $this->faker->randomNumber().'='.$this->faker->userName.'@'.$this->faker->safeEmailDomain;
        $this->arrForm = [
            self::FORM_NAME => $email
        ];
        $this->expected = '※ EMAIL_CHAR_CHECKに使用する文字を正しく入力してください。<br />';

        $this->scenario();
        $this->verify($email.' は使用できないパターンが含まれているはず');
    }
}
