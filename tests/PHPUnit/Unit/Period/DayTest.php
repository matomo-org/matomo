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
use Piwik\Period\Day;

class DayTest extends BasePeriodTest
{
    /**
     * @group Core
     */
    public function testInvalidDate()
    {
        $this->expectException(\TypeError::class);

        new Day('Invalid Date');
    }

    /**
     * @group Core
     */
    public function testToString()
    {
        $period = new Day(Date::today());
        $this->assertEquals(date("Y-m-d"), $period->getPrettyString());
        $this->assertEquals(date("Y-m-d"), (string)$period);
        $this->assertEquals(date("Y-m-d"), $period->toString());
    }

    /**
     * today is NOT finished
     * @group Core
     */
    public function testDayIsFinishedToday()
    {
        $period = new Day(Date::today());
        $this->assertEquals(date("Y-m-d"), $period->toString());
        $this->assertEquals(array(), $period->getSubperiods());
        $this->assertEquals(0, $period->getNumberOfSubperiods());
    }

    /**
     * yesterday 23:59:59 is finished
     * @group Core
     */
    public function testDayIsFinishedYesterday()
    {

        $period = new Day(Date::yesterday());
        $this->assertEquals(date("Y-m-d", time() - 86400), $period->toString());
        $this->assertEquals(array(), $period->getSubperiods());
        $this->assertEquals(0, $period->getNumberOfSubperiods());
    }

    /**
     * tomorrow is not finished
     * @group Core
     */
    public function testDayIsFinishedTomorrow()
    {
        $period = new Day(Date::factory(date("Y-m-d", time() + 86400)));
        $this->assertEquals(date("Y-m-d", time() + 86400), $period->toString());
        $this->assertEquals(array(), $period->getSubperiods());
        $this->assertEquals(0, $period->getNumberOfSubperiods());
    }

    /**
     * test day doesnt exist 31st feb
     * @group Core
     */
    public function testDayIsFinished31stfeb()
    {
        $period = new Day(Date::factory("2007-02-31"));
        $this->assertEquals("2007-03-03", $period->toString());
        $this->assertEquals(array(), $period->getSubperiods());
        $this->assertEquals(0, $period->getNumberOfSubperiods());
    }

    /**
     * test date that doesn't exist, should return the corresponding correct date
     * @group Core
     */
    public function testDayGetDateStart1()
    {
        // create the period
        $period = new Day(Date::factory("2007-02-31"));

        // start date
        $startDate = $period->getDateStart();

        // expected string
        $this->assertEquals("2007-03-03", $startDate->toString());

        // check that for a day, getDateStart = getStartEnd
        $this->assertEquals($startDate, $period->getDateEnd());
    }

    /**
     * test normal date
     * @group Core
     */
    public function testDayGetDateStart2()
    {
        // create the period
        $period = new Day(Date::factory("2007-01-03"));

        // start date
        $startDate = $period->getDateStart();

        // expected string
        $this->assertEquals("2007-01-03", $startDate->toString());

        // check that for a day, getDateStart = getStartEnd
        $this->assertEquals($startDate, $period->getDateEnd());
    }

    /**
     * test last day of year
     * @group Core
     */
    public function testDayGetDateStart3()
    {
        // create the period
        $period = new Day(Date::factory("2007-12-31"));

        // start date
        $startDate = $period->getDateStart();

        // expected string
        $this->assertEquals("2007-12-31", $startDate->toString());

        // check that for a day, getDateStart = getStartEnd
        $this->assertEquals($startDate, $period->getDateEnd());
    }

    /**
     * test date that doesn't exist, should return the corresponding correct date
     * @group Core
     */
    public function testDayGetDateEnd1()
    {
        // create the period
        $period = new Day(Date::factory("2007-02-31"));

        // end date
        $endDate = $period->getDateEnd();

        // expected string
        $this->assertEquals("2007-03-03", $endDate->toString());
    }

    /**
     * test normal date
     * @group Core
     */
    public function testDayGetDateEnd2()
    {
        // create the period
        $period = new Day(Date::factory("2007-04-15"));

        // end date
        $endDate = $period->getDateEnd();

        // expected string
        $this->assertEquals("2007-04-15", $endDate->toString());
    }

    /**
     * test last day of year
     * @group Core
     */
    public function testDayGetDateEnd3()
    {
        // create the period
        $period = new Day(Date::factory("2007-12-31"));

        // end date
        $endDate = $period->getDateEnd();

        // expected string
        $this->assertEquals("2007-12-31", $endDate->toString());
    }

    /**
     * adding a subperiod should not be possible
     * @group Core
     */
    public function testAddSubperiodFails()
    {
        $this->expectException(\Exception::class);

        // create the period
        $period = new Day(Date::factory("2007-12-31"));

        $period->addSubperiod('');
    }

    public function getLocalizedShortStrings()
    {
        return array(
            array('en', 'Wed, Oct 9'),
            array('lt', '10-09, tr'),
            array('ru', 'Ср, 9 окт.'),
            array('zh-cn', '10月9日周三'),
        );
    }

    /**
     * @group Core
     * @dataProvider getLocalizedShortStrings
     */
    public function testGetLocalizedShortString($language, $shouldBe)
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage($language);

        $month = new Day(Date::factory('2024-10-09'));
        $this->assertEquals($shouldBe, $month->getLocalizedShortString());
    }

    public function getLocalizedLongStrings()
    {
        return array(
            array('en', 'Wednesday, October 9, 2024'),
            array('lt', '2024 m. spalio 9 d., trečiadienis'),
            array('zh-cn', '2024年10月9日星期三'),
        );
    }

    /**
     * @group Core
     * @dataProvider getLocalizedLongStrings
     */
    public function testGetLocalizedLongString($language, $shouldBe)
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage($language);

        $month = new Day(Date::factory('2024-10-09'));
        $this->assertEquals($shouldBe, $month->getLocalizedLongString());
    }

    /**
     * @group Core
     */
    public function testGetPrettyString()
    {
        $month = new Day(Date::factory('2024-10-09'));
        $shouldBe = '2024-10-09';
        $this->assertEquals($shouldBe, $month->getPrettyString());
    }
}
