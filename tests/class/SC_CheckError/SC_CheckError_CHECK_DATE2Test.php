<?php

class SC_CheckError_CHECK_DATE2Test extends SC_CheckError_AbstractTestCase
{
    /** @var string */
    const FORM_NAME1 = 'year';
    /** @var string */
    const FORM_NAME2 = 'month';
    /** @var string */
    const FORM_NAME3 = 'day';
    /** @var string */
    const FORM_NAME4 = 'hour';
    /** @var string */
    const FORM_NAME5 = 'minute';
    /** @var string */
    const FORM_NAME6 = 'second';
    /** @var string */
    protected $year;
    /** @var string */
    protected $month;
    /** @var string */
    protected $day;
    /** @var string */
    protected $hour;
    /** @var string */
    protected $minute;
    /** @var string */
    protected $second;
    /** @var \DateTime */
    protected $targetDateTime;

    protected function setUp(): void
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');
        parent::setUp();
        $this->target_func = 'CHECK_DATE2';
        $this->targetDateTime = $faker->dateTime();
        $this->year = $this->targetDateTime->format('Y');
        $this->month = $this->targetDateTime->format('m');
        $this->day = $this->targetDateTime->format('d');
        $this->hour = $this->targetDateTime->format('H');
        $this->minute = $this->targetDateTime->format('i');
        $this->second = $this->targetDateTime->format('s');
    }

    public function testCHECK_DATE2()
    {
        $this->arrForm = [
            self::FORM_NAME1 => $this->year,
            self::FORM_NAME2 => $this->month,
            self::FORM_NAME3 => $this->day,
            self::FORM_NAME4 => $this->hour,
            self::FORM_NAME5 => $this->minute,
            self::FORM_NAME6 => $this->second
        ];
        $this->expected = [];

        $this->scenario();
        $this->verify();
    }


    public function testCHECK_DATE2WithEmpty()
    {
        $this->arrForm = [
            self::FORM_NAME1 => $this->year,
            self::FORM_NAME2 => '',
            self::FORM_NAME3 => $this->day,
            self::FORM_NAME4 => $this->hour,
            self::FORM_NAME5 => $this->minute,
            self::FORM_NAME6 => $this->second
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_DATE2は全ての項目を入力して下さい。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_DATE2WithNull()
    {
        $this->arrForm = [
            self::FORM_NAME1 => $this->year,
            self::FORM_NAME2 => $this->month,
            self::FORM_NAME3 => $this->day,
            self::FORM_NAME4 => $this->hour,
            self::FORM_NAME5 => null,
            self::FORM_NAME6 => $this->second
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_DATE2は全ての項目を入力して下さい。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_DATE2WithZero()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '0',
            self::FORM_NAME2 => '0',
            self::FORM_NAME3 => '0',
            self::FORM_NAME4 => '0',
            self::FORM_NAME5 => '0',
            self::FORM_NAME6 => '0'
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_DATE2が正しくありません。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_DATE2WithInvalid()
    {
        $this->arrForm = [
            self::FORM_NAME1 => 2001,
            self::FORM_NAME2 => '2',
            self::FORM_NAME3 => '29',
            self::FORM_NAME4 => '0',
            self::FORM_NAME5 => '0',
            self::FORM_NAME6 => '0'
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_DATE2が正しくありません。<br />'];

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME1, self::FORM_NAME2, self::FORM_NAME3, self::FORM_NAME4, self::FORM_NAME5, self::FORM_NAME6], [$this->target_func]);
        $this->objErr->doFunc([$this->target_func, self::FORM_NAME1, self::FORM_NAME2, self::FORM_NAME3, self::FORM_NAME4, self::FORM_NAME5, self::FORM_NAME6], [$this->target_func]);
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

