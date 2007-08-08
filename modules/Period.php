<?php

abstract class Piwik_Period
{
	protected $subperiods = array();
	
	public function __construct(  )
	{	
	}
	
	protected function checkInputDate($date)
	{
		if( !($date instanceof Piwik_Date))
		{
			throw new Exception("The date must be a Piwik_Date object. " . var_export($date,true));
		}
	}
	
	public function getNumberOfSubperiods()
	{
		return count($this->subperiods);
	}
	
	/**
	 * Returns Period_Day for a period made of days (week, month),
	 * 			Period_Month for a period made of months (year) 
	 * 
	 */
	public function getSubperiods()
	{
		return $this->subperiods;
	}
		
	/**
	 * Add a date to the period.
	 * 
	 * Protected because it not yet supported to add periods after the initialization
	 * 
	 * @param Piwik_Date Valid Piwik_Date object
	 */
	protected function addSubperiod( $date )
	{
		$this->subperiods[] = $date;
	}
	
	/**
	 * A period is finished if all the subperiods are finished
	 */
	public function isFinished()
	{
		foreach($this->subperiods as $period)
		{
			if(!$period->isFinished())
			{
				return false;
			}
		}
		return true;
	}
	
	public function toString()
	{
		$dateString = array();
		foreach($this->subperiods as $period)
		{
			$dateString[] = $period->toString();
		}
		return $dateString;
	}
}

class Piwik_Period_Day extends Piwik_Period
{
	protected $date = null;
	
	public function __construct( $date )
	{
		parent::__construct();
		$this->checkInputDate( $date );
		$this->date = clone $date;
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
		return 1;
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

class Piwik_Period_Week extends Piwik_Period
{
	public function __construct( $date )
	{
		parent::__construct();
		$this->checkInputDate( $date );
		$this->generateWeekDates(clone $date);
	}
	
	private function generateWeekDates($date)
	{
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

class Piwik_Period_Month extends Piwik_Period
{
	public function __construct( $date )
	{
		parent::__construct();
		$this->checkInputDate( $date );
		$this->generateMonthDates(clone $date);
	}
	
	private function generateMonthDates($date)
	{
		$startMonth = $date->setDay(1);
		$currentDay = clone $startMonth;
		while($currentDay->compareMonth($startMonth) == 0)
		{
			$this->addSubperiod(new Piwik_Period_Day($currentDay));
			$currentDay = $currentDay->addDay(1);
		}
	}
}

class Piwik_Period_Year extends Piwik_Period
{	
	public function __construct( $date )
	{
		parent::__construct();
		$this->checkInputDate( $date );
		$this->generateMonthSubperiods( clone $date );
	}
	
	public function generateMonthSubperiods( $date )
	{
		$currentMonth = $date->getYear();
		
		for($i=1; $i<=12; $i++)
		{
			$this->addSubperiod( new Piwik_Period_Month( $currentMonth ) );
			$currentMonth = $currentMonth->addMonth(1);
		}
	}
	
	function toString()
	{
		$stringMonth = array();
		foreach($this->subperiods as $month)
		{
			$daysInMonth = $month->getSubperiods();
			$firstDay = $daysInMonth[0];
			$stringMonth[] = $firstDay->toString();
		}
		return $stringMonth;
	}
}




?>
