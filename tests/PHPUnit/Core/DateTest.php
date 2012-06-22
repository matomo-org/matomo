<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
class DateTest extends PHPUnit_Framework_TestCase
{
    /**
     * create today object check that timestamp is correct (midnight)
     * 
     * @group Core
     * @group Date
     */
    public function testToday()
    {
        $date = Piwik_Date::today();
        $this->assertEquals( strtotime(date("Y-m-d "). " 00:00:00"), $date->getTimestamp());
        
        // test getDatetime()
        $this->assertEquals( $date->getDatetime(), $date->getDateStartUTC());
        $date = $date->setTime('12:00:00');
        $this->assertEquals( $date->getDatetime(), date('Y-m-d') . ' 12:00:00');
    }
    
    /**
     * create today object check that timestamp is correct (midnight)
     * 
     * @group Core
     * @group Date
     */
    public function testYesterday()
    {
        $date = Piwik_Date::yesterday();
        $this->assertEquals( strtotime(date("Y-m-d",strtotime('-1day')). " 00:00:00"), $date->getTimestamp());
    }

    /**
     * @group Core
     * @group Date
     */
    public function test_invalidDate_throws()
    {
        try {
            $date = Piwik_Date::factory('0001-01-01');
        } catch(Exception $e) {
            return;
        }
        $this->fail('Exception not raised');
    }

    /**
     * @group Core
     * @group Date
     */
    public function testFactoryTimezone()
    {
        // now in UTC converted to UTC+10 means adding 10 hours 
        $date = Piwik_Date::factory('now', 'UTC+10');
        $dateExpected = Piwik_Date::now()->addHour(10);
        $this->assertEquals($date->getDatetime(), $dateExpected->getDatetime());

        // Congo is in UTC+1 all year long (no DST)
        $date = Piwik_Date::factory('now', 'Africa/Brazzaville');
        $dateExpected = Piwik_Date::factory('now')->addHour(1);
        $this->assertEquals($date->getDatetime(), $dateExpected->getDatetime());
        
        // yesterday same time in Congo is the same as today in Congo - 24 hours
        $date = Piwik_Date::factory('yesterdaySameTime', 'Africa/Brazzaville');
        $dateExpected = Piwik_Date::factory('now', 'Africa/Brazzaville')->subHour(24);
        $this->assertEquals($date->getDatetime(), $dateExpected->getDatetime());

        if(Piwik::isTimezoneSupportEnabled())
        {
            // convert to/from local time
            $now = time();
            $date = Piwik_Date::factory($now, 'America/New_York');
            $time = $date->getTimestamp();
            $this->assertTrue($time < $now);

            $date = Piwik_Date::factory($time)->setTimezone('America/New_York');
            $time = $date->getTimestamp();
            $this->assertEquals($now, $time);
        }
    }
    
    /**
     * @group Core
     * @group Date
     */
    public function testSetTimezoneDayInUTC()
    {
        $date = Piwik_Date::factory('2010-01-01');
        
        $dayStart = '2010-01-01 00:00:00';
        $dayEnd = '2010-01-01 23:59:59';
        $this->assertEquals($date->getDateStartUTC(), $dayStart);
        $this->assertEquals($date->getDateEndUTC(), $dayEnd);
        
        // try with empty timezone
        $date = $date->setTimezone('');
        $this->assertEquals($date->getDateStartUTC(), $dayStart);
        $this->assertEquals($date->getDateEndUTC(), $dayEnd);
        
        $date = $date->setTimezone('UTC');
        $this->assertEquals($date->getDateStartUTC(), $dayStart);
        $this->assertEquals($date->getDateEndUTC(), $dayEnd);

        if(Piwik::isTimezoneSupportEnabled())
        {
            $date = $date->setTimezone('Europe/Paris');
            $utcDayStart = '2009-12-31 23:00:00';
            $utcDayEnd = '2010-01-01 22:59:59';
            $this->assertEquals($date->getDateStartUTC(), $utcDayStart);
            $this->assertEquals($date->getDateEndUTC(), $utcDayEnd);
        }
        
        $date = $date->setTimezone('UTC+1');
        $utcDayStart = '2009-12-31 23:00:00';
        $utcDayEnd = '2010-01-01 22:59:59';
        $this->assertEquals($date->getDateStartUTC(), $utcDayStart);
        $this->assertEquals($date->getDateEndUTC(), $utcDayEnd);

        $date = $date->setTimezone('UTC-1');
        $utcDayStart = '2010-01-01 01:00:00';
        $utcDayEnd = '2010-01-02 00:59:59';
        $this->assertEquals($date->getDateStartUTC(), $utcDayStart);
        $this->assertEquals($date->getDateEndUTC(), $utcDayEnd);

        if(Piwik::isTimezoneSupportEnabled())
        {
            $date = $date->setTimezone('America/Vancouver');
            $utcDayStart = '2010-01-01 08:00:00';
            $utcDayEnd = '2010-01-02 07:59:59';
            $this->assertEquals($date->getDateStartUTC(), $utcDayStart);
            $this->assertEquals($date->getDateEndUTC(), $utcDayEnd);
        }
    }
    
    /**
     * @group Core
     * @group Date
     */
    public function testModifyDateWithTimezone()
    {
        $date = Piwik_Date::factory('2010-01-01');
        $date = $date->setTimezone('UTC-1');
        
        $timestamp = $date->getTimestamp();
        $date = $date->addHour(0)->addHour(0)->addHour(0);
        $this->assertEquals($timestamp, $date->getTimestamp());
        

        if(Piwik::isTimezoneSupportEnabled())
        {
            $date = Piwik_Date::factory('2010-01-01')->setTimezone('Europe/Paris');
            $dateExpected = clone $date;
            $date = $date->addHour(2);
            $dateExpected = $dateExpected->addHour(1.1)->addHour(0.9)->addHour(1)->subHour(1);
            $this->assertEquals($date->getTimestamp(), $dateExpected->getTimestamp());
        }
    }
    
    /**
     * @group Core
     * @group Date
     */
    public function testGetDateStartUTCEndDuringDstTimezone()
    {
        if(Piwik::isTimezoneSupportEnabled())
        {
            $date = Piwik_Date::factory('2010-03-28');

            $date = $date->setTimezone('Europe/Paris');
            $utcDayStart = '2010-03-27 23:00:00';
            $utcDayEnd = '2010-03-28 21:59:59';

            $this->assertEquals($date->getDateStartUTC(), $utcDayStart);
            $this->assertEquals($date->getDateEndUTC(), $utcDayEnd);
        }
    }
    
    /**
     * @group Core
     * @group Date
     */
    public function testAddHour()
    {
        // add partial hours less than 1
        $dayStart = '2010-03-28 00:00:00';
        $dayExpected = '2010-03-28 00:18:00';
        $date = Piwik_Date::factory($dayStart)->addHour(0.3);
        $this->assertEquals($date->getDatetime(), $dayExpected);
        $date = $date->subHour(0.3);
        $this->assertEquals($date->getDatetime(), $dayStart);
        
        // add partial hours
        $dayExpected = '2010-03-28 05:45:00';
        $date = Piwik_Date::factory($dayStart)->addHour(5.75);
        $this->assertEquals($date->getDatetime(), $dayExpected);
        
        // remove partial hours
        $dayExpected = '2010-03-27 18:15:00';
        $date = Piwik_Date::factory($dayStart)->subHour(5.75);
        $this->assertEquals($date->getDatetime(), $dayExpected);
    }

    /**
     * @group Core
     * @group Date
     */
    public function testAddHourLongHours()
    {
        $dateTime = '2010-01-03 11:22:33';
        $expectedTime = '2010-01-05 11:28:33';
        $this->assertEquals(Piwik_Date::factory($dateTime)->addHour(48.1)->getDatetime(), $expectedTime);
        $this->assertEquals(Piwik_Date::factory($dateTime)->addHour(48.1)->subHour(48.1)->getDatetime(), $dateTime);
    }

    /**
     * @group Core
     * @group Date
     */
    public function testAddPeriod()
    {
        $date = Piwik_Date::factory('2010-01-01');
        $dateExpected = Piwik_Date::factory('2010-01-06');
        $date = $date->addPeriod(5, 'day');
        $this->assertEquals($date->getTimestamp(), $dateExpected->getTimestamp());

        $date = Piwik_Date::factory('2010-03-01');
        $dateExpected = Piwik_Date::factory('2010-04-05');
        $date = $date->addPeriod(5, 'week');
        $this->assertEquals($date->getTimestamp(), $dateExpected->getTimestamp());
    }

    /**
     * @group Core
     * @group Date
     */
    public function testSubPeriod()
    {
        $date = Piwik_Date::factory('2010-03-01');
        $dateExpected = Piwik_Date::factory('2010-02-15');
        $date = $date->subPeriod(2, 'week');
        $this->assertEquals($date->getTimestamp(), $dateExpected->getTimestamp());

        $date = Piwik_Date::factory('2010-12-15');
        $dateExpected = Piwik_Date::factory('2005-12-15');
        $date = $date->subPeriod(5, 'year');
        $this->assertEquals($date->getTimestamp(), $dateExpected->getTimestamp());
    }
}
