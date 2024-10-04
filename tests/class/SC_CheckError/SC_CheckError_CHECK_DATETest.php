<?php

class SC_CheckError_CHECK_DATETest extends SC_CheckError_AbstractTestCase
{
    /** @var string */
    const FORM_NAME1 = 'year';
    /** @var string */
    const FORM_NAME2 = 'month';
    /** @var string */
    const FORM_NAME3 = 'day';
    /** @var string */
    protected $year;
    /** @var string */
    protected $month;
    /** @var string */
    protected $day;
    /** @var \DateTime */
    protected $targetDateTime;

    protected function setUp(): void
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');
        parent::setUp();
        $this->target_func = 'CHECK_DATE';
        $this->targetDateTime = $faker->dateTime();
        $this->year = $this->targetDateTime->format('Y');
        $this->month = $this->targetDateTime->format('m');
        $this->day = $this->targetDateTime->format('d');
    }

    public function testCHECK_DATE()
    {
        $this->arrForm = [
            self::FORM_NAME1 => $this->year,
            self::FORM_NAME2 => $this->month,
            self::FORM_NAME3 => $this->day
        ];
        $this->expected = [];

        $this->scenario();
        $this->verify();
    }


    public function testCHECK_DATEWithEmpty()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '2019',
            self::FORM_NAME2 => '',
            self::FORM_NAME3 => ''
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_DATEは全ての項目を入力して下さい。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_DATEWithNull()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '2019',
            self::FORM_NAME2 => null,
            self::FORM_NAME3 => null
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_DATEは全ての項目を入力して下さい。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_DATEWithZero()
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

    public function testCHECK_DATEWithInvalid()
    {
        $this->arrForm = [
            self::FORM_NAME1 => 2001,
            self::FORM_NAME2 => '2',
            self::FORM_NAME3 => '29'
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_DATEが正しくありません。<br />'];

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME1, self::FORM_NAME2, self::FORM_NAME3], [$this->target_func]);
        $this->objErr->doFunc(['dummy', self::FORM_NAME1, self::FORM_NAME2, self::FORM_NAME3], [$this->target_func]);
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

