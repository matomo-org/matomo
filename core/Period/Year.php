<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * @package Piwik
 * @subpackage Piwik_Period
 */
class Piwik_Period_Year extends Piwik_Period
{	
	protected $label = 'year';

	public function getLocalizedShortString()
	{
		return $this->getLocalizedLongString();
	}

	public function getLocalizedLongString()
	{
		//"2009"
		$out = $this->getDateStart()->getLocalized("%longYear%");
		return $out;
	}
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
