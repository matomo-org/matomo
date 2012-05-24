<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * @package Piwik
 * @subpackage Piwik_Period
 */
class Piwik_Period_Month extends Piwik_Period
{
	protected $label = 'month';

	public function getLocalizedShortString()
	{
		//"Aug 09"
		$out = $this->getDateStart()->getLocalized("%shortMonth% %longYear%");
		return $out;
	}

	public function getLocalizedLongString()
	{
		//"August 2009"
		$out = $this->getDateStart()->getLocalized(Piwik_Translate('CoreHome_LongMonthFormat'));
		return $out;
	}
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
}
