<?php

class SC_CheckError_MAX_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var int */
    protected $max;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'MAX_CHECK';
    }

    public function testMAX_CHECK()
    {
        $this->max = 5;
        $this->arrForm = [self::FORM_NAME => 6];
        $this->expected = '※ MAX_CHECKは5以下で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }


    public function testMAX_CHECKWithNoError()
    {
        $this->max = 5;
        $this->arrForm = [self::FORM_NAME => 5];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testMAX_CHECKWithEmpty()
    {
        $this->max = 5;
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testMAX_CHECKWithNull()
    {
        $this->max = 5;
        $this->arrForm = [self::FORM_NAME => null];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testMAX_CHECKWithNotType()
    {
        $this->max = 5;
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
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME, $this->max], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME, $this->max], [$this->target_func]);
    }
}

