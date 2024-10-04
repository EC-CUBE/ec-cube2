<?php

use Faker\Provider\DateTime;


class SC_CheckError_CHECK_BIRTHDAYTest extends SC_CheckError_AbstractTestCase
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
        $this->target_func = 'CHECK_BIRTHDAY';
        $this->targetDateTime = $faker->dateTime();
        $this->year = $this->targetDateTime->format('Y');
        $this->month = $this->targetDateTime->format('m');
        $this->day = $this->targetDateTime->format('d');
    }

    public function testCHECK_BIRTHDAY()
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


    public function testCHECK_BIRTHDAYWithEmpty()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '2019',
            self::FORM_NAME2 => '',
            self::FORM_NAME3 => ''
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_BIRTHDAYは全ての項目を入力して下さい。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_BIRTHDAYWithNull()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '2019',
            self::FORM_NAME2 => null,
            self::FORM_NAME3 => null
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_BIRTHDAYは全ての項目を入力して下さい。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_BIRTHDAYWithZero()
    {
        $this->arrForm = [
            self::FORM_NAME1 => '0',
            self::FORM_NAME2 => '0',
            self::FORM_NAME3 => '0'
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_BIRTHDAY(年)は1901以上で入力してください。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_BIRTHDAYWithInvalid()
    {
        $this->arrForm = [
            self::FORM_NAME1 => 2001,
            self::FORM_NAME2 => '2',
            self::FORM_NAME3 => '29'
        ];
        $this->expected = [self::FORM_NAME1 => '※ CHECK_BIRTHDAYが正しくありません。<br />'];

        $this->scenario();
        $this->verify();
    }

    public function testCHECK_BIRTHDAYWithMaxYear()
    {
        $now = new \DateTime();
        $now->modify('+1 year');
        $this->arrForm = [
            self::FORM_NAME1 => $now->format('Y'),
            self::FORM_NAME2 => $this->month,
            self::FORM_NAME3 => $this->day
        ];
        $this->scenario();
        $this->actual = $this->objErr->arrErr[self::FORM_NAME1];
        $this->assertStringContainsString('以下で入力', $this->actual);
    }

    public function testCHECK_BIRTHDAYWithMinYear()
    {
        $this->arrForm = [
            self::FORM_NAME1 => BIRTH_YEAR - 1,
            self::FORM_NAME2 => $this->month,
            self::FORM_NAME3 => $this->day
        ];
        $this->scenario();
        $this->actual = $this->objErr->arrErr[self::FORM_NAME1];
        $this->assertStringContainsString('以上で入力', $this->actual);
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
