<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Returns a string stating the number of days and hours from a number of seconds
 * 
 * Example
 * - 10 gives "10s"
 * - 4200 gives "1h 10min"
 * - 86400 gives "1 day"
 * - 90600 gives "1 day 1h" (it is exactly 1day 1h 10min but we truncate)
 */
function smarty_modifier_sumtime($string)
{
	$seconds = (double)$string;
	$days = floor($seconds / 86400);
	
	$minusDays = $seconds - $days * 86400;
	$hours = floor($minusDays / 3600);
	
	$minusDaysAndHours = $minusDays - $hours * 3600;
	$minutes = floor($minusDaysAndHours / 60 );
	
	$minusDaysAndHoursAndMinutes = $minusDaysAndHours - $minutes * 60;
	$secondsMod = $minusDaysAndHoursAndMinutes; // should be same as $seconds % 60 
	
	if($days > 0)
	{
		return sprintf("%d days %d hours", $days, $hours);
	}
	elseif($hours > 0)
	{
		return sprintf("%d hours %d min", $hours, $minutes);
	}
	else
	{
		return sprintf("%d min %d s", $minutes, $seconds);		
	}
}

