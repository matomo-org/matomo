<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Period;

use Exception;
use Piwik\Date;
use Piwik\Period\Month;
use Piwik\Period\Range;
use Piwik\Period\Week;
use Piwik\Period\Year;

/**
 * @group Core
 */
class RangeTest extends BasePeriodTest
{
    public function setUp(): void
    {
        parent::setUp();
        Date::$now = null;
    }

    /**
     * @dataProvider getDateXPeriodsAgoProvider
     */
    public function testGetDateXPeriodsAgo($expected, $subXPeriods, $date, $period)
    {
        $result = Range::getDateXPeriodsAgo($subXPeriods, $date, $period);
        $day = [$expected,$expected];
        if ($period === 'range') {
            $day = [$expected, false];
        }
        $this->assertEquals($day, $result);
    }

    public function getDateXPeriodsAgoProvider()
    {
        return [
            ['2019-05-14', '1', '2019-05-15', 'day'],
            ['2018-05-15', '1', '2019-05-15', 'year'],
            ['2019-05-08', '1', '2019-05-15', 'week'],
            ['2019-04-15', '1', '2019-05-15', 'month'],
            ['2019-02-15', '3', '2019-05-15', 'month'],
            ['2018-05-15', '365', '2019-05-15', 'day'],
            ['2018-05-15', '1', '2019-05-15', 'year'],
            ['2017-05-15', '2', '2019-05-15', 'year'],
            ['2019-06-04,2019-06-09', '1', '2019-06-10,2019-06-15', 'range'],
            ['2019-06-10,2019-06-12', '1', '2019-06-13,2019-06-15', 'range'],
        ];
    }

    // test range 1
    public function testRangeToday()
    {
        $range = new Range('day', 'last1');
        $today = Date::today();

        $correct = array(
            $today->toString(),
        );
        $correct = array_reverse($correct);

        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testRangeTodayUtcPlus12()
    {
        // rather ugly test, UTC+23 doesn't exist, but it's a way to test that last1 in UTC+23 will be "our" UTC tomorrow
        $range = new Range('day', 'last1', 'UTC+23');
        $today = Date::now()->addHour(23);

        $correct = array(
            $today->toString(),
        );
        $correct = array_reverse($correct);

        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range 2
    public function testRange2days()
    {
        $range = new Range('day', 'last2');
        $today = Date::today();

        $correct = array(
            $today->toString(),
            $today->subDay(1)->toString()
        );
        $correct = array_reverse($correct);

        $this->assertEquals(2, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range 3
    public function testRange5days()
    {
        $range = new Range('day', 'last50');
        $today = Date::today();

        $correct = array();
        for ($i = 0; $i < 50; $i++) {
            $correct[] = $today->subDay($i)->toString();
        }
        $correct = array_reverse($correct);

        $this->assertEquals(50, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range 4
    public function testRangePrevious3days()
    {
        $range = new Range('day', 'previous3');
        $yesterday = Date::yesterday();

        $correct = array();
        for ($i = 0; $i < 3; $i++) {
            $correct[] = $yesterday->subDay($i)->toString();
        }
        $correct = array_reverse($correct);

        $this->assertEquals(3, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range date1,date2
    public function testRangeComma1()
    {

        $range = new Range('day', '2008-01-01,2008-01-03');

        $correct = array(
            '2008-01-01',
            '2008-01-02',
            '2008-01-03',
        );

        $this->assertEquals(3, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range date1,date2
    public function testRangeComma2()
    {

        $rangeString = '2007-12-22,2008-01-03';
        $range = new Range('day', $rangeString);

        $correct = array(
            '2007-12-22',
            '2007-12-23',
            '2007-12-24',
            '2007-12-25',
            '2007-12-26',
            '2007-12-27',
            '2007-12-28',
            '2007-12-29',
            '2007-12-30',
            '2007-12-31',
            '2008-01-01',
            '2008-01-02',
            '2008-01-03',
        );

        $this->assertEquals(13, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
        $this->assertEquals($rangeString, $range->getRangeString());
    }

    // test range date1,date2
    // see https://github.com/piwik/piwik/issues/6194
    public function testRangeComma3EndDateIncludesToday()
    {
        $range = new Range('day', '2008-01-01,today');
        $subPeriods = $range->getSubperiods();
        $this->assertEquals('2008-01-01', $subPeriods[0]->toString());
        $this->assertEquals('2008-01-02', $subPeriods[1]->toString());
        $this->assertEquals('2008-01-03', $subPeriods[2]->toString());
    }

    // test range date1,date2
    public function testRangeComma4EndDateIncludesTodayWithTimezone()
    {
        Date::$now = strtotime('2020-08-01 03:00:00');
        $range = new Range('day', '2008-01-01,today', 'Europe/Berlin');
        $subPeriods = $range->getSubperiods();
        $this->assertEquals('2008-01-01', $subPeriods[0]->toString());
        $this->assertEquals('2008-01-02', $subPeriods[1]->toString());
        $this->assertEquals('2008-01-03', $subPeriods[2]->toString());
        $this->assertEquals('2020-08-01', end($subPeriods)->toString());
    }

    // test range date1,date2
    public function testRangeComma5EndDateIncludesTodayWithTimezoneAfterCurrentUTCDate()
    {
        Date::$now = strtotime('2020-08-01 03:00:00');
        $range = new Range('day', '2008-01-01,today', 'Pacific/Auckland');
        $subPeriods = $range->getSubperiods();
        $this->assertEquals('2008-01-01', $subPeriods[0]->toString());
        $this->assertEquals('2008-01-02', $subPeriods[1]->toString());
        $this->assertEquals('2008-01-03', $subPeriods[2]->toString());
        $this->assertEquals('2020-08-01', end($subPeriods)->toString());
    }

    // test range date1,date2
    public function testRangeComma6EndDateIncludesTodayWithTimezoneBeforeCurrentUTCDate()
    {
        Date::$now = strtotime('2020-08-01 03:00:00');
        $range = new Range('day', '2008-01-01,today', 'America/New_York');
        $subPeriods = $range->getSubperiods();
        $this->assertEquals('2008-01-01', $subPeriods[0]->toString());
        $this->assertEquals('2008-01-02', $subPeriods[1]->toString());
        $this->assertEquals('2008-01-03', $subPeriods[2]->toString());
        $this->assertEquals('2020-07-31', end($subPeriods)->toString());
    }

    // test range date1,date2
    public function testRangeWeekcomma1()
    {
        $range = new Range('week', '2007-12-22,2008-01-03');
        $range2String = '2007-12-19,2008-01-03';
        $range2 = new Range('week', $range2String);

        $correct = array(
            implode(',', array(
                '2007-12-17',
                '2007-12-18',
                '2007-12-19',
                '2007-12-20',
                '2007-12-21',
                '2007-12-22',
                '2007-12-23',
            )),
            implode(',', array(
                '2007-12-24',
                '2007-12-25',
                '2007-12-26',
                '2007-12-27',
                '2007-12-28',
                '2007-12-29',
                '2007-12-30',
            )),
            implode(',', array(
                '2007-12-31',
                '2008-01-01',
                '2008-01-02',
                '2008-01-03',
                '2008-01-04',
                '2008-01-05',
                '2008-01-06',
            )),
        );

        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals(count($correct), $range2->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
        $this->assertEquals($correct, $range2->toString());
        $this->assertEquals('2007-12-17,2008-01-06', $range2->getRangeString());
    }

    // test range date1,date2
    public function testRangeYearcomma1()
    {
        $range = new Range('year', '2006-12-22,2007-01-03');

        $correct = [
            implode(',', [
                0 => '2006-01-01',
                1 => '2006-02-01',
                2 => '2006-03-01',
                3 => '2006-04-01',
                4 => '2006-05-01',
                5 => '2006-06-01',
                6 => '2006-07-01',
                7 => '2006-08-01',
                8 => '2006-09-01',
                9 => '2006-10-01',
                10 => '2006-11-01',
                11 => '2006-12-01',
            ]),
            1 => implode(',', [
                0 => '2007-01-01',
                1 => '2007-02-01',
                2 => '2007-03-01',
                3 => '2007-04-01',
                4 => '2007-05-01',
                5 => '2007-06-01',
                6 => '2007-07-01',
                7 => '2007-08-01',
                8 => '2007-09-01',
                9 => '2007-10-01',
                10 => '2007-11-01',
                11 => '2007-12-01',
            ]),
        ];
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
        $this->assertEquals('2006-01-01,2007-12-31', $range->getRangeString());
    }

    // test range date1,date2
    public function testRangeMonthcomma1()
    {
        $range = new Range('month', '2006-12-22,2007-01-03');

        $correct = array(
            implode(',', array(
                '2006-12-01',
                '2006-12-02',
                '2006-12-03',
                implode(',', array(
                    '2006-12-04',
                    '2006-12-05',
                    '2006-12-06',
                    '2006-12-07',
                    '2006-12-08',
                    '2006-12-09',
                    '2006-12-10'
                )),
                implode(',', array(
                    '2006-12-11',
                    '2006-12-12',
                    '2006-12-13',
                    '2006-12-14',
                    '2006-12-15',
                    '2006-12-16',
                    '2006-12-17'
                )),
                implode(',', array(
                    '2006-12-18',
                    '2006-12-19',
                    '2006-12-20',
                    '2006-12-21',
                    '2006-12-22',
                    '2006-12-23',
                    '2006-12-24'
                )),
                implode(',', array(
                    '2006-12-25',
                    '2006-12-26',
                    '2006-12-27',
                    '2006-12-28',
                    '2006-12-29',
                    '2006-12-30',
                    '2006-12-31'
                )),
            )),
            implode(',', array(
                implode(',', array(
                    '2007-01-01',
                    '2007-01-02',
                    '2007-01-03',
                    '2007-01-04',
                    '2007-01-05',
                    '2007-01-06',
                    '2007-01-07'
                )),
                implode(',', array(
                    '2007-01-08',
                    '2007-01-09',
                    '2007-01-10',
                    '2007-01-11',
                    '2007-01-12',
                    '2007-01-13',
                    '2007-01-14'
                )),
                implode(',', array(
                    '2007-01-15',
                    '2007-01-16',
                    '2007-01-17',
                    '2007-01-18',
                    '2007-01-19',
                    '2007-01-20',
                    '2007-01-21',
                )),
                implode(',', array(
                    '2007-01-22',
                    '2007-01-23',
                    '2007-01-24',
                    '2007-01-25',
                    '2007-01-26',
                    '2007-01-27',
                    '2007-01-28'
                )),
                '2007-01-29',
                '2007-01-30',
                '2007-01-31',
            )),
        );

        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
        $this->assertEquals('2006-12-01,2007-01-31', $range->getRangeString());
    }

    // test range date1,date2
    public function testRangeMonthcommaAfterMaxAllowedDate()
    {
        Date::$now = strtotime('2024-07-09');
        $range = new Range('month', '2024-01-01,2100-01-03');

        // range should be limited to 2034, so includes 11 years
        $this->assertEquals(11 * 12, $range->getNumberOfSubperiods());
        $this->assertEquals('2024-01-01,2034-12-31', $range->getRangeString());
    }

    // test range WEEK
    public function testRangeWeek()
    {
        $range = new Range('week', 'last50');
        $today = Date::today();

        $correct = array();
        for ($i = 0; $i < 50; $i++) {
            $date = $today->subDay($i * 7);
            $week = new Week($date);

            $correct[] = implode(',', $week->toString());
        }
        $correct = array_reverse($correct);

        $this->assertEquals(50, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range WEEK last1
    public function testRangeWeekLast1()
    {
        $range = new Range('week', 'last1');
        $currentWeek = new Week(Date::today());
        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals(array(implode(',', $currentWeek->toString())), $range->toString());
    }

    // test range MONTH
    public function testRangeMonth()
    {
        $range = new Range('month', 'last20');
        $today = Date::today();

        $correct = array();
        for ($i = 0; $i < 20; $i++) {
            $date = $today->subMonth($i);
            $week = new Month($date);

            $correct[] = implode(',', $week->toString());
        }
        $correct = array_reverse($correct);

        $this->assertEquals(20, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range MONTH last1
    public function testRangeMonthLast1()
    {
        $range = new Range('month', 'last1');
        $month = new Month(Date::today());
        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals(array(implode(',', $month->toString())), $range->toString());
    }

    // test range PREVIOUS MONTH
    public function testRangePreviousmonth()
    {
        $range = new Range('month', 'previous10');
        $end = Date::today();
        $end = $end->subMonth(1);

        $correct = array();
        for ($i = 0; $i < 10; $i++) {
            $date = $end->subMonth($i);
            $week = new Month($date);

            $correct[] = implode(',', $week->toString());
        }
        $correct = array_reverse($correct);

        $this->assertEquals(10, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testRangePreviousmonthOnLastDayOfMonth()
    {
        $end = Date::factory('2013-10-31');
        $range = new Range('month', 'previous10', 'UTC', $end);
        $end = $end->subMonth(1);

        $correct = array();
        for ($i = 0; $i < 10; $i++) {
            $date = $end->subMonth($i);
            $week = new Month($date);

            $correct[] = implode(',', $week->toString());
        }
        $correct = array_reverse($correct);

        $this->assertEquals(10, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testRangePreviousweekOnLastDayOfWeek()
    {
        $end = Date::factory('2013-11-03');
        $range = new Range('week', 'previous2', 'UTC', $end);
        $end = $end->subWeek(1);

        $correct = array();
        for ($i = 0; $i < 2; $i++) {
            $date = $end->subWeek($i);
            $week = new Week($date);
            $correct[] = implode(',', $week->toString());
        }
        $correct = array_reverse($correct);
        $this->assertEquals($correct, $range->toString());
    }

    public function testRangePreviousweekOnFirstDayOfWeek()
    {
        $end = Date::factory('2013-11-04');
        $range = new Range('week', 'previous2', 'UTC', $end);
        $end = $end->subWeek(1);

        $correct = array();
        for ($i = 0; $i < 2; $i++) {
            $date = $end->subWeek($i);
            $week = new Week($date);

            $correct[] = implode(',', $week->toString());
        }
        $correct = array_reverse($correct);
        $this->assertEquals($correct, $range->toString());
    }

    public function testRangeLastweekOnFirstDayOfWeek()
    {
        $end = Date::factory('2013-11-04');
        $range = new Range('week', 'last2', 'UTC', $end);

        $correct = array();
        for ($i = 0; $i < 2; $i++) {
            $date = $end->subWeek($i);
            $week = new Week($date);

            $correct[] = implode(',', $week->toString());
        }
        $correct = array_reverse($correct);
        $this->assertEquals($correct, $range->toString());
    }

    public function testRangeLastmonthOnLastDayOfMonth()
    {
        $end = Date::factory('2013-10-31');
        $range = new Range('month', 'last10', 'UTC', $end);

        $correct = [];
        for ($i = 0; $i < 10; $i++) {
            $date = $end->subMonth($i);
            $month = new Month($date);

            $correct[] = implode(',', $month->toString());
        }
        $correct = array_reverse($correct);

        $this->assertEquals(10, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testRangePreviousmonthOnFirstOfMonth()
    {
        $end = Date::factory('2013-11-01');
        $range = new Range('month', 'previous10', 'UTC', $end);
        $end = $end->subMonth(1);

        $correct = [];
        for ($i = 0; $i < 10; $i++) {
            $date = $end->subMonth($i);
            $month = new Month($date);

            $correct[] = implode(',', $month->toString());
        }
        $correct = array_reverse($correct);

        $this->assertEquals(10, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testRangeLastmonthOnFirstOfMonth()
    {
        $end = Date::factory('2013-11-01');
        $range = new Range('month', 'last10', 'UTC', $end);

        $correct = [];
        for ($i = 0; $i < 10; $i++) {
            $date = $end->subMonth($i);
            $month = new Month($date);

            $correct[] = implode(',', $month->toString());
        }
        $correct = array_reverse($correct);

        $this->assertEquals(10, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range YEAR
    public function testRangeYear()
    {
        $range = new Range('year', 'last10');
        $today = Date::today();

        $correct = array();
        for ($i = 0; $i < 10; $i++) {
            $date = $today->subMonth(12 * $i);
            $week = new Year($date);

            $correct[] = implode(',', $week->toString());
        }
        $correct = array_reverse($correct);

        $this->assertEquals(10, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range YEAR last1
    public function testRangeYearLast1()
    {
        $range = new Range('year', 'last1');
        $currentYear = new Year(Date::today());
        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals(array(implode(',', $currentYear->toString())), $range->toString());
    }

    public function testCustomRangeYearUsesYearIfPossible()
    {
        $range = new Range('range', '2005-12-17,2008-01-03', 'UTC', Date::factory('2008-01-03'));
        $year2006 = new Year(Date::factory('2006-02-02'));
        $year2007 = new Year(Date::factory('2007-02-02'));
        $year2008 = new Year(Date::factory('2008-02-02'));

        $correct = array(
            '2005-12-17',
            '2005-12-18',
            implode(',', array (
                "2005-12-19",
                "2005-12-20",
                "2005-12-21",
                "2005-12-22",
                "2005-12-23",
                "2005-12-24",
                "2005-12-25"
            )),
            "2005-12-26",
            "2005-12-27",
            "2005-12-28",
            "2005-12-29",
            "2005-12-30",
            "2005-12-31",
            implode(',', $year2006->toString()),
            implode(',', $year2007->toString()),
            implode(',', $year2008->toString()),
        );

        $this->assertEquals(12, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangeIsYearUsesFullYear()
    {
        $range = new Range('range', '2011-01-01,2011-12-31', 'UTC', Date::factory('2012-01-03'));
        $year2011 = new Year(Date::factory('2011-02-02'));

        $correct = array(
            implode(',', $year2011->toString()),
        );

        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangeYearUsesCurrentYear()
    {
        $rangeString = '2013-01-01,2013-11-01';
        $range = new Range('range', $rangeString, 'UTC', Date::factory('2013-11-01'));
        $year2013 = new Year(Date::factory('2013-02-02'));

        $correct = array(
            implode(',', $year2013->toString()),
        );

        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
        $this->assertEquals($correct, $range->toString());
        $this->assertEquals($rangeString, $range->getRangeString());
    }

    public function testCustomRangeYearUsesCurrentYearOnLastDayOfYear()
    {
        $range = new Range('range', '2013-01-01,2013-12-31', 'UTC', Date::factory('2013-12-31'));
        $year2013 = new Year(Date::factory('2013-01-01'));

        $correct = array(
            implode(',', $year2013->toString()),
        );

        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangeWeekInsideEndingToday()
    {
        $range = new Range('range', '2007-12-22,2008-01-03', 'UTC', Date::factory('2008-01-03'));

        $correct = array(
            '2007-12-22',
            '2007-12-23',
            implode(',', array(
                '2007-12-24',
                '2007-12-25',
                '2007-12-26',
                '2007-12-27',
                '2007-12-28',
                '2007-12-29',
                '2007-12-30',
            )),
            implode(',', array(
                '2007-12-31',
                '2008-01-01',
                '2008-01-02',
                '2008-01-03',
                '2008-01-04',
                '2008-01-05',
                '2008-01-06',
            )),
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testRangeEndDateIsTodayAndStartDateNotStartOfTheWeek()
    {
        $range = new Range('range', '2013-10-29,2013-10-30', 'UTC', Date::factory('2013-10-30'));

        $correct = array(
            '2013-10-29',
            '2013-10-30'
        );

        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testRangeEndDateIsInFuture()
    {
        $range = new Range('range', '2013-10-29,2013-10-31', 'UTC', Date::factory('2013-10-30'));

        $correct = array(
            '2013-10-29',
            '2013-10-30',
            '2013-10-31'
        );

        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testRangePreviousmonthEndDateIsInFutureAndEndOfTheWeek()
    {
        $range = new Range('range', '2013-10-29,2013-11-03', 'UTC', Date::factory('2013-10-30'));

        $correct = array(
            '2013-10-29',
            '2013-10-30',
            '2013-10-31',
            '2013-11-01',
            '2013-11-02',
            '2013-11-03',
        );

        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangeWeekInsideEndingYesterday()
    {
        $todays = array(
            Date::factory('2008-01-04'),
            Date::factory('2008-01-05'),
            Date::factory('2008-01-14'),
            Date::factory('2008-02-14'),
            Date::factory('2009-02-14'),
        );

        foreach ($todays as $today) {
            $range = new Range('range', '2007-12-22,2008-01-03', 'UTC', $today);

            $correct = array(
                '2007-12-22',
                '2007-12-23',
                implode(',', array(
                    '2007-12-24',
                    '2007-12-25',
                    '2007-12-26',
                    '2007-12-27',
                    '2007-12-28',
                    '2007-12-29',
                    '2007-12-30',
                )),
                '2007-12-31',
                '2008-01-01',
                '2008-01-02',
                '2008-01-03',
            );
            $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
            $this->assertEquals($correct, $range->toString());
        }
    }

    public function testCustomRangeOnlyDaysLessThanOneWeek()
    {
        $range = new Range('range', '2007-12-30,2008-01-01');

        $correct = array(
            '2007-12-30',
            '2007-12-31',
            '2008-01-01',
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangeOneWeekOnly()
    {
        $range = new Range('range', '2007-12-31,2008-01-06');

        $correct = array(
            implode(',', array(
                '2007-12-31',
                '2008-01-01',
                '2008-01-02',
                '2008-01-03',
                '2008-01-04',
                '2008-01-05',
                '2008-01-06',
            )),
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangeStartsWithWeek()
    {
        $range = new Range('range', '2007-12-31,2008-01-08');

        $correct = array(
            implode(',', array(
                '2007-12-31',
                '2008-01-01',
                '2008-01-02',
                '2008-01-03',
                '2008-01-04',
                '2008-01-05',
                '2008-01-06',
            )),
            '2008-01-07',
            '2008-01-08',
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangeEndsWithWeek()
    {
        $range = new Range('range', '2007-12-21,2008-01-06');

        $correct = array(
            '2007-12-21',
            '2007-12-22',
            '2007-12-23',
            implode(',', array(
                '2007-12-24',
                '2007-12-25',
                '2007-12-26',
                '2007-12-27',
                '2007-12-28',
                '2007-12-29',
                '2007-12-30',
            )),
            implode(',', array(
                '2007-12-31',
                '2008-01-01',
                '2008-01-02',
                '2008-01-03',
                '2008-01-04',
                '2008-01-05',
                '2008-01-06',
            )),
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangeContainsMonthAndWeek()
    {
        $range = new Range('range', '2011-09-18,2011-11-02', 'UTC', Date::factory('2012-01-01'));

        $correct = array(

            '2011-09-18',
            implode(',', array(
                '2011-09-19',
                '2011-09-20',
                '2011-09-21',
                '2011-09-22',
                '2011-09-23',
                '2011-09-24',
                '2011-09-25',
            )),

            '2011-09-26',
            '2011-09-27',
            '2011-09-28',
            '2011-09-29',
            '2011-09-30',
            implode(',', array(
                "2011-10-01",
                "2011-10-02",
                implode(',', array(
                    "2011-10-03",
                    "2011-10-04",
                    "2011-10-05",
                    "2011-10-06",
                    "2011-10-07",
                    "2011-10-08",
                    "2011-10-09"
                )),
                implode(',', array(
                    "2011-10-10",
                    "2011-10-11",
                    "2011-10-12",
                    "2011-10-13",
                    "2011-10-14",
                    "2011-10-15",
                    "2011-10-16"
                )),
                implode(',', array(
                    "2011-10-17",
                    "2011-10-18",
                    "2011-10-19",
                    "2011-10-20",
                    "2011-10-21",
                    "2011-10-22",
                    "2011-10-23"
                )),
                implode(',', array(
                    "2011-10-24",
                    "2011-10-25",
                    "2011-10-26",
                    "2011-10-27",
                    "2011-10-28",
                    "2011-10-29",
                    "2011-10-30"
                )),
                "2011-10-31",
            )),
            "2011-11-01",
            "2011-11-02",
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangeContainsSeveralMonthsAndWeeksStartingWithMonth()
    {
        // Testing when "today" is in the same month, or later in the future
        $todays = array(
            Date::factory('2011-10-18'),
            Date::factory('2011-10-19'),
            Date::factory('2011-10-24'),
            Date::factory('2011-11-01'),
            Date::factory('2011-11-30'),
            Date::factory('2011-12-31'),
            Date::factory('2021-10-18')
        );
        foreach ($todays as $today) {
            $range = new Range('range', '2011-08-01,2011-10-17', 'UTC', $today);

            $correct = array(

                implode(',', array(
                    implode(',', array(
                        "2011-08-01",
                        "2011-08-02",
                        "2011-08-03",
                        "2011-08-04",
                        "2011-08-05",
                        "2011-08-06",
                        "2011-08-07"
                    )),
                    implode(',', array(
                        "2011-08-08",
                        "2011-08-09",
                        "2011-08-10",
                        "2011-08-11",
                        "2011-08-12",
                        "2011-08-13",
                        "2011-08-14"
                    )),
                    implode(',', array(
                        "2011-08-15",
                        "2011-08-16",
                        "2011-08-17",
                        "2011-08-18",
                        "2011-08-19",
                        "2011-08-20",
                        "2011-08-21"
                    )),
                    implode(',', array(
                        "2011-08-22",
                        "2011-08-23",
                        "2011-08-24",
                        "2011-08-25",
                        "2011-08-26",
                        "2011-08-27",
                        "2011-08-28"
                    )),
                    "2011-08-29",
                    "2011-08-30",
                    "2011-08-31",
                )),
                implode(',', array(
                    "2011-09-01",
                    "2011-09-02",
                    "2011-09-03",
                    "2011-09-04",
                    implode(',', array(
                        "2011-09-05",
                        "2011-09-06",
                        "2011-09-07",
                        "2011-09-08",
                        "2011-09-09",
                        "2011-09-10",
                        "2011-09-11"
                    )),
                    implode(',', array(
                        "2011-09-12",
                        "2011-09-13",
                        "2011-09-14",
                        "2011-09-15",
                        "2011-09-16",
                        "2011-09-17",
                        "2011-09-18"
                    )),
                    implode(',', array(
                        "2011-09-19",
                        "2011-09-20",
                        "2011-09-21",
                        "2011-09-22",
                        "2011-09-23",
                        "2011-09-24",
                        "2011-09-25"
                    )),
                    "2011-09-26",
                    "2011-09-27",
                    "2011-09-28",
                    "2011-09-29",
                    "2011-09-30",
                )),
                "2011-10-01",
                "2011-10-02",

                implode(',', array(
                    "2011-10-03",
                    "2011-10-04",
                    "2011-10-05",
                    "2011-10-06",
                    "2011-10-07",
                    "2011-10-08",
                    "2011-10-09",
                )),
                implode(',', array(
                    "2011-10-10",
                    "2011-10-11",
                    "2011-10-12",
                    "2011-10-13",
                    "2011-10-14",
                    "2011-10-15",
                    "2011-10-16",
                )),
                "2011-10-17",
            );

            $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
            $this->assertEquals($correct, $range->toString());
        }
    }

    public function testCustomRangeOneMonthOnly()
    {
        $range = new Range('range', '2011-09-01,2011-09-30');

        $correct = array(
            implode(',', array(
                "2011-09-01",
                "2011-09-02",
                "2011-09-03",
                "2011-09-04",
                implode(',', array(
                    "2011-09-05",
                    "2011-09-06",
                    "2011-09-07",
                    "2011-09-08",
                    "2011-09-09",
                    "2011-09-10",
                    "2011-09-11"
                )),
                implode(',', array(
                    "2011-09-12",
                    "2011-09-13",
                    "2011-09-14",
                    "2011-09-15",
                    "2011-09-16",
                    "2011-09-17",
                    "2011-09-18"
                )),
                implode(',', array(
                    "2011-09-19",
                    "2011-09-20",
                    "2011-09-21",
                    "2011-09-22",
                    "2011-09-23",
                    "2011-09-24",
                    "2011-09-25"
                )),
                "2011-09-26",
                "2011-09-27",
                "2011-09-28",
                "2011-09-29",
                "2011-09-30",
            )),
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangeStartsWithWeekEndsWithMonth()
    {
        $range = new Range('range', '2011-07-25,2011-08-31');

        $correct = array(

            implode(',', array(
                '2011-07-25',
                '2011-07-26',
                '2011-07-27',
                '2011-07-28',
                '2011-07-29',
                '2011-07-30',
                '2011-07-31',
            )),
            implode(',', array(
                implode(',', array(
                "2011-08-01",
                "2011-08-02",
                "2011-08-03",
                "2011-08-04",
                "2011-08-05",
                "2011-08-06",
                "2011-08-07"
                )),
                implode(',', array(
                    "2011-08-08",
                    "2011-08-09",
                    "2011-08-10",
                    "2011-08-11",
                    "2011-08-12",
                    "2011-08-13",
                    "2011-08-14"
                )),
                implode(',', array(
                    "2011-08-15",
                    "2011-08-16",
                    "2011-08-17",
                    "2011-08-18",
                    "2011-08-19",
                    "2011-08-20",
                    "2011-08-21"
                )),
                implode(',', array(
                    "2011-08-22",
                    "2011-08-23",
                    "2011-08-24",
                    "2011-08-25",
                    "2011-08-26",
                    "2011-08-27",
                    "2011-08-28"
                )),
                "2011-08-29",
                "2011-08-30",
                "2011-08-31",
            )),
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangeBeforeIsAfterYearRight()
    {
        $this->expectException(Exception::class);

        $range = new Range('range', '2007-02-09,2007-02-01');
        $this->assertEquals(0, $range->getNumberOfSubperiods());
        $this->assertEquals(array(), $range->toString());

        $range->getPrettyString();
    }

    /**
     * @dataProvider getAbnormalDateRanges
     */
    public function testCustomRangeWithOutOfRangeDate($dateStr)
    {
        self::expectException(Exception::class);

        $range = new Range('range', $dateStr);
        $range->getDateStart();
    }

    public function getAbnormalDateRanges(): iterable
    {
        yield 'range starts before first website creation' => [
            '1900-01-01,2021-01-01',
        ];

        yield 'range starts after it ends' => [
            '2024-01-01,2020-12-16',
        ];
    }

    public function testCustomRangeLastN()
    {
        $range = new Range('range', 'last4');
        $range->setDefaultEndDate(Date::factory('2008-01-03'));
        $correct = array(
            '2007-12-31',
            '2008-01-01',
            '2008-01-02',
            '2008-01-03',
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangePreviousN()
    {
        $range = new Range('range', 'previous3');
        $range->setDefaultEndDate(Date::factory('2008-01-03'));
        $correct = array(
            '2007-12-31',
            '2008-01-01',
            '2008-01-02',
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testCustomRangePreviousNEndToday()
    {
        $range = new Range('range', 'previous3');
        $correct = array(
            date('Y-m-d', time() - 86400 * 3),
            date('Y-m-d', time() - 86400 * 2),
            date('Y-m-d', time() - 86400 * 1),
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    public function testInvalidRangeThrows()
    {
        $this->expectException(Exception::class);

        $range = new Range('range', '0001-01-01,today');
        $range->getLocalizedLongString();
    }

    public function testGetLocalizedShortString()
    {
        $month = new Range('range', '2000-12-09,2001-02-01');
        $shouldBe = 'Dec 9, 2000 – Feb 1, 2001';
        $this->assertEquals($shouldBe, $month->getLocalizedShortString());
    }

    public function testGetLocalizedLongString()
    {
        $month = new Range('range', '2023-05-09,2023-05-21');
        $shouldBe = 'May 9 – 21, 2023';
        $this->assertEquals($shouldBe, $month->getLocalizedLongString());
    }

    public function testGetPrettyString()
    {
        $month = new Range('range', '2007-02-09,2007-03-15');
        $shouldBe = 'From 2007-02-09 to 2007-03-15';
        $this->assertEquals($shouldBe, $month->getPrettyString());
    }

    /**
     * Data provider for testLastNLimits.
     */
    public function getDataForLastNLimitsTest()
    {
        return array(array('day', 5 * 365 + 1, 5 * 365),
                     array('week', 10 * 52 + 1, 10 * 52),
                     array('month', 121, 120),
                     array('year', 11, 10));
    }

    /**
     * @dataProvider getDataForLastNLimitsTest
     */
    public function testLastNLimits($period, $lastN, $expectedLastN)
    {
        $range = new Range($period, 'last' . $lastN);
        $this->assertEquals($expectedLastN, $range->getNumberOfSubperiods());
    }

    /**
     * @link https://github.com/piwik/piwik/pull/7057
     */
    public function testLastWithoutNumberShouldBehaveLikeLast1()
    {
        $range = new Range('day', 'last');
        $expected = new Range('day', 'last1');

        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals($expected->getRangeString(), $range->getRangeString());
    }

    /**
     * @link https://github.com/piwik/piwik/pull/7057
     */
    public function testPreviousWithoutNumberShouldBehaveLikePrevious1()
    {
        $range = new Range('day', 'previous');
        $expected = new Range('day', 'previous1');

        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals($expected->getRangeString(), $range->getRangeString());
    }

    /**
     * @link https://github.com/piwik/piwik/pull/7057
     */
    public function testLast0ShouldBehaveLikeLast1()
    {
        $range = new Range('day', 'last0');
        $expected = new Range('day', 'last1');

        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals($expected->getRangeString(), $range->getRangeString());
    }

    /**
     * @link https://github.com/piwik/piwik/pull/7057
     */
    public function testPrevious0ShouldBehaveLikePrevious1()
    {
        $range = new Range('day', 'previous0');
        $expected = new Range('day', 'previous1');

        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals($expected->getRangeString(), $range->getRangeString());
    }
}
