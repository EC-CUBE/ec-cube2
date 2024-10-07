<?php

class SC_CheckError_MIN_LENGTH_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var int */
    protected $minlength;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'MIN_LENGTH_CHECK';
    }

    public function testMINLENGTHCHECK()
    {
        $this->minlength = 5;
        $this->arrForm = [self::FORM_NAME => 'aあaa'];
        $this->expected = '※ MIN_LENGTH_CHECKは5字以上で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testMINLENGTHCHECKWithNoError()
    {
        $this->minlength = 5;
        $this->arrForm = [self::FORM_NAME => 'aaaあaa'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testMINLENGTHCHECKWithEmpty()
    {
        $this->minlength = 5;
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '※ MIN_LENGTH_CHECKは5字以上で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testMINLENGTHCHECKWithNull()
    {
        $this->minlength = 5;
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '※ MIN_LENGTH_CHECKは5字以上で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME, $this->minlength], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME, $this->minlength], [$this->target_func]);
    }
}
