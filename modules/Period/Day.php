<?php

/**
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
}
