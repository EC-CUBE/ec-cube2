<?php

class SC_CheckError_PREF_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;
    /** @var int */
    protected $pref_id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'PREF_CHECK';
        $this->faker = Faker\Factory::create('ja_JP');
        $masterData = new SC_DB_MasterData_Ex();
        $arrPref = $masterData->getMasterData('mtb_pref');
        $this->pref_id = $this->faker->numberBetween(1, count($arrPref));
    }

    public function testPREFCHECK()
    {
        $this->arrForm = [self::FORM_NAME => $this->pref_id];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testPREFCHECKWithInvalid()
    {
        $this->arrForm = [self::FORM_NAME => 0];
        $this->expected = '※ PREF_CHECKが不正な値です。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testPREFCHECKWithOver()
    {
        $this->arrForm = [self::FORM_NAME => 48];
        $this->expected = '※ PREF_CHECKが不正な値です。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testPREFCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = null;

        $this->scenario();
        $this->verify();
    }

    public function testPREFCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = null;

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
