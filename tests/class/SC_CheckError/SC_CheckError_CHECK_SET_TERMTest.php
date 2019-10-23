<?php

class SC_CheckError_CHECK_SET_TERMTest extends SC_CheckError_AbstractTestCase
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
    const FORM_NAME6 = 'end_year';
    /** @var string */
    const FORM_NAME7 = 'end_month';
    /** @var string */
    const FORM_NAME8 = 'end_day';

    /** @var string */
    protected $start_year;
    /** @var string */
    protected $start_month;
    /** @var string */
    protected $start_day;
    /** @var string */
    protected $end_year;
    /** @var string */
    protected $end_month;
    /** @var string */
    protected $end_day;
    /** @var \DateTime */
    protected $targetDateTime;

    protected function setUp()
    {
        /** @var Faker\Generator $faker */
        $faker = Faker\Factory::create('ja_JP');
        parent::setUp();
        $this->target_func = 'CHECK_SET_TERM';
        $this->targetDateTime = $faker->dateTime();
        $this->start_year = $this->targetDateTime->format('Y');
        $this->start_month = $this->targetDateTime->format('m');
        $this->start_day = $this->targetDateTime->format('d');

        $this->targetDateTime->modify('+1 month');
        $this->end_year = $this->targetDateTime->format('Y');
        $this->end_month = $this->targetDateTime->format('m');
        $this->end_day = $this->targetDateTime->format('d');
    }

    public function testCHECK_SET_TERM()
    {
        $this->arrForm = [
            self::FORM_NAME3 => $this->start_year,
            self::FORM_NAME4 => $this->start_month,
            self::FORM_NAME5 => $this->start_day,
            self::FORM_NAME6 => $this->end_year,
            self::FORM_NAME7 => $this->end_month,
            self::FORM_NAME8 => $this->end_day
        ];
        $this->expected = [];

        $this->scenario();
        $this->verify();
    }


    public function testCHECK_SET_TERMWithEmpty()
    {
        $this->arrForm = [
            self::FORM_NAME3 => $this->start_year,
            self::FORM_NAME4 => $this->start_month,
            self::FORM_NAME5 => $this->start_day,
            self::FORM_NAME6 => '',
            self::FORM_NAME7 => $this->end_month,
            self::FORM_NAME8 => $this->end_day
        ];
        $this->expected = [self::FORM_NAME6 => '※ endを正しく指定してください。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_SET_TERMWithNull()
    {
        $this->arrForm = [
            self::FORM_NAME3 => '2019',
            self::FORM_NAME4 => null,
            self::FORM_NAME5 => null,
            self::FORM_NAME6 => '2019',
            self::FORM_NAME7 => null,
            self::FORM_NAME8 => null
        ];

        $this->expected = [
            self::FORM_NAME3 => '※ startを正しく指定してください。<br />',
            self::FORM_NAME6 => '※ endを正しく指定してください。<br />'
        ];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_SET_TERMWithZero()
    {
        $this->arrForm = [
            self::FORM_NAME3 => '0',
            self::FORM_NAME4 => '0',
            self::FORM_NAME5 => '0',
            self::FORM_NAME6 => '0',
            self::FORM_NAME7 => '0',
            self::FORM_NAME8 => '0'
        ];

        $this->expected = [
            self::FORM_NAME3 => '※ startを正しく指定してください。<br />',
            self::FORM_NAME6 => '※ endを正しく指定してください。<br />'
        ];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_SET_TERMWithInvalid()
    {
        $this->arrForm = [
            self::FORM_NAME3 => '2000',
            self::FORM_NAME4 => '2',
            self::FORM_NAME5 => '29',
            self::FORM_NAME6 => '2001',
            self::FORM_NAME7 => '2',
            self::FORM_NAME8 => '29'
        ];

        $this->expected = [self::FORM_NAME6 => '※ endを正しく指定してください。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_SET_TERMWithReverse()
    {
        $this->arrForm = [
            self::FORM_NAME3 => '2001',
            self::FORM_NAME4 => '2',
            self::FORM_NAME5 => '28',
            self::FORM_NAME6 => '2000',
            self::FORM_NAME7 => '2',
            self::FORM_NAME8 => '28'
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
                self::FORM_NAME8
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
                self::FORM_NAME8
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
