<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 */

class Piwik_ExampleUI_API 
{
	static private $instance = null;
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	function getTemperaturesEvolution($date, $period)
	{
		$period = new Piwik_Period_Range($period, 'last30');
		$dateStart = $period->getDateStart()->get('Y-m-d'); // eg. "2009-04-01"
		$dateEnd = $period->getDateEnd()->get('Y-m-d'); // eg. "2009-04-30"
		
		// here you could select from your custom table in the database, eg.
		$query = "SELECT AVG(temperature)
					FROM server_temperatures
					WHERE date > ?
						AND date < ?
					GROUP BY date
					ORDER BY date ASC";
		//$result = Piwik_FetchAll($query, array($dateStart, $dateEnd));
		// to keep things simple, we generate the data
		foreach($period->getSubperiods() as $subPeriod)
		{
			$server1 = rand(50,90);
			$server2 = rand(40, 110);
			$value = array('server1' => $server1, 'server2' => $server2);
			$temperatures[$subPeriod->getLocalizedShortString()] = $value;
		}
		
		// convert this array to a DataTable object
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($temperatures);
		return $dataTable;
	}
	
	// we generate an array of random server temperatures
	function getTemperatures()
	{
		$xAxis = array(
			'12AM', '1AM', '2AM', '3AM', '4AM', '5AM', '6AM', '7AM', '8AM', '9AM', '10AM', '11AM', 
			'12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM', '11PM',
		);
		$temperatureValues = array_slice(range(50,90), 0, count($xAxis));
		shuffle($temperatureValues);
		$temperatures = array();
		foreach($xAxis as $i => $xAxisLabel) {
			$temperatures[$xAxisLabel] = $temperatureValues[$i];
		}
		
		// convert this array to a DataTable object
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($temperatures);
		return $dataTable;
	}
	
	function getPlanetRatios()
	{
		$planetRatios = array(
			'Mercury' => 0.382,
			'Venus' => 0.949,
			'Earth' => 1.00,
			'Mars' => 0.532,	
			'Jupiter' => 11.209,
			'Saturn' => 9.449,
			'Uranus' => 4.007,
			'Neptune' => 3.883,
		);
		// convert this array to a DataTable object
		$dataTable = new Piwik_DataTable();
		$dataTable->addRowsFromArrayWithIndexLabel($planetRatios);
		return $dataTable;
	}
	
	function getPlanetRatiosWithLogos()
	{
		$planetsDataTable = $this->getPlanetRatios();
		foreach($planetsDataTable->getRows() as $row)
		{
			$row->addMetadata('logo', "plugins/ExampleUI/images/icons-planet/".strtolower($row->getColumn('label').".png"));
			$row->addMetadata('url', "http://en.wikipedia.org/wiki/".$row->getColumn('label'));
		}
		return $planetsDataTable;
	}
}