<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: Hourly.php
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Piwik_ScheduledTime_Hourly class is used to schedule tasks every hour.
 *
 * @see Piwik_ScheduledTask
 * @package Piwik
 * @subpackage Piwik_ScheduledTime
 */
class Piwik_ScheduledTime_Hourly extends Piwik_ScheduledTime
{
	public function getRescheduledTime()
	{
		$currentTime = $this->getTime();
		
		// Adds one hour and reset the number of minutes
		$rescheduledTime = mktime ( 	date('H', $currentTime) + 1, 
									0, 
									date('s', $currentTime),
									date('n', $currentTime),
									date('j', $currentTime),
									date('Y', $currentTime)
									);
		return $rescheduledTime;
	}

	public function setHour($_hour)
	{
		throw new Exception ("Method not supported");
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
