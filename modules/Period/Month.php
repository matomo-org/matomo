<?php
/**
 * 
 * @package Piwik_Period
 */
class Piwik_Period_Month extends Piwik_Period
{
	protected $label = 'month';

	public function getPrettyString()
	{
		$out = $this->getDateStart()->toString('Y-m');
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
		
		$startMonth = $date->setDay(1);
		$currentDay = clone $startMonth;
		while($currentDay->compareMonth($startMonth) == 0)
		{
			$this->addSubperiod(new Piwik_Period_Day($currentDay));
			$currentDay = $currentDay->addDay(1);
		}
	}
	
	public function isFinished()
	{
		if(!$this->subperiodsProcessed)
		{
			$this->generate();
		}
		// a month is finished 
		// if current month > month AND current year == year
		// OR if current year > year
		$year = $this->date->get("Y");
		return ( date("m") > $this->date->get("m") && date("Y") == $year)
				||  date("Y") > $year;
	}
}
