<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: Weekly.php
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Piwik_ScheduledTime_Weekly class is used to schedule tasks every week.
 *
 * @see Piwik_ScheduledTask
 * @package Piwik
 * @subpackage Piwik_ScheduledTime
 */
class Piwik_ScheduledTime_Weekly extends Piwik_ScheduledTime
{
	
	public function getRescheduledTime()
	{
		$currentTime = $this->getTime();
		
		// Adds 7 days
		$rescheduledTime = mktime ( 	date('H', $currentTime), 
									date('i', $currentTime),
									date('s', $currentTime),
									date('n', $currentTime),
									date('j', $currentTime) + 7,
									date('Y', $currentTime)
									);
		
		// Adjusts the scheduled hour
		$rescheduledTime = $this->adjustHour($rescheduledTime);
		
		// Adjusts the scheduled day
		$rescheduledTime = $this->adjustDay($rescheduledTime);
		
		return $rescheduledTime;
	}
	
	public function setWeek($_week)
	{
		throw new Exception ("Method not supported");
	}
}
