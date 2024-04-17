<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Period;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Period\Month;

/**
 * @group Core
 * @group MonthTest
 * @group Period
 */
class MonthTest extends BasePeriodTest
{
    /**
     * testing december
     */
    public function testMonthDec()
    {
        $month = new Month(Date::factory("2006-12-31"));
        $correct = array(
            "2006-12-01",
            "2006-12-02",
            "2006-12-03",
            implode(',', array(
                "2006-12-04",
                "2006-12-05",
                "2006-12-06",
                "2006-12-07",
                "2006-12-08",
                "2006-12-09",
                "2006-12-10"
            )),
            implode(',', array(
                "2006-12-11",
                "2006-12-12",
                "2006-12-13",
                "2006-12-14",
                "2006-12-15",
                "2006-12-16",
                "2006-12-17"
            )),
            implode(',', array(
                "2006-12-18",
                "2006-12-19",
                "2006-12-20",
                "2006-12-21",
                "2006-12-22",
                "2006-12-23",
                "2006-12-24",
            )),
            implode(',', array(
                "2006-12-25",
                "2006-12-26",
                "2006-12-27",
                "2006-12-28",
                "2006-12-29",
                "2006-12-30",
                "2006-12-31"
            )),
        );

        $this->assertEquals($correct, $month->toString());
        $this->assertEquals(7, $month->getNumberOfSubperiods());
    }

    /**
     * testing month feb leap year
     */
    public function testMonthFebLeap()
    {
        $month = new Month(Date::factory("2024-02-11"), Date::factory("2025-01-01"));
        $correct = array(
            "2024-02-01",
            "2024-02-02",
            "2024-02-03",
            "2024-02-04",
            implode(',', array(
                "2024-02-05",
                "2024-02-06",
                "2024-02-07",
                "2024-02-08",
                "2024-02-09",
                "2024-02-10",
                "2024-02-11"
            )),
            implode(',', array(
                "2024-02-12",
                "2024-02-13",
                "2024-02-14",
                "2024-02-15",
                "2024-02-16",
                "2024-02-17",
                "2024-02-18"
            )),
            implode(',', array(
                "2024-02-19",
                "2024-02-20",
                "2024-02-21",
                "2024-02-22",
                "2024-02-23",
                "2024-02-24",
                "2024-02-25"
            )),
            "2024-02-26",
            "2024-02-27",
            "2024-02-28",
            "2024-02-29");

        $this->assertEquals($correct, $month->toString());
        $this->assertEquals(11, $month->getNumberOfSubperiods());
    }

    /**
     * testing month feb non-leap year
     */
    public function testMonthFebNonLeap()
    {
        $month = new Month(Date::factory("2023-02-11"));
        $correct = array(
            "2023-02-01",
            "2023-02-02",
            "2023-02-03",
            "2023-02-04",
            "2023-02-05",
            implode(',', array(
                "2023-02-06",
                "2023-02-07",
                "2023-02-08",
                "2023-02-09",
                "2023-02-10",
                "2023-02-11",
                "2023-02-12"
            )),
            implode(',', array(
                "2023-02-13",
                "2023-02-14",
                "2023-02-15",
                "2023-02-16",
                "2023-02-17",
                "2023-02-18",
                "2023-02-19"
            )),
            implode(',', array(
                "2023-02-20",
                "2023-02-21",
                "2023-02-22",
                "2023-02-23",
                "2023-02-24",
                "2023-02-25",
                "2023-02-26"
            )),
            "2023-02-27",
            "2023-02-28");
        $this->assertEquals($correct, $month->toString());
        $this->assertEquals(10, $month->getNumberOfSubperiods());
    }

    /**
     * testing jan
     */
    public function testMonthJan()
    {
        $month = new Month(Date::factory("2007-01-01"));
        $correct = array(
            implode(',', array(
                "2007-01-01",
                "2007-01-02",
                "2007-01-03",
                "2007-01-04",
                "2007-01-05",
                "2007-01-06",
                "2007-01-07"
            )),
            implode(',', array(
                "2007-01-08",
                "2007-01-09",
                "2007-01-10",
                "2007-01-11",
                "2007-01-12",
                "2007-01-13",
                "2007-01-14"
            )),
            implode(',', array(
                "2007-01-15",
                "2007-01-16",
                "2007-01-17",
                "2007-01-18",
                "2007-01-19",
                "2007-01-20",
                "2007-01-21"
            )),
            implode(',', array(
                "2007-01-22",
                "2007-01-23",
                "2007-01-24",
                "2007-01-25",
                "2007-01-26",
                "2007-01-27",
                "2007-01-28"
            )),
            "2007-01-29",
            "2007-01-30",
            "2007-01-31");
        $this->assertEquals($correct, $month->toString());
        $this->assertEquals(7, $month->getNumberOfSubperiods());
    }

    /**
     * testing month containing a time change (DST)
     */
    public function testMonthDSTChangeMarch()
    {
        $month = new Month(Date::factory("2007-02-31"));
        $correct = array(
            "2007-03-01",
            "2007-03-02",
            "2007-03-03",
            "2007-03-04",
            implode(',', array(
                "2007-03-05",
                "2007-03-06",
                "2007-03-07",
                "2007-03-08",
                "2007-03-09",
                "2007-03-10",
                "2007-03-11"
            )),
            implode(',', array(
                "2007-03-12",
                "2007-03-13",
                "2007-03-14",
                "2007-03-15",
                "2007-03-16",
                "2007-03-17",
                "2007-03-18"
            )),
            implode(',', array(
                "2007-03-19",
                "2007-03-20",
                "2007-03-21",
                "2007-03-22",
                "2007-03-23",
                "2007-03-24",
                "2007-03-25"
            )),
            "2007-03-26",
            "2007-03-27",
            "2007-03-28",
            "2007-03-29",
            "2007-03-30",
            "2007-03-31");
        $this->assertEquals($correct, $month->toString());
        $this->assertEquals(13, $month->getNumberOfSubperiods());
    }

    public function testMonthDSTChangeOct()
    {
        $month = new Month(Date::factory("2017-10-31"));
        $correct = array(
            "2017-10-01",
            implode(',', array(
                "2017-10-02",
                "2017-10-03",
                "2017-10-04",
                "2017-10-05",
                "2017-10-06",
                "2017-10-07",
                "2017-10-08"
            )),
            implode(',', array(
                "2017-10-09",
                "2017-10-10",
                "2017-10-11",
                "2017-10-12",
                "2017-10-13",
                "2017-10-14",
                "2017-10-15"
            )),
            implode(',', array(
                "2017-10-16",
                "2017-10-17",
                "2017-10-18",
                "2017-10-19",
                "2017-10-20",
                "2017-10-21",
                "2017-10-22"
            )),
            implode(',', array(
                "2017-10-23",
                "2017-10-24",
                "2017-10-25",
                "2017-10-26",
                "2017-10-27",
                "2017-10-28",
                "2017-10-29"
            )),
            "2017-10-30",
            "2017-10-31",);
        $this->assertEquals($correct, $month->toString());
        $this->assertEquals(7, $month->getNumberOfSubperiods());
    }

    public function getLocalizedShortStrings()
    {
        return array(
            array('en', 'Oct 2024'),
            array('lt', '2024-10'),
            array('zh-cn', '2024年10月'),
        );
    }

    /**
     * @group Core
     * @group Month
     * @dataProvider getLocalizedShortStrings
     */
    public function testGetLocalizedShortString($language, $shouldBe)
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage($language);

        $month = new Month(Date::factory('2024-10-09'));
        $this->assertEquals($shouldBe, $month->getLocalizedShortString());
    }

    public function getLocalizedLongStrings()
    {
        return array(
            array('en', 'October 2024'),
            array('lt', '2024 m. spalis'),
            array('zh-cn', '2024年10月'),
        );
    }

    /**
     * @group Core
     * @group Month
     * @dataProvider getLocalizedLongStrings
     */
    public function testGetLocalizedLongString($language, $shouldBe)
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage($language);

        $month = new Month(Date::factory('2024-10-09'));
        $this->assertEquals($shouldBe, $month->getLocalizedLongString());
    }

    public function testGetPrettyString()
    {
        $month = new Month(Date::factory('2024-10-09'));
        $shouldBe = '2024-10';
        $this->assertEquals($shouldBe, $month->getPrettyString());
    }
}
