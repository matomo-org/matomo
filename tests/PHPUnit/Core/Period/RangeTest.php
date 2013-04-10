<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class Period_RangeTest extends PHPUnit_Framework_TestCase
{
    // test range 1
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeToday()
    {
        $range = new Piwik_Period_Range('day', 'last1');
        $today = Piwik_Date::today();

        $correct = array(
            $today->toString(),
        );
        $correct = array_reverse($correct);

        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeTodayUtcPlus12()
    {
        // rather ugly test, UTC+23 doesn't exist, but it's a way to test that last1 in UTC+23 will be "our" UTC tomorrow
        $range = new Piwik_Period_Range('day', 'last1', 'UTC+23');
        $today = Piwik_Date::now()->addHour(23);

        $correct = array(
            $today->toString(),
        );
        $correct = array_reverse($correct);

        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range 2
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRange2days()
    {
        $range = new Piwik_Period_Range('day', 'last2');
        $today = Piwik_Date::today();

        $correct = array(
            $today->toString(),
            $today->subDay(1)->toString()
        );
        $correct = array_reverse($correct);

        $this->assertEquals(2, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range 3
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRange5days()
    {
        $range = new Piwik_Period_Range('day', 'last50');
        $today = Piwik_Date::today();

        $correct = array();
        for ($i = 0; $i < 50; $i++) {
            $correct[] = $today->subDay($i)->toString();
        }
        $correct = array_reverse($correct);

        $this->assertEquals(50, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range 4
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangePrevious3days()
    {
        $range = new Piwik_Period_Range('day', 'previous3');
        $yesterday = Piwik_Date::yesterday();

        $correct = array();
        for ($i = 0; $i < 3; $i++) {
            $correct[] = $yesterday->subDay($i)->toString();
        }
        $correct = array_reverse($correct);

        $this->assertEquals(3, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range date1,date2
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeComma1()
    {

        $range = new Piwik_Period_Range('day', '2008-01-01,2008-01-03');

        $correct = array(
            '2008-01-01',
            '2008-01-02',
            '2008-01-03',
        );

        $this->assertEquals(3, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range date1,date2
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeComma2()
    {

        $range = new Piwik_Period_Range('day', '2007-12-22,2008-01-03');

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
    }

    // test range date1,date2
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeWeekcomma1()
    {
        $range = new Piwik_Period_Range('week', '2007-12-22,2008-01-03');
        $range2 = new Piwik_Period_Range('week', '2007-12-19,2008-01-03');

        $correct = array(
            array(
                '2007-12-17',
                '2007-12-18',
                '2007-12-19',
                '2007-12-20',
                '2007-12-21',
                '2007-12-22',
                '2007-12-23',
            ),
            array(
                '2007-12-24',
                '2007-12-25',
                '2007-12-26',
                '2007-12-27',
                '2007-12-28',
                '2007-12-29',
                '2007-12-30',
            ),
            array(
                '2007-12-31',
                '2008-01-01',
                '2008-01-02',
                '2008-01-03',
                '2008-01-04',
                '2008-01-05',
                '2008-01-06',
            )
        );

        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals(count($correct), $range2->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
        $this->assertEquals($correct, $range2->toString());
    }

    // test range date1,date2
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeYearcomma1()
    {
        $range = new Piwik_Period_Range('year', '2006-12-22,2007-01-03');

        $correct = array(
            array(
                0  => '2006-01-01',
                1  => '2006-02-01',
                2  => '2006-03-01',
                3  => '2006-04-01',
                4  => '2006-05-01',
                5  => '2006-06-01',
                6  => '2006-07-01',
                7  => '2006-08-01',
                8  => '2006-09-01',
                9  => '2006-10-01',
                10 => '2006-11-01',
                11 => '2006-12-01',
            ),
            1 =>
            array(
                0  => '2007-01-01',
                1  => '2007-02-01',
                2  => '2007-03-01',
                3  => '2007-04-01',
                4  => '2007-05-01',
                5  => '2007-06-01',
                6  => '2007-07-01',
                7  => '2007-08-01',
                8  => '2007-09-01',
                9  => '2007-10-01',
                10 => '2007-11-01',
                11 => '2007-12-01',
            ),
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range date1,date2
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeMonthcomma1()
    {
        $range = new Piwik_Period_Range('month', '2006-12-22,2007-01-03');

        $correct = array(
            array(
                '2006-12-01',
                '2006-12-02',
                '2006-12-03',
                '2006-12-04',
                '2006-12-05',
                '2006-12-06',
                '2006-12-07',
                '2006-12-08',
                '2006-12-09',
                '2006-12-10',
                '2006-12-11',
                '2006-12-12',
                '2006-12-13',
                '2006-12-14',
                '2006-12-15',
                '2006-12-16',
                '2006-12-17',
                '2006-12-18',
                '2006-12-19',
                '2006-12-20',
                '2006-12-21',
                '2006-12-22',
                '2006-12-23',
                '2006-12-24',
                '2006-12-25',
                '2006-12-26',
                '2006-12-27',
                '2006-12-28',
                '2006-12-29',
                '2006-12-30',
                '2006-12-31',
            ),
            array(
                '2007-01-01',
                '2007-01-02',
                '2007-01-03',
                '2007-01-04',
                '2007-01-05',
                '2007-01-06',
                '2007-01-07',
                '2007-01-08',
                '2007-01-09',
                '2007-01-10',
                '2007-01-11',
                '2007-01-12',
                '2007-01-13',
                '2007-01-14',
                '2007-01-15',
                '2007-01-16',
                '2007-01-17',
                '2007-01-18',
                '2007-01-19',
                '2007-01-20',
                '2007-01-21',
                '2007-01-22',
                '2007-01-23',
                '2007-01-24',
                '2007-01-25',
                '2007-01-26',
                '2007-01-27',
                '2007-01-28',
                '2007-01-29',
                '2007-01-30',
                '2007-01-31',
            ),
        );

        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range WEEK
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeWeek()
    {
        $range = new Piwik_Period_Range('week', 'last50');
        $today = Piwik_Date::today();

        $correct = array();
        for ($i = 0; $i < 50; $i++) {
            $date = $today->subDay($i * 7);
            $week = new Piwik_Period_Week($date);

            $correct[] = $week->toString();
        }
        $correct = array_reverse($correct);


        $this->assertEquals(50, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range WEEK last1
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeWeekLast1()
    {
        $range = new Piwik_Period_Range('week', 'last1');
        $currentWeek = new Piwik_Period_Week(Piwik_Date::today());
        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals(array($currentWeek->toString()), $range->toString());
    }

    // test range MONTH
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeMonth()
    {
        $range = new Piwik_Period_Range('month', 'last20');
        $today = Piwik_Date::today();

        $correct = array();
        for ($i = 0; $i < 20; $i++) {
            $date = $today->subMonth($i);
            $week = new Piwik_Period_Month($date);

            $correct[] = $week->toString();
        }
        $correct = array_reverse($correct);

        $this->assertEquals(20, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range MONTH last1
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeMonthLast1()
    {
        $range = new Piwik_Period_Range('month', 'last1');
        $month = new Piwik_Period_Month(Piwik_Date::today());
        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals(array($month->toString()), $range->toString());
    }

    // test range PREVIOUS MONTH
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangePreviousmonth()
    {
        $range = new Piwik_Period_Range('month', 'previous10');
        $end = Piwik_Date::today();
        $end = $end->subMonth(1);

        $correct = array();
        for ($i = 0; $i < 10; $i++) {
            $date = $end->subMonth($i);
            $week = new Piwik_Period_Month($date);

            $correct[] = $week->toString();
        }
        $correct = array_reverse($correct);


        $this->assertEquals(10, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range YEAR
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeYear()
    {
        $range = new Piwik_Period_Range('year', 'last10');
        $today = Piwik_Date::today();

        $correct = array();
        for ($i = 0; $i < 10; $i++) {
            $date = $today->subMonth(12 * $i);
            $week = new Piwik_Period_Year($date);

            $correct[] = $week->toString();
        }
        $correct = array_reverse($correct);

        $this->assertEquals(10, $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    // test range YEAR last1
    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testRangeYearLast1()
    {
        $range = new Piwik_Period_Range('year', 'last1');
        $currentYear = new Piwik_Period_Year(Piwik_Date::today());
        $this->assertEquals(1, $range->getNumberOfSubperiods());
        $this->assertEquals(array($currentYear->toString()), $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangeWeekInsideEndingToday()
    {
        $range = new Piwik_Period_Range('range', '2007-12-22,2008-01-03', 'UTC', Piwik_Date::factory('2008-01-03'));

        $correct = array(
            '2007-12-22',
            '2007-12-23',
            array(
                '2007-12-24',
                '2007-12-25',
                '2007-12-26',
                '2007-12-27',
                '2007-12-28',
                '2007-12-29',
                '2007-12-30',
            ),
            array(
                '2007-12-31',
                '2008-01-01',
                '2008-01-02',
                '2008-01-03',
                '2008-01-04',
                '2008-01-05',
                '2008-01-06',
            )
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangeWeekInsideEndingYesterday()
    {
        $todays = array(
            Piwik_Date::factory('2008-01-04'),
            Piwik_Date::factory('2008-01-05'),
            Piwik_Date::factory('2008-01-14'),
            Piwik_Date::factory('2008-02-14'),
            Piwik_Date::factory('2009-02-14'),
        );

        foreach ($todays as $today) {
            $range = new Piwik_Period_Range('range', '2007-12-22,2008-01-03', 'UTC', $today);

            $correct = array(
                '2007-12-22',
                '2007-12-23',
                array(
                    '2007-12-24',
                    '2007-12-25',
                    '2007-12-26',
                    '2007-12-27',
                    '2007-12-28',
                    '2007-12-29',
                    '2007-12-30',
                ),
                '2007-12-31',
                '2008-01-01',
                '2008-01-02',
                '2008-01-03',
            );
            $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
            $this->assertEquals($correct, $range->toString());
        }
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangeOnlyDaysLessThanOneWeek()
    {
        $range = new Piwik_Period_Range('range', '2007-12-30,2008-01-01');

        $correct = array(
            '2007-12-30',
            '2007-12-31',
            '2008-01-01',
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangeOneWeekOnly()
    {
        $range = new Piwik_Period_Range('range', '2007-12-31,2008-01-06');

        $correct = array(
            array(
                '2007-12-31',
                '2008-01-01',
                '2008-01-02',
                '2008-01-03',
                '2008-01-04',
                '2008-01-05',
                '2008-01-06',
            )
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangeStartsWithWeek()
    {
        $range = new Piwik_Period_Range('range', '2007-12-31,2008-01-08');

        $correct = array(
            array(
                '2007-12-31',
                '2008-01-01',
                '2008-01-02',
                '2008-01-03',
                '2008-01-04',
                '2008-01-05',
                '2008-01-06',
            ),
            '2008-01-07',
            '2008-01-08',
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangeEndsWithWeek()
    {
        $range = new Piwik_Period_Range('range', '2007-12-21,2008-01-06');

        $correct = array(
            '2007-12-21',
            '2007-12-22',
            '2007-12-23',
            array(
                '2007-12-24',
                '2007-12-25',
                '2007-12-26',
                '2007-12-27',
                '2007-12-28',
                '2007-12-29',
                '2007-12-30',
            ),
            array(
                '2007-12-31',
                '2008-01-01',
                '2008-01-02',
                '2008-01-03',
                '2008-01-04',
                '2008-01-05',
                '2008-01-06',
            ),
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangeContainsMonthAndWeek()
    {
        $range = new Piwik_Period_Range('range', '2011-09-18,2011-11-02', 'UTC', Piwik_Date::factory('2012-01-01'));

        $correct = array(

            '2011-09-18',
            array(
                '2011-09-19',
                '2011-09-20',
                '2011-09-21',
                '2011-09-22',
                '2011-09-23',
                '2011-09-24',
                '2011-09-25',
            ),

            '2011-09-26',
            '2011-09-27',
            '2011-09-28',
            '2011-09-29',
            '2011-09-30',
            array(
                "2011-10-01",
                "2011-10-02",
                "2011-10-03",
                "2011-10-04",
                "2011-10-05",
                "2011-10-06",
                "2011-10-07",
                "2011-10-08",
                "2011-10-09",
                "2011-10-10",
                "2011-10-11",
                "2011-10-12",
                "2011-10-13",
                "2011-10-14",
                "2011-10-15",
                "2011-10-16",
                "2011-10-17",
                "2011-10-18",
                "2011-10-19",
                "2011-10-20",
                "2011-10-21",
                "2011-10-22",
                "2011-10-23",
                "2011-10-24",
                "2011-10-25",
                "2011-10-26",
                "2011-10-27",
                "2011-10-28",
                "2011-10-29",
                "2011-10-30",
                "2011-10-31",
            ),
            "2011-11-01",
            "2011-11-02",
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangeContainsSeveralMonthsAndWeeksStartingWithMonth()
    {
        // Testing when "today" is in the same month, or later in the future
        $todays = array(
            Piwik_Date::factory('2011-10-18'),
            Piwik_Date::factory('2011-10-19'),
            Piwik_Date::factory('2011-10-24'),
            Piwik_Date::factory('2011-11-01'),
            Piwik_Date::factory('2011-11-30'),
            Piwik_Date::factory('2011-12-31'),
            Piwik_Date::factory('2021-10-18')
        );
        foreach ($todays as $today) {
            $range = new Piwik_Period_Range('range', '2011-08-01,2011-10-17', 'UTC', $today);

            $correct = array(

                array(
                    "2011-08-01",
                    "2011-08-02",
                    "2011-08-03",
                    "2011-08-04",
                    "2011-08-05",
                    "2011-08-06",
                    "2011-08-07",
                    "2011-08-08",
                    "2011-08-09",
                    "2011-08-10",
                    "2011-08-11",
                    "2011-08-12",
                    "2011-08-13",
                    "2011-08-14",
                    "2011-08-15",
                    "2011-08-16",
                    "2011-08-17",
                    "2011-08-18",
                    "2011-08-19",
                    "2011-08-20",
                    "2011-08-21",
                    "2011-08-22",
                    "2011-08-23",
                    "2011-08-24",
                    "2011-08-25",
                    "2011-08-26",
                    "2011-08-27",
                    "2011-08-28",
                    "2011-08-29",
                    "2011-08-30",
                    "2011-08-31",
                ),
                array(
                    "2011-09-01",
                    "2011-09-02",
                    "2011-09-03",
                    "2011-09-04",
                    "2011-09-05",
                    "2011-09-06",
                    "2011-09-07",
                    "2011-09-08",
                    "2011-09-09",
                    "2011-09-10",
                    "2011-09-11",
                    "2011-09-12",
                    "2011-09-13",
                    "2011-09-14",
                    "2011-09-15",
                    "2011-09-16",
                    "2011-09-17",
                    "2011-09-18",
                    "2011-09-19",
                    "2011-09-20",
                    "2011-09-21",
                    "2011-09-22",
                    "2011-09-23",
                    "2011-09-24",
                    "2011-09-25",
                    "2011-09-26",
                    "2011-09-27",
                    "2011-09-28",
                    "2011-09-29",
                    "2011-09-30",
                ),
                "2011-10-01",
                "2011-10-02",

                array(
                    "2011-10-03",
                    "2011-10-04",
                    "2011-10-05",
                    "2011-10-06",
                    "2011-10-07",
                    "2011-10-08",
                    "2011-10-09",
                ),
                array(
                    "2011-10-10",
                    "2011-10-11",
                    "2011-10-12",
                    "2011-10-13",
                    "2011-10-14",
                    "2011-10-15",
                    "2011-10-16",
                ),
                "2011-10-17",
            );

            $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
            $this->assertEquals($correct, $range->toString());
        }
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangeOneMonthOnly()
    {
        $range = new Piwik_Period_Range('range', '2011-09-01,2011-09-30');

        $correct = array(
            array(
                "2011-09-01",
                "2011-09-02",
                "2011-09-03",
                "2011-09-04",
                "2011-09-05",
                "2011-09-06",
                "2011-09-07",
                "2011-09-08",
                "2011-09-09",
                "2011-09-10",
                "2011-09-11",
                "2011-09-12",
                "2011-09-13",
                "2011-09-14",
                "2011-09-15",
                "2011-09-16",
                "2011-09-17",
                "2011-09-18",
                "2011-09-19",
                "2011-09-20",
                "2011-09-21",
                "2011-09-22",
                "2011-09-23",
                "2011-09-24",
                "2011-09-25",
                "2011-09-26",
                "2011-09-27",
                "2011-09-28",
                "2011-09-29",
                "2011-09-30",
            ));
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function test_CustomRange_startsWithWeek_EndsWithMonth()
    {
        $range = new Piwik_Period_Range('range', '2011-07-25,2011-08-31');

        $correct = array(

            array(
                '2011-07-25',
                '2011-07-26',
                '2011-07-27',
                '2011-07-28',
                '2011-07-29',
                '2011-07-30',
                '2011-07-31',
            ),
            array(
                "2011-08-01",
                "2011-08-02",
                "2011-08-03",
                "2011-08-04",
                "2011-08-05",
                "2011-08-06",
                "2011-08-07",
                "2011-08-08",
                "2011-08-09",
                "2011-08-10",
                "2011-08-11",
                "2011-08-12",
                "2011-08-13",
                "2011-08-14",
                "2011-08-15",
                "2011-08-16",
                "2011-08-17",
                "2011-08-18",
                "2011-08-19",
                "2011-08-20",
                "2011-08-21",
                "2011-08-22",
                "2011-08-23",
                "2011-08-24",
                "2011-08-25",
                "2011-08-26",
                "2011-08-27",
                "2011-08-28",
                "2011-08-29",
                "2011-08-30",
                "2011-08-31",
            ));
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangeBeforeIsAfterYearRight()
    {
        try {
            $range = new Piwik_Period_Range('range', '2007-02-09,2007-02-01');
            $this->assertEquals(0, $range->getNumberOfSubperiods());
            $this->assertEquals(array(), $range->toString());

            $range->getPrettyString();
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangeLastN()
    {
        $range = new Piwik_Period_Range('range', 'last4');
        $range->setDefaultEndDate(Piwik_Date::factory('2008-01-03'));
        $correct = array(
            '2007-12-31',
            '2008-01-01',
            '2008-01-02',
            '2008-01-03',
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangePreviousN()
    {
        $range = new Piwik_Period_Range('range', 'previous3');
        $range->setDefaultEndDate(Piwik_Date::factory('2008-01-03'));
        $correct = array(
            '2007-12-31',
            '2008-01-01',
            '2008-01-02',
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testCustomRangePreviousNEndToday()
    {
        $range = new Piwik_Period_Range('range', 'previous3');
        $correct = array(
            date('Y-m-d', time() - 86400 * 3),
            date('Y-m-d', time() - 86400 * 2),
            date('Y-m-d', time() - 86400 * 1),
        );
        $this->assertEquals(count($correct), $range->getNumberOfSubperiods());
        $this->assertEquals($correct, $range->toString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testInvalidRangeThrows()
    {
        try {
            $range = new Piwik_Period_Range('range', '0001-01-01,today');
            $range->getLocalizedLongString();
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testGetLocalizedShortString()
    {
        Piwik_Translate::getInstance()->loadEnglishTranslation();
        $month = new Piwik_Period_Range('range', '2000-12-09,2001-02-01');
        $shouldBe = '9 Dec 00 - 1 Feb 01';
        $this->assertEquals($shouldBe, $month->getLocalizedShortString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testGetLocalizedLongString()
    {
        Piwik_Translate::getInstance()->loadEnglishTranslation();
        $month = new Piwik_Period_Range('range', '2023-05-09,2023-05-21');
        $shouldBe = '8 May 23 - 21 May 23';
        $this->assertEquals($shouldBe, $month->getLocalizedLongString());
    }

    /**
     * @group Core
     * @group Period
     * @group Period_Range
     */
    public function testGetPrettyString()
    {
        Piwik_Translate::getInstance()->loadEnglishTranslation();
        $month = new Piwik_Period_Range('range', '2007-02-09,2007-03-15');
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
     * @group Core
     * @group Period
     * @group Period_Range
     * @dataProvider getDataForLastNLimitsTest
     */
    public function testLastNLimits($period, $lastN, $expectedLastN)
    {
        $range = new Piwik_Period_Range($period, 'last' . $lastN);
        $this->assertEquals($expectedLastN, $range->getNumberOfSubperiods());
    }
}
