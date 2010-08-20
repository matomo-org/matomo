<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: Daily.php
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Piwik_ScheduledTime_Daily class is used to schedule tasks every day.
 *
 * @see Piwik_ScheduledTask
 * @package Piwik
 * @subpackage Piwik_ScheduledTime
 */
class Piwik_ScheduledTime_Daily extends Piwik_ScheduledTime
{	
	public function getRescheduledTime()
	{
		$currentTime = $this->getTime();
		
		// Add one day
		$rescheduledTime = mktime ( 	date('H', $currentTime), 
									date('i', $currentTime),
									date('s', $currentTime),
									date('n', $currentTime),
									date('j', $currentTime) + 1,
									date('Y', $currentTime)
									);

		// Adjusts the scheduled hour
		$rescheduledTime = $this->adjustHour($rescheduledTime);

		return $rescheduledTime;
	}
	
	public function setDay($_day)
	{
		throw new Exception ("Method not supported");
	}
	
	public function setWeek($_week)
	{
		throw new Exception ("Method not supported");
	}
}
