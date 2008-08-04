<?php

require_once "Period/Month.php";
/**
 * 
 * @package Piwik_Period
 */
class Piwik_Period_Year extends Piwik_Period
{	
	protected $label = 'year';

	public function getPrettyString()
	{
		$out = $this->getDateStart()->toString('Y');
		return $out;
	}
	
	protected function generate()
	{
		if($this->subperiodsProcessed)
		{
			return;
		}
		parent::generate();
		
		$year = $this->date->get("Y");
		for($i=1; $i<=12; $i++)
		{
			$this->addSubperiod( new Piwik_Period_Month(
									Piwik_Date::factory("$year-$i-01")
								)
							);
		}
	}
	
	function toString()
	{
		if(!$this->subperiodsProcessed)
		{
			$this->generate();
		}
		$stringMonth = array();
		foreach($this->subperiods as $month)
		{
			$stringMonth[] = $month->get("Y")."-".$month->get("m")."-01";
		}
		return $stringMonth;
	}
}
