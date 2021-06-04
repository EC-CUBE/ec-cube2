<?php

class SC_CheckError_NUM_RANGE_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var int */
    protected $minlength;

    /** @var int */
    protected $maxlength;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'NUM_RANGE_CHECK';
    }

    public function testNUM_RANGE_CHECK()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => 33344];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_RANGE_CHECKWithMin()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => 333];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }


    public function testNUM_RANGE_CHECKWithLess()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => 22];
        $this->expected = '※ NUM_RANGE_CHECKは3桁～5桁で入力して下さい。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_RANGE_CHECKWithGreater()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => 223425];
        $this->expected = '※ NUM_RANGE_CHECKは3桁～5桁で入力して下さい。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_RANGE_CHECKWithEmpty()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_RANGE_CHECKWithNull()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_RANGE_CHECKWithZero()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => 0];
        $this->expected = '※ NUM_RANGE_CHECKは3桁～5桁で入力して下さい。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testNUM_RANGE_CHECKWithString()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => '123'];
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
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME, $this->minlength, $this->maxlength], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME, $this->minlength, $this->maxlength], [$this->target_func]);
    }
}

