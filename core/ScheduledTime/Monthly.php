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
 * Piwik_ScheduledTime_Monthly class is used to schedule tasks every month.
 *
 * @see Piwik_ScheduledTask
 * @package Piwik
 * @subpackage Piwik_ScheduledTime
 */
class Piwik_ScheduledTime_Monthly extends Piwik_ScheduledTime
{
    /**
     * @return int
     */
    public function getRescheduledTime()
	{
		$currentTime = $this->getTime();

		// Adds one month
		$rescheduledTime = mktime ( date('H', $currentTime),
									date('i', $currentTime), 
									date('s', $currentTime),
									date('n', $currentTime) + 1,
									1,
									date('Y', $currentTime)
									);

		$nextMonthLength = date('t', $rescheduledTime);

		// Sets scheduled day
        $scheduledDay = date('j', $currentTime);

		if ( $this->day !== null )
		{
			$scheduledDay = $this->day;
		}

		// Caps scheduled day
		if ( $scheduledDay > $nextMonthLength )
		{
			$scheduledDay = $nextMonthLength;
		}

		// Adjusts the scheduled day
		$rescheduledTime += ($scheduledDay - 1) * 86400;

		// Adjusts the scheduled hour
		$rescheduledTime = $this->adjustHour($rescheduledTime);
		
		return $rescheduledTime;
	}

	/**
	 * @param int $_day the day to set, has to be >= 1 and < 32
	 * @throws Exception if parameter _day is invalid
	 */
	public function setDay($_day)
	{
		if (!($_day >=1 && $_day < 32))
		{
			throw new Exception ("Invalid day parameter, must be >=1 and < 32");
		}

		$this->day = $_day;
	}
}
