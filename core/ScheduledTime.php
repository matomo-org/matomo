<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
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
	/**
	 * @link http://php.net/manual/en/function.date.php, format string : 'G'
	 * @var integer 
	 */
	var $hour;
	
	/**
	 * For weekly scheduling : http://php.net/manual/en/function.date.php, format string : 'N'
	 * For monthly scheduling : day of the month (1 to 31),
	 * capped to http://php.net/manual/en/function.cal-days-in-month.php when needed
	 * @var integer
	 */
	var $day;
	

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
	 * @param  _day the day to set
	 * @throws Exception if method not supported by subclass or parameter _day is invalid
	 */
	abstract public function setDay($_day);

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
}
