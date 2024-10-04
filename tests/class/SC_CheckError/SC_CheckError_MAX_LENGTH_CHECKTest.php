<?php

class SC_CheckError_MAX_LENGTH_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var int */
    protected $maxlength;

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'MAX_LENGTH_CHECK';
    }

    public function testMAX_LENGTH_CHECK()
    {
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => 'aあaaaa'];
        $this->expected = '※ MAX_LENGTH_CHECKは5字以下で入力してください。<br />';

        $this->scenario();
        $this->verify();
    }


    public function testMAX_LENGTH_CHECKWithNoError()
    {
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => 'aaaあa'];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testMAX_LENGTH_CHECKWithEmpty()
    {
        $this->maxlength = 5;
        $this->arrForm = [self::FORM_NAME => ''];
        $this->expected = '';

        $this->scenario();
        $this->verify();
    }

    public function testMAX_LENGTH_CHECKWithNull()
    {
        $this->maxlength = 5;
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
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME, $this->maxlength], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME, $this->maxlength], [$this->target_func]);
    }
}

