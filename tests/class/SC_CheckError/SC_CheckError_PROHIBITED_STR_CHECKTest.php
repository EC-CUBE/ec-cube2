<?php

class SC_CheckError_PROHIBITED_STR_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var Faker\Generator */
    protected $faker;

    /** @var array */
    protected $denyStrings;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'PROHIBITED_STR_CHECK';
        $this->faker = Faker\Factory::create('ja_JP');
        $this->denyStrings = ['aaa', 'bbb', 'ccc'];
    }

    public function testPROHIBITEDSTRCHECK()
    {
        $this->arrForm = [
            self::FORM_NAME => 'ddd'
        ];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testPROHIBITEDSTRCHECKWithInvalid()
    {
        $this->arrForm = [self::FORM_NAME => 'aaaaa'];
        $this->expected = '※ PROHIBITED_STR_CHECKは入力できません。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testPROHIBITEDSTRCHECKWithEmpty()
    {
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testPROHIBITEDSTRCHECKWithNull()
    {
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME, $this->denyStrings], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME, $this->denyStrings], [$this->target_func]);
    }
}
