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

    public function testNUMRANGECHECK()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => 33344];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMRANGECHECKWithMin()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => 333];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMRANGECHECKWithLess()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => 22];
        $this->expected = '※ NUM_RANGE_CHECKは3桁～5桁で入力して下さい。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testNUMRANGECHECKWithGreater()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => 223425];
        $this->expected = '※ NUM_RANGE_CHECKは3桁～5桁で入力して下さい。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testNUMRANGECHECKWithEmpty()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMRANGECHECKWithNull()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testNUMRANGECHECKWithZero()
    {
        $this->minlength = 3;
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => 0];
        $this->expected = '※ NUM_RANGE_CHECKは3桁～5桁で入力して下さい。<br />';

        $this->scenario();
        $this->verify();
    }

    public function testNUMRANGECHECKWithString()
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
