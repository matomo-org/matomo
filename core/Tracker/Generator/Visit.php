<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Generator.php 404 2008-03-23 01:09:59Z matt $
 * 
 * @package Piwik_Tracker
 */


/**
 * Fake Piwik_Tracker_Visit class that overwrite all the Time related method to be able
 * to setup a given timestamp for the generated visitor and actions.
 * 
 * 
 * @package Piwik_Tracker
 * @subpackage Piwik_Tracker_Generator
 */
class Piwik_Tracker_Generator_Visit extends Piwik_Tracker_Visit
{
	static protected $timestampToUse;
	
	static public function setTimestampToUse($time)
	{
		self::$timestampToUse = $time;
	}
	protected function getCurrentDate( $format = "Y-m-d")
	{
		return date($format, $this->getCurrentTimestamp() );
	}
	
	protected function getCurrentTimestamp()
	{
		self::$timestampToUse = max(@$this->visitorInfo['visit_last_action_time'],self::$timestampToUse);
		self::$timestampToUse += mt_rand(4,1840);
		return self::$timestampToUse;
	}
		
	protected function getDatetimeFromTimestamp($timestamp)
	{
		return date("Y-m-d H:i:s",$timestamp);
	}
	
	protected function updateCookie()
	{
		@parent::updateCookie();
	}
	
}
