<?php

class SC_CheckError_FULL_EXIST_CHECKTest extends SC_CheckError_AbstractTestCase
{
    /** @var string */
    const FORM_NAME1 = 'year';
    /** @var string */
    const FORM_NAME2 = 'month';
    /** @var string */
    const FORM_NAME3 = 'day';

    protected function setUp(): void
    {
        parent::setUp();
        $this->target_func = 'FULL_EXIST_CHECK';
    }

    public function testFULL_EXIST_CHECK()
    {
        $this->arrForm = [
            self::FORM_NAME1 => 2019,
            self::FORM_NAME2 => '05',
            self::FORM_NAME3 => 'a'
        ];
        $this->expected = [];

        $this->scenario();
        $this->verify();
    }


    public function testFULL_EXIST_CHECKWithEmpty()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '',
            self::FORM_NAME2 => '',
            self::FORM_NAME3 => ''
        ];
        $this->expected = [
            self::FORM_NAME1 => '※ FULL_EXIST_CHECKが入力されていません。<br />'
        ];

        $this->scenario();
        $this->verify();
    }

    public function testFULL_EXIST_CHECKWithNull()
    {
        $this->arrForm = [
            self::FORM_NAME1 => 'a',
            self::FORM_NAME2 => null,
            self::FORM_NAME3 => null
        ];
        $this->expected = [
            self::FORM_NAME1 => '※ FULL_EXIST_CHECKが入力されていません。<br />'
        ];

        $this->scenario();
        $this->verify();
    }

    public function testFULL_EXIST_CHECKWithZero()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '0',
            self::FORM_NAME2 => '0',
            self::FORM_NAME3 => '0'
        ];
        $this->expected = [];

        $this->scenario();
        $this->verify();
    }

    public function testFULL_EXIST_CHECKWithErrorExists()
    {
        $this->arrForm = [
            self::FORM_NAME1 => 'a',
            self::FORM_NAME2 => '',
            self::FORM_NAME3 => ''
        ];
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc(
            ['label', self::FORM_NAME1, self::FORM_NAME2, self::FORM_NAME3],
            [
                'NUM_CHECK',
                $this->target_func
            ]
        );

        $this->expected = [
            self::FORM_NAME1 => '※ labelは数字で入力してください。<br />'
        ];

        $this->verify('既存のエラーがある場合はエラーチェックしない');
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME1, self::FORM_NAME2, self::FORM_NAME3], [$this->target_func]);
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

