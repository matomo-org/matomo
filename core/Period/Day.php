<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Period
 */

class Piwik_Period_Day extends Piwik_Period
{
	protected $label = 'day';

	public function getPrettyString()
	{
		$out = $this->getDateStart()->toString() ;
		return $out;
	}
	
	public function getLocalizedShortString()
	{
		//"Mon 15 Aug"
		$date = $this->getDateStart();
		$template = "%shortDay% %day% %shortMonth%";
		$out = $date->getLocalized($template);
		return $out;
	}
	public function getLocalizedLongString()
	{
		//"Mon 15 Aug"
		$date = $this->getDateStart();
		$template = "%longDay% %day% %longMonth% %longYear%";
		$out = $date->getLocalized($template);
		return $out;
	}
	
	public function isFinished()
	{
		$todayMidnight = Piwik_Date::today();
		if($this->date->isEarlier($todayMidnight))
		{
			return true;
		}
	}
	
	public function getNumberOfSubperiods()
	{
		return 0;
	}	
	
	public function addSubperiod( $date )
	{
		throw new Exception("Adding a subperiod is not supported for Piwik_Period_Day");
	}
	
	public function toString()
	{
		return $this->date->toString("Y-m-d");
	}
	public function __toString()
	{
		return $this->toString();
	}
}
