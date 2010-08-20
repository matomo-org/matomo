<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: ScheduledTime.php
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * The Piwik_ScheduledTime abstract class is used as a base class for different types of scheduling intervals.
 * Piwik_ScheduledTime subclasses are used to schedule tasks within Piwik.
 *
 * @see Piwik_ScheduledTask
 * @package Piwik
 * @subpackage Piwik_ScheduledTime
 */
abstract class Piwik_ScheduledTime
{

#
	/**
	 * @link http://php.net/manual/en/function.date.php, format string : 'G'
	 * @var integer 
	 */
	var $hour;
	
	/**
	 * @link http://php.net/manual/en/function.date.php, format string : 'N'
	 * @var integer
	 */
	var $day;
	
	/**
	 * Represents week number within a month (1 to 4)
	 * @var integer
	 */
	var $week;

	/*
	 * Returns the system time used by subclasses to compute schedulings.
	 * This method has been introduced so unit tests can override the current system time.
	 */
	protected function getTime()
	{
		return time();
	}

	/**
	 * Computes the next scheduled time based on the system time at which the method has been called and
	 * the underlying scheduling interval.
	 *
	 * @abstract
	 * @return integer Returns the rescheduled time measured in the number of seconds since the Unix Epoch
	 */
	abstract public function getRescheduledTime();

	/*
	 * @param  _hour the hour to set, has to be >= 0 and < 24
	 * @throws Exception if method not supported by subclass or parameter _hour is invalid
	 */
	public function setHour($_hour)
	{
		if (!($_hour >=0 && $_hour < 24))
		{			
			throw new Exception ("Invalid hour parameter, must be >=0 and < 24");
		}

		$this->hour = $_hour;
	}
	
	/*
	 * @param  _day the day to set, has to be >= 1 and < 8
	 * @throws Exception if method not supported by subclass or parameter _hour is invalid
	 */
	public function setDay($_day)
	{
		if (!($_day >=1 && $_day < 8))
		{
			throw new Exception ("Invalid day parameter, must be >=1 and < 8");
		}

		$this->day = $_day;
	}
	
	/*
	 * @param  _week the week to set, has to be >= 1 and < 5
	 * @throws Exception if method not supported by subclass or parameter _week is invalid
	 */
	public function setWeek($_week)
	{
		if (!($_week >=1 && $_week < 5))
		{
			throw new Exception ("Invalid day parameter, must be >=1 and < 5");
		}

		$this->week = $_week;
	}	
	
	/*
	 * Computes the delta in seconds needed to adjust the rescheduled time to the required hour.
	 * 
	 * @param rescheduledTime The rescheduled time to be adjusted
	 * @return adjusted rescheduled time
	 */	
	protected function adjustHour ($rescheduledTime)
	{
		if ( $this->hour !== null )
		{
			// Reset the number of minutes and set the scheduled hour to the one specified with setHour()
			$rescheduledTime = mktime ( 	$this->hour,
										0,
										date('s', $rescheduledTime),
										date('n', $rescheduledTime),
										date('j', $rescheduledTime),
										date('Y', $rescheduledTime)
										);
		}
		return $rescheduledTime;
	}
	
	/*
	 * Computes the delta in seconds needed to adjust the rescheduled time to the required day.
	 * 
	 * @param rescheduledTime The rescheduled time to be adjusted
	 * @return adjusted rescheduled time
	 */	
	protected function adjustDay ($rescheduledTime)
	{
		if ( $this->day !== null )
		{
			// Removes or adds a umber of day to set the scheduled day to the one specified with setDay()
			$rescheduledTime = mktime ( 	date('H', $rescheduledTime), 
										date('i', $rescheduledTime),
										date('s', $rescheduledTime),
										date('n', $rescheduledTime),
										date('j', $rescheduledTime) - (date('N', $rescheduledTime) - $this->day),
										date('Y', $rescheduledTime)
										);
		}
		
		return $rescheduledTime;
	}
}
