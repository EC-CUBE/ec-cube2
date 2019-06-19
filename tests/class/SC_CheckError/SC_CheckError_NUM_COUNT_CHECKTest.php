<?php

class SC_CheckError_NUM_COUNT_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var int */
    protected $length;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'NUM_COUNT_CHECK';
    }

    public function testNUM_COUNT_CHECK()
    {
        $this->length = 5;
        $this->arrForm = [self::FORM_NAME => 123456];
        $this->expected = '※ NUM_COUNT_CHECKは5桁で入力して下さい。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_COUNT_CHECKWithMin()
    {
        $this->length = 5;
        $this->arrForm = [self::FORM_NAME => 3456];
        $this->expected = '※ NUM_COUNT_CHECKは5桁で入力して下さい。<br />';

        $this->scenario();
        $this->verify();
    }


    public function testNUM_COUNT_CHECKWithNoError()
    {
        $this->length = 5;
        $this->arrForm = [self::FORM_NAME => 12345];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_COUNT_CHECKWithEmpty()
    {
        $this->length = 5;
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_COUNT_CHECKWithNull()
    {
        $this->length = 5;
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_COUNT_CHECKWithString()
    {
        $this->length = 5;
        $this->arrForm = [self::FORM_NAME => '02345'];
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
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME, $this->length], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME, $this->length], [$this->target_func]);
    }
}

