<?php

class SC_CheckError_MIN_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var int */
    protected $min;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'MIN_CHECK';
    }

    public function testMIN_CHECK()
    {
        $this->min = 5;
        $this->arrForm = [self::FORM_NAME => 4];
        $this->expected = '※ MIN_CHECKは5以上で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }


    public function testMIN_CHECKWithNoError()
    {
        $this->min = 5;
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testMIN_CHECKWithEmpty()
    {
        $this->min = 5;
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '※ MIN_CHECKは5以上で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testMIN_CHECKWithNull()
    {
        $this->min = 5;
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '※ MIN_CHECKは5以上で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testMIN_CHECKWithNotType()
    {
        $this->min = 5;
        $this->arrForm = [self::FORM_NAME => '5'];
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
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME, $this->min], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME, $this->min], [$this->target_func]);
    }
}

