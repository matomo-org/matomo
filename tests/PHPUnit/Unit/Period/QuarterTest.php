<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Period;

use Piwik\Date;
use Piwik\Period\Quarter;

/**
 * @group Core
 * @group QuarterTest
 * @group Period
 */
class QuarterTest extends BasePeriodTest
{
    /**
     * Test Q1 (January - March)
     */
    public function testQuarterQ1()
    {
        $quarter = new Quarter(Date::factory('2024-02-15'));

        $this->assertEquals(1, $quarter->getQuarterNumber());
        $this->assertEquals(3, $quarter->getNumberOfSubperiods());
        $this->assertEquals(['2024-01-01', '2024-02-01', '2024-03-01'], $quarter->toString());
        $this->assertEquals('2024-01-01', $quarter->getDateStart()->toString());
        $this->assertEquals('2024-03-31', $quarter->getDateEnd()->toString());
    }

    /**
     * Test Q1 from January
     */
    public function testQuarterQ1FromJanuary()
    {
        $quarter = new Quarter(Date::factory('2024-01-01'));

        $this->assertEquals(1, $quarter->getQuarterNumber());
        $this->assertEquals('2024-01-01', $quarter->getDateStart()->toString());
        $this->assertEquals('2024-03-31', $quarter->getDateEnd()->toString());
    }

    /**
     * Test Q1 from March
     */
    public function testQuarterQ1FromMarch()
    {
        $quarter = new Quarter(Date::factory('2024-03-31'));

        $this->assertEquals(1, $quarter->getQuarterNumber());
        $this->assertEquals('2024-01-01', $quarter->getDateStart()->toString());
        $this->assertEquals('2024-03-31', $quarter->getDateEnd()->toString());
    }

    /**
     * Test Q2 (April - June)
     */
    public function testQuarterQ2()
    {
        $quarter = new Quarter(Date::factory('2024-05-20'));

        $this->assertEquals(2, $quarter->getQuarterNumber());
        $this->assertEquals(3, $quarter->getNumberOfSubperiods());
        $this->assertEquals(['2024-04-01', '2024-05-01', '2024-06-01'], $quarter->toString());
        $this->assertEquals('2024-04-01', $quarter->getDateStart()->toString());
        $this->assertEquals('2024-06-30', $quarter->getDateEnd()->toString());
    }

    /**
     * Test Q3 (July - September)
     */
    public function testQuarterQ3()
    {
        $quarter = new Quarter(Date::factory('2024-08-01'));

        $this->assertEquals(3, $quarter->getQuarterNumber());
        $this->assertEquals(3, $quarter->getNumberOfSubperiods());
        $this->assertEquals(['2024-07-01', '2024-08-01', '2024-09-01'], $quarter->toString());
        $this->assertEquals('2024-07-01', $quarter->getDateStart()->toString());
        $this->assertEquals('2024-09-30', $quarter->getDateEnd()->toString());
    }

    /**
     * Test Q4 (October - December)
     */
    public function testQuarterQ4()
    {
        $quarter = new Quarter(Date::factory('2024-12-31'));

        $this->assertEquals(4, $quarter->getQuarterNumber());
        $this->assertEquals(3, $quarter->getNumberOfSubperiods());
        $this->assertEquals(['2024-10-01', '2024-11-01', '2024-12-01'], $quarter->toString());
        $this->assertEquals('2024-10-01', $quarter->getDateStart()->toString());
        $this->assertEquals('2024-12-31', $quarter->getDateEnd()->toString());
    }

    /**
     * Test Q1 in a leap year (February has 29 days)
     */
    public function testQuarterQ1LeapYear()
    {
        $quarter = new Quarter(Date::factory('2024-02-29'));

        $this->assertEquals(1, $quarter->getQuarterNumber());
        $this->assertEquals('2024-01-01', $quarter->getDateStart()->toString());
        $this->assertEquals('2024-03-31', $quarter->getDateEnd()->toString());
    }

    /**
     * Test Q1 in a non-leap year
     */
    public function testQuarterQ1NonLeapYear()
    {
        $quarter = new Quarter(Date::factory('2023-02-15'));

        $this->assertEquals(1, $quarter->getQuarterNumber());
        $this->assertEquals('2023-01-01', $quarter->getDateStart()->toString());
        $this->assertEquals('2023-03-31', $quarter->getDateEnd()->toString());
    }

    /**
     * Test pretty string format
     */
    public function testGetPrettyString()
    {
        $quarter = new Quarter(Date::factory('2024-05-15'));
        $this->assertEquals('2024-Q2', $quarter->getPrettyString());
    }

    /**
     * Test localized short string format
     */
    public function testGetLocalizedShortString()
    {
        $quarter = new Quarter(Date::factory('2024-05-15'));
        $this->assertEquals('Q2 2024', $quarter->getLocalizedShortString());
    }

    /**
     * Test localized long string format
     */
    public function testGetLocalizedLongString()
    {
        $quarter = new Quarter(Date::factory('2024-05-15'));
        $this->assertEquals('Q2 2024', $quarter->getLocalizedLongString());
    }

    /**
     * Test that quarter's child period is month
     */
    public function testGetImmediateChildPeriodLabel()
    {
        $quarter = new Quarter(Date::factory('2024-05-15'));
        $this->assertEquals('month', $quarter->getImmediateChildPeriodLabel());
    }

    /**
     * Test that quarter has no parent period (parallel period)
     */
    public function testGetParentPeriodLabel()
    {
        $quarter = new Quarter(Date::factory('2024-05-15'));
        $this->assertNull($quarter->getParentPeriodLabel());
    }

    /**
     * Test period ID constant
     */
    public function testPeriodId()
    {
        $this->assertEquals(6, Quarter::PERIOD_ID);
    }

    /**
     * Test quarter label
     */
    public function testGetLabel()
    {
        $quarter = new Quarter(Date::factory('2024-05-15'));
        $this->assertEquals('quarter', $quarter->getLabel());
    }

    /**
     * Test range string
     */
    public function testGetRangeString()
    {
        $quarter = new Quarter(Date::factory('2024-05-15'));
        $this->assertEquals('2024-04-01,2024-06-30', $quarter->getRangeString());
    }
}
