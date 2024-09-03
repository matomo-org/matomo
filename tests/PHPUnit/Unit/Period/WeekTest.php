<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Period;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Period\Week;

/**
 * Class WeekTest
 * @group WeekTest
 */
class WeekTest extends BasePeriodTest
{
    /**
     * test week between 2 years
     * @group Core
     */
    public function testWeekBetween2years()
    {
        $week = new Week(Date::factory("2006-01-01"));
        $correct = array(
            "2005-12-26",
            "2005-12-27",
            "2005-12-28",
            "2005-12-29",
            "2005-12-30",
            "2005-12-31",
            "2006-01-01",);
        $this->assertEquals($correct, $week->toString());
        $this->assertEquals(7, $week->getNumberOfSubperiods());
    }

    /**
     * test week between 2 months Week Mai 29 To Mai 31 2006
     * @group Core
     */
    public function testWeekBetween2month()
    {
        $week = new Week(Date::factory("2006-05-29"));
        $correct = array(
            "2006-05-29",
            "2006-05-30",
            "2006-05-31",
            "2006-06-01",
            "2006-06-02",
            "2006-06-03",
            "2006-06-04",);
        $this->assertEquals($correct, $week->toString());
        $this->assertEquals(7, $week->getNumberOfSubperiods());
    }

    /**
     * test week between feb and march for leap year
     * @group Core
     */
    public function testWeekFebLeapyear()
    {
        $correct = array(
            '2023-02-27',
            '2023-02-28',
            '2023-03-01',
            '2023-03-02',
            '2023-03-03',
            '2023-03-04',
            '2023-03-05',);

        $week = new Week(Date::factory('2023-02-27'));
        $this->assertEquals($correct, $week->toString());
        $this->assertEquals(7, $week->getNumberOfSubperiods());
        $week = new Week(Date::factory('2023-03-01'));
        $this->assertEquals($correct, $week->toString());
        $this->assertEquals(7, $week->getNumberOfSubperiods());
    }

    /**
     * test week between feb and march for no leap year
     * @group Core
     */
    public function testWeekFebnonLeapyear()
    {
        $correct = array(
            '2024-02-26',
            '2024-02-27',
            '2024-02-28',
            '2024-02-29',
            '2024-03-01',
            '2024-03-02',
            '2024-03-03',);

        $week = new Week(Date::factory('2024-02-27'));
        $this->assertEquals($correct, $week->toString());
        $this->assertEquals(7, $week->getNumberOfSubperiods());
        $week = new Week(Date::factory('2024-03-01'));
        $this->assertEquals($correct, $week->toString());
        $this->assertEquals(7, $week->getNumberOfSubperiods());
    }

    /**
     * test week normal middle of the month
     * @group Core
     */
    public function testWeekMiddleofmonth()
    {
        $correct = array(
            '2024-10-07',
            '2024-10-08',
            '2024-10-09',
            '2024-10-10',
            '2024-10-11',
            '2024-10-12',
            '2024-10-13',);

        $week = new Week(Date::factory('2024-10-09'));
        $this->assertEquals($correct, $week->toString());
        $this->assertEquals(7, $week->getNumberOfSubperiods());
    }

    public function getLocalizedShortStrings()
    {
        return array(
            array('en', array('Oct 7 – 13, 2024', 'Nov 25 – Dec 1, 2024', 'Dec 30, 2024 – Jan 5, 2025')),
            array('lt', array('2024 spal. 7–13', '2024 lapkr. 25 – gruod. 1', '2024 gruod. 30 – 2025 saus. 5')),
            array('zh-cn', array('2024年10月7日至13日', '2024年11月25日至12月1日', '2024年12月30日至2025年01月5日')),
        );
    }

    /**
     * @group Core
     * @dataProvider getLocalizedShortStrings
     */
    public function testGetLocalizedShortString($language, $shouldBe)
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage($language);
        // a week within a month
        $week = new Week(Date::factory('2024-10-09'));
        $this->assertEquals($shouldBe[0], $week->getLocalizedShortString());

        // a week ending in another month
        $week = new Week(Date::factory('2024-12-01'));
        $this->assertEquals($shouldBe[1], $week->getLocalizedShortString());

        // a week ending in another year
        $week = new Week(Date::factory('2024-12-31'));
        $this->assertEquals($shouldBe[2], $week->getLocalizedShortString());
    }

    public function getLocalizedLongStrings()
    {
        return array(
            array('en', array('week October 7 – 13, 2024', 'week November 25 – December 1, 2024', 'week December 30, 2024 – January 5, 2025')),
            array('es', array('semana 7–13 de octubre de 2024', 'semana 25 de noviembre – 1 de diciembre de 2024', 'semana 30 de diciembre de 2024 – 5 de enero de 2025')),
            array('lt', array('savaitė 2024 spalio 7–13', 'savaitė 2024 lapkričio 25 – gruodžio 1', 'savaitė 2024 gruodžio 30 – 2025 sausio 5')),
            array('zh-cn', array('周 2024年10月7日至13日', '周 2024年11月25日至12月1日', '周 2024年12月30日至2025年01月5日')),
        );
    }

    /**
     * @group Core
     * @dataProvider getLocalizedLongStrings
     */
    public function testGetLocalizedLongString($language, $shouldBe)
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage($language);
        // a week within a month
        $week = new Week(Date::factory('2024-10-09'));
        $this->assertEquals($shouldBe[0], $week->getLocalizedLongString());

        // a week ending in another month
        $week = new Week(Date::factory('2024-12-01'));
        $this->assertEquals($shouldBe[1], $week->getLocalizedLongString());

        // a week ending in another year
        $week = new Week(Date::factory('2024-12-31'));
        $this->assertEquals($shouldBe[2], $week->getLocalizedLongString());
    }

    /**
     * @group Core
     */
    public function testGetPrettyString()
    {
        $week = new Week(Date::factory('2024-10-09'));
        $shouldBe = 'From 2024-10-07 to 2024-10-13';
        $this->assertEquals($shouldBe, $week->getPrettyString());
    }
}
