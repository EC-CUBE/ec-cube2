<?php

class SC_CheckError_MOBILE_EMAIL_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;
    /** @var string */
    protected $mobileEmail;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'MOBILE_EMAIL_CHECK';
        $this->faker = Faker\Factory::create('ja_JP');
        $masterData = new SC_DB_MasterData_Ex();
        $arrMobileDomains = $masterData->getMasterData('mtb_mobile_domain');
        $this->mobileEmail = $this->faker->userName.'@'.$arrMobileDomains[$this->faker->numberBetween(0, count($arrMobileDomains) - 1)];
    }

    public function testMOBILE_EMAIL_CHECK()
    {
        $this->arrForm = [self::FORM_NAME => $this->mobileEmail];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }


    public function testMOBILE_EMAIL_CHECKWithInvalid()
    {
        $this->arrForm = [self::FORM_NAME => $this->faker->safeEmail];
        $this->expected = '※ MOBILE_EMAIL_CHECKは携帯電話のものではありません。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testMOBILE_EMAIL_CHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testMOBILE_EMAIL_CHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }
}
