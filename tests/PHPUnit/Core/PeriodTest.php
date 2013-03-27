<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class PeriodTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     * @group Period
     */
    public function testGetId()
    {
        $period = new Piwik_Period_Day(Piwik_Date::today());
        $this->assertNotEquals(0, $period->getId());
        $period = new Piwik_Period_Week(Piwik_Date::today());
        $this->assertNotEquals(0, $period->getId());
        $period = new Piwik_Period_Month(Piwik_Date::today());
        $this->assertNotEquals(0, $period->getId());
        $period = new Piwik_Period_Year(Piwik_Date::today());
        $this->assertNotEquals(0, $period->getId());
    }

    /**
     * @group Core
     * @group Period
     */
    public function testGetLabel()
    {
        $period = new Piwik_Period_Day(Piwik_Date::today());
        $label = $period->getLabel();
        $this->assertInternalType('string', $label);
        $this->assertNotEmpty($label);
        $period = new Piwik_Period_Week(Piwik_Date::today());
        $label = $period->getLabel();
        $this->assertInternalType('string', $label);
        $this->assertNotEmpty($label);
        $period = new Piwik_Period_Month(Piwik_Date::today());
        $label = $period->getLabel();
        $this->assertInternalType('string', $label);
        $this->assertNotEmpty($label);
        $period = new Piwik_Period_Year(Piwik_Date::today());
        $label = $period->getLabel();
        $this->assertInternalType('string', $label);
        $this->assertNotEmpty($label);
    }

    /**
     * @group Core
     * @group Period
     */
    public function testFactoryDay()
    {
        $period = Piwik_Period::factory('day', Piwik_Date::today());
        $this->assertInstanceOf('Piwik_Period_Day', $period);
    }

    /**
     * @group Core
     * @group Period
     */
    public function testFactoryMonth()
    {
        $period = Piwik_Period::factory('month', Piwik_Date::today());
        $this->assertInstanceOf('Piwik_Period_Month', $period);
    }

    /**
     * @group Core
     * @group Period
     */
    public function testFactoryWeek()
    {
        $period = Piwik_Period::factory('week', Piwik_Date::today());
        $this->assertInstanceOf('Piwik_Period_Week', $period);
    }

    /**
     * @group Core
     * @group Period
     */
    public function testFactoryYear()
    {
        $period = Piwik_Period::factory('year', Piwik_Date::today());
        $this->assertInstanceOf('Piwik_Period_Year', $period);
    }

    /**
     * @group Core
     * @group Period
     */
    public function testFactoryInvalid()
    {
        try {
            $period = Piwik_Period::factory('inValid', Piwik_Date::today());
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected Exception not raised');
    }
}