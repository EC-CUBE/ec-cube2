<?php

class SC_CheckError_CHECK_DATE3Test extends SC_CheckError_AbstractTestCase
{
    /** @var string */
    const FORM_NAME1 = 'year';
    /** @var string */
    const FORM_NAME2 = 'month';
    /** @var string */
    protected $year;
    /** @var string */
    protected $month;
    /** @var \DateTime */
    protected $targetDateTime;

    protected function setUp()
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');
        parent::setUp();
        $this->target_func = 'CHECK_DATE3';
        $this->targetDateTime = $faker->dateTime();
        $this->year = $this->targetDateTime->format('Y');
        $this->month = $this->targetDateTime->format('m');
    }

    public function testCHECK_DATE3()
    {
        $this->arrForm = [
            self::FORM_NAME1 => $this->year,
            self::FORM_NAME2 => $this->month
        ];
        $this->expected = [];

        $this->scenario();
        $this->verify();
    }


    public function testCHECK_DATE3WithEmpty()
    {
        $this->arrForm = [
            self::FORM_NAME1 => $this->year,
            self::FORM_NAME2 => ''
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_DATE3は全ての項目を入力して下さい。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_DATE3WithNull()
    {
        $this->arrForm = [
            self::FORM_NAME1 => null,
            self::FORM_NAME2 => $this->month
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_DATE3は全ての項目を入力して下さい。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_DATE3WithZero()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '0',
            self::FORM_NAME2 => '0'
        ];
        $this->expected = [];

        $this->scenario();
        $this->verify('0 の入力は無視される');
    }

    public function testCHECK_DATE3WithInvalid()
    {
        $this->arrForm = [
            self::FORM_NAME1 => 2001,
            self::FORM_NAME2 => '13'
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_DATE3が正しくありません。<br />'];

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME1, self::FORM_NAME2], [$this->target_func]);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME1, self::FORM_NAME2], [$this->target_func]);
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

