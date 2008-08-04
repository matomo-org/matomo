<?php

/**
 * 
 * @package Piwik_Period
 */
class Piwik_Period_Week extends Piwik_Period
{
	protected $label = 'week';
	
	public function getPrettyString()
	{
		$out = $this->getDateStart()->toString() . " to " . $this->getDateEnd()->toString();
		return $out;
	}
	
	protected function generate()
	{
		if($this->subperiodsProcessed)
		{
			return;
		}
		parent::generate();
		$date = $this->date;
		
		if( $date->toString('N') > 1)
		{
			$date = $date->subDay($date->toString('N')-1);
		}
		
		$startWeek = $date;
		
		$currentDay = clone $startWeek;
		while($currentDay->compareWeek($startWeek) == 0)
		{
			$this->addSubperiod(new Piwik_Period_Day($currentDay) );
			$currentDay = $currentDay->addDay(1);
		}
	}

}
