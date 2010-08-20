<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: Monthly.php
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
	
	public function getRescheduledTime()
	{
		$currentTime = $this->getTime();
		
		// Adds one month
		$rescheduledTime = mktime ( 	date('H', $currentTime), 
									date('i', $currentTime), 
									date('s', $currentTime),
									date('n', $currentTime) + 1,
									date('j', $currentTime),
									date('Y', $currentTime)
									);
									
		// Adjusts the scheduled hour
		$rescheduledTime = $this->adjustHour($rescheduledTime);
		
		// Adjusts the scheduled day
		$rescheduledTime = $this->adjustDay($rescheduledTime);
		
		if ( $this->week !== null )
		{
			// Computes the week number of the scheduled month
			$rescheduledWeek = date('W', $rescheduledTime) - (4 * (date('n', $rescheduledTime)-1) );
			$weekError = $rescheduledWeek - $this->week;
			
			if ( $weekError != 0)
			{ 
				/*
				 * Adds or remove a multiple of 7 days to adjust the scheduled week to the one specified
				 * with setWeek()
				 */
				$rescheduledTime = mktime ( 	date('H', $rescheduledTime), 
											date('i', $rescheduledTime), 
											date('s', $rescheduledTime),
											date('n', $rescheduledTime),
											date('j', $rescheduledTime) - (7 * $weekError),
											date('Y', $rescheduledTime)
											);
			}
		}
		
		return $rescheduledTime;
	}
}
