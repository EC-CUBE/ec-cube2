<?php

class SC_CheckError_TEL_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var string */
    const FORM_NAME1 = 'tel01';
    /** @var string */
    const FORM_NAME2 = 'tel02';
    /** @var string */
    const FORM_NAME3 = 'tel03';

    /** @var int */
    protected $tel_item_length = 6;
    /** @var int */
    protected $tel_length = 12;

    protected function setUp()
    {
        parent::setUp();
        $this->target_func = 'TEL_CHECK';
    }

    public function testTEL_CHECK()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '056375',
            self::FORM_NAME2 => '1',
            self::FORM_NAME3 => '2222'
        ];
        $this->expected = [];

        $this->scenario();
        $this->verify();
    }


    public function testTEL_CHECKWithNumber()
    {
        $this->arrForm = [
            self::FORM_NAME1 => 5637,
            self::FORM_NAME2 => 1111,
            self::FORM_NAME3 => 2222
        ];
        $this->expected = [];

        $this->scenario();
        $this->verify();
    }

    public function testTEL_CHECKWithEmpty()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '',
            self::FORM_NAME2 => '',
            self::FORM_NAME3 => ''
        ];
        $this->expected = [];

        $this->scenario();
        $this->verify();
    }

    public function testTEL_CHECKWithNull()
    {
        $this->arrForm = [
            self::FORM_NAME1 => null,
            self::FORM_NAME2 => null,
            self::FORM_NAME3 => null
        ];
        $this->expected = [];

        $this->scenario();
        $this->verify();
    }

    public function testTEL_CHECKWithLastEmpty()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '090',
            self::FORM_NAME2 => 1111,
            self::FORM_NAME3 => ''
        ];
        $this->expected = [self::FORM_NAME1 => '※ TEL_CHECKは全ての項目を入力してください。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testTEL_CHECKWithAlpha()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '111',
            self::FORM_NAME2 => 'aaa',
            self::FORM_NAME3 => 11
        ];
        $this->expected = [self::FORM_NAME2 => '※ TEL_CHECK2は数字で入力してください。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testTEL_CHECKWithMaxlength()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '1111',
            self::FORM_NAME2 => '22222',
            self::FORM_NAME3 => '3333'
        ];
        $this->expected = [self::FORM_NAME3 => '※ TEL_CHECKは12文字以内で入力してください。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testTEL_CHECKWithItemMaxlength()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '1234567',
            self::FORM_NAME2 => '7',
            self::FORM_NAME3 => '333'
        ];
        $this->expected = [self::FORM_NAME1 => '※ TEL_CHECK1は6字以内で入力してください。<br />'];

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME1, self::FORM_NAME2, self::FORM_NAME3, $this->tel_item_length, $this->tel_length], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME1, self::FORM_NAME2, self::FORM_NAME3, $this->tel_item_length, $this->tel_length], [$this->target_func]);
    }

    /**
     * {@inheritdoc}
     */
    protected function verify($message = '')
    {
        $this->actual = $this->objErr->arrErr;

        $this->assertEquals($this->expected, $this->actual, $message);
    }
}

