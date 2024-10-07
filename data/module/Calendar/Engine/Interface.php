<?php

/* vim: set expandtab tabstop=4 shiftwidth=4: */
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Harry Fuecks <hfuecks@phppatterns.com>                      |
// +----------------------------------------------------------------------+
//
// $Id: Interface.php,v 1.5 2004/08/16 12:29:18 hfuecks Exp $
//
/**
 * @version $Id$
 */
/**
 * The methods the classes implementing the Calendar_Engine must implement.
 * Note this class is not used but simply to help development
 */
class Calendar_Engine_Interface
{
    /**
     * Provides a mechansim to make sure parsing of timestamps
     * into human dates is only performed once per timestamp.
     * Typically called "internally" by methods like stampToYear.
     * Return value can vary, depending on the specific implementation
     *
     * @param int timestamp (depending on implementation)
     *
     * @return mixed
     */
    public function stampCollection($stamp)
    {
        return 0;
    }

    /**
     * Returns a numeric year given a timestamp
     *
     * @param int timestamp (depending on implementation)
     *
     * @return int year (e.g. 2003)
     */
    public function stampToYear($stamp)
    {
        return 0;
    }

    /**
     * Returns a numeric month given a timestamp
     *
     * @param int timestamp (depending on implementation)
     *
     * @return int month (e.g. 9)
     */
    public function stampToMonth($stamp)
    {
        return 0;
    }

    /**
     * Returns a numeric day given a timestamp
     *
     * @param int timestamp (depending on implementation)
     *
     * @return int day (e.g. 15)
     */
    public function stampToDay($stamp)
    {
        return 0;
    }

    /**
     * Returns a numeric hour given a timestamp
     *
     * @param int timestamp (depending on implementation)
     *
     * @return int hour (e.g. 13)
     */
    public function stampToHour($stamp)
    {
        return 0;
    }

    /**
     * Returns a numeric minute given a timestamp
     *
     * @param int timestamp (depending on implementation)
     *
     * @return int minute (e.g. 34)
     */
    public function stampToMinute($stamp)
    {
        return 0;
    }

    /**
     * Returns a numeric second given a timestamp
     *
     * @param int timestamp (depending on implementation)
     *
     * @return int second (e.g. 51)
     */
    public function stampToSecond($stamp)
    {
        return 0;
    }

    /**
     * Returns a timestamp. Can be worth "caching" generated
     * timestamps in a static variable, identified by the
     * params this method accepts, to timestamp will only
     * be calculated once.
     *
     * @param int year (e.g. 2003)
     * @param int month (e.g. 9)
     * @param int day (e.g. 13)
     * @param int hour (e.g. 13)
     * @param int minute (e.g. 34)
     * @param int second (e.g. 53)
     *
     * @return int (depends on implementation)
     */
    public function dateToStamp($y, $m, $d, $h, $i, $s)
    {
        return 0;
    }

    /**
     * The upper limit on years that the Calendar Engine can work with
     *
     * @return int (e.g. 2037)
     */
    public function getMaxYears()
    {
        return 0;
    }

    /**
     * The lower limit on years that the Calendar Engine can work with
     *
     * @return int (e.g 1902)
     */
    public function getMinYears()
    {
        return 0;
    }

    /**
     * Returns the number of months in a year
     *
     * @param int (optional) year to get months for
     *
     * @return int (e.g. 12)
     */
    public function getMonthsInYear($y = null)
    {
        return 0;
    }

    /**
     * Returns the number of days in a month, given year and month
     *
     * @param int year (e.g. 2003)
     * @param int month (e.g. 9)
     *
     * @return int days in month
     */
    public function getDaysInMonth($y, $m)
    {
        return 0;
    }

    /**
     * Returns numeric representation of the day of the week in a month,
     * given year and month
     *
     * @param int year (e.g. 2003)
     * @param int month (e.g. 9)
     *
     * @return int
     */
    public function getFirstDayInMonth($y, $m)
    {
        return 0;
    }

    /**
     * Returns the number of days in a week
     *
     * @param int year (2003)
     * @param int month (9)
     * @param int day (4)
     *
     * @return int (e.g. 7)
     */
    public function getDaysInWeek($y = null, $m = null, $d = null)
    {
        return 0;
    }

    /**
     * Returns the number of the week in the year (ISO-8601), given a date
     *
     * @param int year (2003)
     * @param int month (9)
     * @param int day (4)
     *
     * @return int week number
     */
    public function getWeekNInYear($y, $m, $d)
    {
        return 0;
    }

    /**
     * Returns the number of the week in the month, given a date
     *
     * @param int year (2003)
     * @param int month (9)
     * @param int day (4)
     * @param int first day of the week (default: 1 - monday)
     *
     * @return int week number
     */
    public function getWeekNInMonth($y, $m, $d, $firstDay = 1)
    {
        return 0;
    }

    /**
     * Returns the number of weeks in the month
     *
     * @param int year (2003)
     * @param int month (9)
     * @param int first day of the week (default: 1 - monday)
     *
     * @return int weeks number
     */
    public function getWeeksInMonth($y, $m)
    {
        return 0;
    }

    /**
     * Returns the number of the day of the week (0=sunday, 1=monday...)
     *
     * @param int year (2003)
     * @param int month (9)
     * @param int day (4)
     *
     * @return int weekday number
     */
    public function getDayOfWeek($y, $m, $d)
    {
        return 0;
    }

    /**
     * Returns the numeric values of the days of the week.
     *
     * @param int year (2003)
     * @param int month (9)
     * @param int day (4)
     *
     * @return array list of numeric values of days in week, beginning 0
     */
    public function getWeekDays($y = null, $m = null, $d = null)
    {
        return 0;
    }

    /**
     * Returns the default first day of the week as an integer. Must be a
     * member of the array returned from getWeekDays
     *
     * @param int year (2003)
     * @param int month (9)
     * @param int day (4)
     *
     * @return int (e.g. 1 for Monday)
     *
     * @see getWeekDays
     */
    public function getFirstDayOfWeek($y = null, $m = null, $d = null)
    {
        return 0;
    }

    /**
     * Returns the number of hours in a day<br>
     *
     * @param int (optional) day to get hours for
     *
     * @return int (e.g. 24)
     */
    public function getHoursInDay($y = null, $m = null, $d = null)
    {
        return 0;
    }

    /**
     * Returns the number of minutes in an hour
     *
     * @param int (optional) hour to get minutes for
     *
     * @return int
     */
    public function getMinutesInHour($y = null, $m = null, $d = null, $h = null)
    {
        return 0;
    }

    /**
     * Returns the number of seconds in a minutes
     *
     * @param int (optional) minute to get seconds for
     *
     * @return int
     */
    public function getSecondsInMinute($y = null, $m = null, $d = null, $h = null, $i = null)
    {
        return 0;
    }
}
