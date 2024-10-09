<?php

class SC_CheckError_MOBILE_EMAIL_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;
    /** @var string */
    protected $mobileEmail;

    protected function setUp(): void
    {
        $this->markTestSkipped('モバイルメールアドレスは使用されていないためスキップします。');
        parent::setUp();
        $this->target_func = 'MOBILE_EMAIL_CHECK';
        $this->faker = Faker\Factory::create('ja_JP');
        $masterData = new SC_DB_MasterData_Ex();
        $arrMobileDomains = $masterData->getMasterData('mtb_mobile_domain');
        $this->mobileEmail = $this->faker->userName.'@'.$arrMobileDomains[$this->faker->numberBetween(1, count($arrMobileDomains))];
    }

    public function testMOBILEEMAILCHECK()
    {
        $this->arrForm = [self::FORM_NAME => $this->mobileEmail];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testMOBILEEMAILCHECKWithInvalid()
    {
        $this->arrForm = [self::FORM_NAME => $this->faker->safeEmail];
        $this->expected = '※ MOBILE_EMAIL_CHECKは携帯電話のものではありません。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testMOBILEEMAILCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testMOBILEEMAILCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }
}
