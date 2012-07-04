<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
/**
 * Testing Period_Week
 */
class Period_WeekTest extends PHPUnit_Framework_TestCase
{
    /**
     * test week between 2 years
     * @group Core
     * @group Period
     * @group Period_Week
     */
    public function testWeekBetween2years()
    {
        $week = new Piwik_Period_Week( Piwik_Date::factory("2006-01-01"));
        $correct=array(
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
     * @group Period
     * @group Period_Week
     */
    public function testWeekBetween2month()
    {
        $week = new Piwik_Period_Week( Piwik_Date::factory("2006-05-29"));
        $correct=array(
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
     * @group Period
     * @group Period_Week
     */
    public function testWeekFebLeapyear()
    {
        $correct=array(
            '2023-02-27',
            '2023-02-28',
            '2023-03-01',
            '2023-03-02',
            '2023-03-03',
            '2023-03-04',
            '2023-03-05',);
        
        $week = new Piwik_Period_Week( Piwik_Date::factory('2023-02-27'));
        $this->assertEquals($correct, $week->toString());
        $this->assertEquals(7, $week->getNumberOfSubperiods());
        $week = new Piwik_Period_Week( Piwik_Date::factory('2023-03-01'));
        $this->assertEquals($correct, $week->toString());
        $this->assertEquals(7, $week->getNumberOfSubperiods());
    }
    
    /**
     * test week between feb and march for no leap year
     * @group Core
     * @group Period
     * @group Period_Week
     */
    public function testWeekFebnonLeapyear()
    {
        $correct=array(
            '2024-02-26',
            '2024-02-27',
            '2024-02-28',
            '2024-02-29',
            '2024-03-01',
            '2024-03-02',
            '2024-03-03',);
        
        $week = new Piwik_Period_Week( Piwik_Date::factory('2024-02-27'));
        $this->assertEquals($correct, $week->toString());
        $this->assertEquals(7, $week->getNumberOfSubperiods());
        $week = new Piwik_Period_Week( Piwik_Date::factory('2024-03-01'));
        $this->assertEquals($correct, $week->toString());
        $this->assertEquals(7, $week->getNumberOfSubperiods());
    }
    
    /**
     * test week normal middle of the month
     * @group Core
     * @group Period
     * @group Period_Week
     */
    public function testWeekMiddleofmonth()
    {
        $correct=array(
            '2024-10-07',
            '2024-10-08',
            '2024-10-09',
            '2024-10-10',
            '2024-10-11',
            '2024-10-12',
            '2024-10-13',);
        
        $week = new Piwik_Period_Week( Piwik_Date::factory('2024-10-09'));
        $this->assertEquals($correct, $week->toString());
        $this->assertEquals(7, $week->getNumberOfSubperiods());
    }
}
