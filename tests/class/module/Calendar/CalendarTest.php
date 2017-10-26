<?php
$HOME = realpath(dirname(__FILE__)) . "/../../../..";
require_once($HOME . "/tests/class/Common_TestCase.php");

/**
 * Calendar_Test
 *
 * @see month_weekdays_test.php
 * @copyright
 * @author Nobuhiko Kimoto <info@nob-log.info>
 * @license
 */
class Calendar_Test extends Common_TestCase
{
    public function TestOfMonthWeekdays()
    {
        $this->UnitTestCase('Test of Month Weekdays');
    }
    public function setUp()
    {
        $this->cal = new Calendar_Month_Weekdays(2003,10);
    }
    public function tearDown()
    {
    }
    public function testPrevDay()
    {
        $this->assertSame(30,$this->cal->prevDay());
    }
    public function testPrevDay_Array()
    {
        $this->assertSame(
            array(
                'year'   => 2003,
                'month'  => 9,
                'day'    => 30,
                'hour'   => 0,
                'minute' => 0,
                'second' => 0),
            $this->cal->prevDay('array'));
    }
    public function testThisDay()
    {
        $this->assertSame(1,$this->cal->thisDay());
    }
    public function testNextDay()
    {
        $this->assertSame(2,$this->cal->nextDay());
    }
    public function testPrevHour()
    {
        $this->assertSame(23,$this->cal->prevHour());
    }
    public function testThisHour()
    {
        $this->assertSame(0,$this->cal->thisHour());
    }
    public function testNextHour()
    {
        $this->assertSame(1,$this->cal->nextHour());
    }
    public function testPrevMinute()
    {
        $this->assertSame(59,$this->cal->prevMinute());
    }
    public function testThisMinute()
    {
        $this->assertSame(0,$this->cal->thisMinute());
    }
    public function testNextMinute()
    {
        $this->assertSame(1,$this->cal->nextMinute());
    }
    public function testPrevSecond()
    {
        $this->assertSame(59,$this->cal->prevSecond());
    }
    public function testThisSecond()
    {
        $this->assertSame(0,$this->cal->thisSecond());
    }
    public function testNextSecond()
    {
        $this->assertSame(1,$this->cal->nextSecond());
    }
    public function testGetTimeStamp()
    {
        $stamp = mktime(0,0,0,10,1,2003);
        $this->assertSame($stamp,$this->cal->getTimeStamp());
    }
}
