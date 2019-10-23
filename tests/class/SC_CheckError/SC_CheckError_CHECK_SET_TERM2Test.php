<?php

class SC_CheckError_CHECK_SET_TERM2Test extends SC_CheckError_AbstractTestCase
{
    /** @var string */
    const FORM_NAME1 = 'start';
    /** @var string */
    const FORM_NAME2 = 'end';
    /** @var string */
    const FORM_NAME3 = 'start_year';
    /** @var string */
    const FORM_NAME4 = 'start_month';
    /** @var string */
    const FORM_NAME5 = 'start_day';
    /** @var string */
    const FORM_NAME6 = 'start_hour';
    /** @var string */
    const FORM_NAME7 = 'start_minute';
    /** @var string */
    const FORM_NAME8 = 'start_second';
    /** @var string */
    const FORM_NAME9 = 'end_year';
    /** @var string */
    const FORM_NAME10 = 'end_month';
    /** @var string */
    const FORM_NAME11 = 'end_day';
    /** @var string */
    const FORM_NAME12 = 'end_hour';
    /** @var string */
    const FORM_NAME13 = 'end_minute';
    /** @var string */
    const FORM_NAME14 = 'end_second';

    /** @var string */
    protected $start_year;
    /** @var string */
    protected $start_month;
    /** @var string */
    protected $start_day;
    /** @var string */
    protected $start_hour;
    /** @var string */
    protected $start_minute;
    /** @var string */
    protected $start_second;

    /** @var string */
    protected $end_year;
    /** @var string */
    protected $end_month;
    /** @var string */
    protected $end_day;
    /** @var string */
    protected $end_hour;
    /** @var string */
    protected $end_minute;
    /** @var string */
    protected $end_second;

    /** @var \DateTime */
    protected $targetDateTime;

    protected function setUp()
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');
        parent::setUp();
        $this->target_func = 'CHECK_SET_TERM2';
        $this->targetDateTime = $faker->dateTime();
        $this->start_year = $this->targetDateTime->format('Y');
        $this->start_month = $this->targetDateTime->format('m');
        $this->start_day = $this->targetDateTime->format('d');
        $this->start_hour = $this->targetDateTime->format('H');
        $this->start_minute = $this->targetDateTime->format('i');
        $this->start_second = $this->targetDateTime->format('s');

        $this->targetDateTime->modify('+1 month');
        $this->end_year = $this->targetDateTime->format('Y');
        $this->end_month = $this->targetDateTime->format('m');
        $this->end_day = $this->targetDateTime->format('d');
        $this->end_hour = $this->targetDateTime->format('H');
        $this->end_minute = $this->targetDateTime->format('i');
        $this->end_second = $this->targetDateTime->format('d');
    }

    public function testCHECK_SET_TERM2()
    {
        $this->arrForm = [
            self::FORM_NAME3 => $this->start_year,
            self::FORM_NAME4 => $this->start_month,
            self::FORM_NAME5 => $this->start_day,
            self::FORM_NAME6 => $this->start_hour,
            self::FORM_NAME7 => $this->start_minute,
            self::FORM_NAME8 => $this->start_second,
            self::FORM_NAME9 => $this->end_year,
            self::FORM_NAME10 => $this->end_month,
            self::FORM_NAME11 => $this->end_day,
            self::FORM_NAME12 => $this->end_hour,
            self::FORM_NAME13 => $this->end_month,
            self::FORM_NAME14 => $this->end_second
        ];
        $this->expected = [];

        $this->scenario();
        $this->verify();
    }


    public function testCHECK_SET_TERM2WithEmpty()
    {
        $this->arrForm = [
            self::FORM_NAME3 => '',
            self::FORM_NAME4 => $this->start_month,
            self::FORM_NAME5 => $this->start_day,
            self::FORM_NAME6 => $this->start_hour,
            self::FORM_NAME7 => $this->start_minute,
            self::FORM_NAME8 => $this->start_second,
            self::FORM_NAME9 => $this->end_year,
            self::FORM_NAME10 => $this->end_month,
            self::FORM_NAME11 => $this->end_day,
            self::FORM_NAME12 => $this->end_hour,
            self::FORM_NAME13 => $this->end_month,
            self::FORM_NAME14 => $this->end_second
        ];
        $this->expected = [self::FORM_NAME3 => '※ startを正しく指定してください。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_SET_TERM2WithNull()
    {
        $this->arrForm = [
            self::FORM_NAME3 => null,
            self::FORM_NAME4 => $this->start_month,
            self::FORM_NAME5 => $this->start_day,
            self::FORM_NAME6 => $this->start_hour,
            self::FORM_NAME7 => $this->start_minute,
            self::FORM_NAME8 => $this->start_second,
            self::FORM_NAME9 => $this->end_year,
            self::FORM_NAME10 => null,
            self::FORM_NAME11 => $this->end_day,
            self::FORM_NAME12 => $this->end_hour,
            self::FORM_NAME13 => $this->end_month,
            self::FORM_NAME14 => $this->end_second
        ];

        $this->expected = [
            self::FORM_NAME3 => '※ startを正しく指定してください。<br />',
            self::FORM_NAME9 => '※ endを正しく指定してください。<br />'
        ];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_SET_TERM2WithZero()
    {
        $this->arrForm = [
            self::FORM_NAME3 => '0',
            self::FORM_NAME4 => '0',
            self::FORM_NAME5 => '0',
            self::FORM_NAME6 => '0',
            self::FORM_NAME7 => '0',
            self::FORM_NAME8 => '0',
            self::FORM_NAME9 => '0',
            self::FORM_NAME10 => '0',
            self::FORM_NAME11 => '0',
            self::FORM_NAME12 => '0',
            self::FORM_NAME13 => '0',
            self::FORM_NAME14 => '0'
        ];

        $this->expected = [
            self::FORM_NAME3 => '※ startとendの期間指定が不正です。<br />',
            self::FORM_NAME9 => '※ endを正しく指定してください。<br />'
        ];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_SET_TERM2WithInvalid()
    {
        $this->arrForm = [
            self::FORM_NAME3 => '2000',
            self::FORM_NAME4 => '2',
            self::FORM_NAME5 => '29',
            self::FORM_NAME6 => 0,
            self::FORM_NAME7 => 0,
            self::FORM_NAME8 => 0,
            self::FORM_NAME9 => '2001',
            self::FORM_NAME10 => '2',
            self::FORM_NAME11 => '29',
            self::FORM_NAME12 => 0,
            self::FORM_NAME13 => 0,
            self::FORM_NAME14 => 0
        ];
        $this->expected = [self::FORM_NAME9 => '※ endを正しく指定してください。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_SET_TERM2WithReverse()
    {
        $this->arrForm = [
            self::FORM_NAME3 => '2001',
            self::FORM_NAME4 => '2',
            self::FORM_NAME5 => '28',
            self::FORM_NAME6 => 0,
            self::FORM_NAME7 => 0,
            self::FORM_NAME8 => 0,
            self::FORM_NAME9 => '2000',
            self::FORM_NAME10 => '2',
            self::FORM_NAME11 => '29',
            self::FORM_NAME12 => 0,
            self::FORM_NAME13 => 0,
            self::FORM_NAME14 => 0
        ];

        $this->expected = [self::FORM_NAME3 => '※ startとendの期間指定が不正です。<br />'];

        $this->scenario();
        $this->verify();
    }

    /**
     * {@inheritdoc}
     */
    public function scenario()
    {
        $this->objErr = new SC_CheckError_Ex($this->arrForm);
        $this->objErr->doFunc(
            [
                self::FORM_NAME1,
                self::FORM_NAME2,
                self::FORM_NAME3,
                self::FORM_NAME4,
                self::FORM_NAME5,
                self::FORM_NAME6,
                self::FORM_NAME7,
                self::FORM_NAME8,
                self::FORM_NAME9,
                self::FORM_NAME10,
                self::FORM_NAME11,
                self::FORM_NAME12,
                self::FORM_NAME13,
                self::FORM_NAME14
            ],
            [$this->target_func]
        );
        $this->objErr->doFunc(
            [
                'dummy1',
                'dummy2',
                self::FORM_NAME3,
                self::FORM_NAME4,
                self::FORM_NAME5,
                self::FORM_NAME6,
                self::FORM_NAME7,
                self::FORM_NAME8,
                self::FORM_NAME9,
                self::FORM_NAME10,
                self::FORM_NAME11,
                self::FORM_NAME12,
                self::FORM_NAME13,
                self::FORM_NAME14,
            ],
            [$this->target_func]
        );
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
