<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_UserCountry
 */

/**
 *
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry extends Piwik_Plugin
{
	const VISITS_BY_COUNTRY_RECORD_NAME = 'UserCountry_country';
	const VISITS_BY_REGION_RECORD_NAME = 'UserCountry_region';
	const VISITS_BY_CITY_RECORD_NAME = 'UserCountry_city';
	
	const DISTINCT_COUNTRIES_METRIC = 'UserCountry_distinctCountries';
	
	const UNKNOWN_CODE = 'xx';
	
	// separate region, city & country info in stored report labels
	const LOCATION_SEPARATOR = '|';
	
	public function getInformation()
	{
		$info = array(
			'description' => Piwik_Translate('UserCountry_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
			'TrackerPlugin' => true,
		);
		return $info;
	}

	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archivePeriod',
			'WidgetsList.add' => 'addWidgets',
			'Menu.add' => 'addMenu',
            'AdminMenu.add' => 'addAdminMenu',
			'Goals.getReportsWithGoalMetrics' => 'getReportsWithGoalMetrics',
			'API.getReportMetadata' => 'getReportMetadata',
			'API.getSegmentsMetadata' => 'getSegmentsMetadata',
			'AssetManager.getJsFiles' => 'getJsFiles',
			'Tracker.getVisitorLocation' => 'getVisitorLocation',
		);
		return $hooks;
	}

	/**
	 * @param Piwik_Event_Notification $notification  notification object
	 */
    public function getJsFiles( $notification )
    {
        $jsFiles = &$notification->getNotificationObject();

        $jsFiles[] = "plugins/UserCountry/templates/admin.js";
    }
    
	/**
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	public function getVisitorLocation( $notification )
	{
		$location = &$notification->getNotificationObject();
		$visitorInfo = $notification->getNotificationInfo();
		
		$id = Piwik_Common::getCurrentLocationProviderId();
		$provider = Piwik_UserCountry_LocationProvider::getProviderById($id);
		$location = $provider->getLocation($visitorInfo);
		
		// if we can't find a location, use default provider
		if ($location === false)
		{
			$provider = Piwik_UserCountry_LocationProvider::getProviderById(
				Piwik_UserCountry_LocationProvider_Default::ID);
			$location = $provider->getLocation($visitorInfo);
		}
	}
	
	function addWidgets()
	{
		$widgetContinentLabel = Piwik_Translate('UserCountry_WidgetLocation')
							  . ' ('.Piwik_Translate('UserCountry_Continent').')';
		$widgetCountryLabel = Piwik_Translate('UserCountry_WidgetLocation')
							. ' ('.Piwik_Translate('UserCountry_Country').')';
		$widgetRegionLabel = Piwik_Translate('UserCountry_WidgetLocation')
							. ' ('.Piwik_Translate('UserCountry_Region').')';
		$widgetCityLabel = Piwik_Translate('UserCountry_WidgetLocation')
							. ' ('.Piwik_Translate('UserCountry_City').')';
		
		Piwik_AddWidget( 'General_Visitors', $widgetContinentLabel, 'UserCountry', 'getContinent');
		Piwik_AddWidget( 'General_Visitors', $widgetCountryLabel, 'UserCountry', 'getCountry');
		Piwik_AddWidget('General_Visitors', $widgetRegionLabel, 'UserCountry', 'getVisitsByRegion');
		Piwik_AddWidget('General_Visitors', $widgetCityLabel, 'UserCountry', 'getVisitsByCity');
	}

	function addMenu()
	{
		Piwik_AddMenu('General_Visitors', 'UserCountry_SubmenuLocations', array('module' => 'UserCountry', 'action' => 'index'));
	}
	
	/**
	 * Event handler. Adds menu items to the Admin menu.
	 */
	function addAdminMenu()
	{
		if (Piwik::isUserIsSuperUser())
		{
			Piwik_AddAdminMenu('UserCountry_Geolocation',
							   array('module' => 'UserCountry', 'action' => 'adminIndex'),
		                       Piwik::isUserHasSomeAdminAccess(),
		                       $order = 8);
		}
	}

	/**
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	public function getSegmentsMetadata($notification)
	{
		$segments =& $notification->getNotificationObject();
		$segments[] = array(
			'type' => 'dimension',
			'category' => 'Visit',
			'name' => Piwik_Translate('UserCountry_Country'),
			'segment' => 'country',
			'sqlSegment' => 'log_visit.location_country',
			'acceptedValues' => 'de, us, fr, in, es, etc.',
		);
		$segments[] = array(
			'type' => 'dimension',
			'category' => 'Visit',
			'name' => Piwik_Translate('UserCountry_Continent'),
			'segment' => 'continent',
			'sqlSegment' => 'log_visit.location_country',
			'acceptedValues' => 'eur, asi, amc, amn, ams, afr, ant, oce',
			'sqlFilter' => array('Piwik_UserCountry', 'getCountriesForContinent'),
		);
		$segments[] = array(
			'type' => 'dimension',
			'category' => 'visit',
			'name' => Piwik_Translate('UserCountry_Region'),
			'segment' => 'region',
			'sqlSegment' => 'log_visit.location_region',
			'acceptedValues' => '01 02, OR, P8, etc.',
		);
		$segments[] = array(
			'type' => 'dimension',
			'category' => 'visit',
			'name' => Piwik_Translate('UserCountry_City'),
			'segment' => 'city',
			'sqlSegment' => 'log_visit.location_city',
			'acceptedValues' => 'Sydney, Sao Paolo, Rome, etc.',
		);
		$segments[] = array(
			'type' => 'dimension',
			'category' => 'visit',
			'name' => Piwik_Translate('UserCountry_Latitude'),
			'segment' => 'lat',
			'sqlSegment' => 'log_visit.location_latitude',
			'acceptedValues' => '-33.578, 40.830, etc.',
		);
		$segments[] = array(
			'type' => 'dimension',
			'category' => 'visit',
			'name' => Piwik_Translate('UserCountry_Longitude'),
			'segment' => 'long',
			'sqlSegment' => 'log_visit.location_longitude',
			'acceptedValues' => '-70.664, 14.326, etc.',
		);
	}

	/**
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	public function getReportMetadata($notification)
	{
		$metrics = array(
			'nb_visits' => Piwik_Translate('General_ColumnNbVisits'),
			'nb_uniq_visitors' => Piwik_Translate('General_ColumnNbUniqVisitors'),
			'nb_actions' => Piwik_Translate('General_ColumnNbActions'),
		);

		$reports = &$notification->getNotificationObject();

		$reports[] = array(
			'category' => Piwik_Translate('General_Visitors'),
			'name' => Piwik_Translate('UserCountry_Country'),
			'module' => 'UserCountry',
			'action' => 'getCountry',
			'dimension' => Piwik_Translate('UserCountry_Country'),
			'metrics' => $metrics,
			'order' => 5,
		);

		$reports[] = array(
			'category' => Piwik_Translate('General_Visitors'),
			'name' => Piwik_Translate('UserCountry_Continent'),
			'module' => 'UserCountry',
			'action' => 'getContinent',
			'dimension' => Piwik_Translate('UserCountry_Continent'),
			'metrics' => $metrics,
			'order' => 6,
		);
		
		$reports[] = array(
			'category' => Piwik_Translate('General_Visitors'),
			'name' => Piwik_Translate('UserCountry_Region'),
			'module' => 'UserCountry',
			'action' => 'getVisitsByRegion',
			'dimension' => Piwik_Translate('UserCountry_Region'),
			'metrics' => $metrics,
			'order' => 7,
		);
		
		$reports[] = array(
			'category' => Piwik_Translate('General_Visitors'),
			'name' => Piwik_Translate('UserCountry_City'),
			'module' => 'UserCountry',
			'action' => 'getVisitsByCity',
			'dimension' => Piwik_Translate('UserCountry_City'),
			'metrics' => $metrics,
			'order' => 8,
		);
	}

	/**
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	function getReportsWithGoalMetrics( $notification )
	{
		$dimensions =& $notification->getNotificationObject();
		$dimensions = array_merge($dimensions, array(
			array(	'category'  => Piwik_Translate('General_Visit'),
				'name'   => Piwik_Translate('UserCountry_Country'),
				'module' => 'UserCountry',
				'action' => 'getCountry',
			),
			array(	'category'  => Piwik_Translate('General_Visit'),
				'name'   => Piwik_Translate('UserCountry_Continent'),
				'module' => 'UserCountry',
				'action' => 'getContinent',
			),
			array('category' => Piwik_Translate('General_Visit'),
				  'name' => Piwik_Translate('UserCountry_Region'),
				  'module' => 'UserCountry',
				  'action' => 'getVisitsByRegion'),
			array('category' => Piwik_Translate('General_Visit'),
				  'name' => Piwik_Translate('UserCountry_City'),
				  'module' => 'UserCountry',
				  'action' => 'getVisitsByCity'),
		));
	}

	/**
	 * @param Piwik_Event_Notification $notification  notification object
	 * @return mixed
	 */
	function archivePeriod( $notification )
	{
		/**
		 * @param Piwik_ArchiveProcessing_Period  $archiveProcessing
		 */
		$archiveProcessing = $notification->getNotificationObject();

		if(!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

		$dataTableToSum = array(
			self::VISITS_BY_COUNTRY_RECORD_NAME,
			self::VISITS_BY_REGION_RECORD_NAME,
			self::VISITS_BY_CITY_RECORD_NAME,
		);

		$nameToCount = $archiveProcessing->archiveDataTable($dataTableToSum);
		$archiveProcessing->insertNumericRecord(self::DISTINCT_COUNTRIES_METRIC,
												$nameToCount[self::VISITS_BY_COUNTRY_RECORD_NAME]['level0']);
	}

	private $interestTables = null;
	
	/**
	 * @param Piwik_Event_Notification $notification  notification object
	 * @return mixed
	 */
	function archiveDay($notification)
	{
		/**
		 * @var Piwik_ArchiveProcessing
		 */
		$archiveProcessing = $notification->getNotificationObject();

		if(!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

		$this->interestTables = array('location_country' => array(),
									  'location_region' => array(),
									  'location_city' => array());
		
		$this->archiveDayAggregateVisits($archiveProcessing);
		$this->archiveDayAggregateGoals($archiveProcessing);
		$this->archiveDayRecordInDatabase($archiveProcessing);
		
		unset($this->interestTables);
	}
	
	/**
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 */
	protected function archiveDayAggregateVisits($archiveProcessing)
	{
		$dimensions = array_keys($this->interestTables);
		$query = $archiveProcessing->queryVisitsByDimension($dimensions);
		
		if ($query === false)
		{
			return;
		}
		
		$emptyInterestColumns = $archiveProcessing->getNewInterestRow();
		while ($row = $query->fetch())
		{
			// make sure regions & cities w/ the same name don't get merged
			$this->setLongCityRegionId($row);
			
			// add the stats to each dimension's table
			foreach ($this->interestTables as $dimension => &$table)
			{
				$label = (string)$row[$dimension];
				
				if (!isset($table[$label]))
				{
					$table[$label] = $archiveProcessing->getNewInterestRow();
				}
				$archiveProcessing->updateInterestStats($row, $table[$label]);
			}
		}
	}

	/**
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 */
	protected function archiveDayAggregateGoals($archiveProcessing)
	{
		$dimensions = array_keys($this->interestTables);
		$query = $archiveProcessing->queryConversionsByDimension($dimensions);

		if ($query === false)
		{
			return;
		}

		while ($row = $query->fetch())
		{
			// make sure regions & cities w/ the same name don't get merged
			$this->setLongCityRegionId($row);
			
			$idGoal = $row['idgoal'];
			foreach ($this->interestTables as $dimension => &$table)
			{
				$label = (string)$row[$dimension];
				
				if (!isset($table[$label][Piwik_Archive::INDEX_GOALS][$idGoal]))
				{
					$table[$label][Piwik_Archive::INDEX_GOALS][$idGoal] = $archiveProcessing->getNewGoalRow($idGoal);
				}
				
				$archiveProcessing->updateGoalStats($row, $table[$label][Piwik_Archive::INDEX_GOALS][$idGoal]);
			}
		}
		
		foreach ($this->interestTables as &$table)
		{
			$archiveProcessing->enrichConversionsByLabelArray($table);
		}
	}

	/**
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 */
	protected function archiveDayRecordInDatabase($archiveProcessing)
	{
		$maximumRows = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
		
		$tableCountry = Piwik_ArchiveProcessing_Day::getDataTableFromArray($this->interestTables['location_country']);
		$archiveProcessing->insertBlobRecord(self::VISITS_BY_COUNTRY_RECORD_NAME, $tableCountry->getSerialized());
		$archiveProcessing->insertNumericRecord(self::DISTINCT_COUNTRIES_METRIC, $tableCountry->getRowsCount());
		destroy($tableCountry);

		$tableRegion = Piwik_ArchiveProcessing_Day::getDataTableFromArray($this->interestTables['location_region']);
		$serialized = $tableRegion->getSerialized($maximumRows, $maximumRows, Piwik_Archive::INDEX_NB_VISITS);
		$archiveProcessing->insertBlobRecord(self::VISITS_BY_REGION_RECORD_NAME, $serialized);
		destroy($tableRegion);
		
		$tableCity = Piwik_ArchiveProcessing_Day::getDataTableFromArray($this->interestTables['location_city']);
		$serialized = $tableCity->getSerialized($maximumRows, $maximumRows, Piwik_Archive::INDEX_NB_VISITS);
		$archiveProcessing->insertBlobRecord(self::VISITS_BY_CITY_RECORD_NAME, $serialized);
		destroy($tableCity);
	}
	
	/**
	 * Makes sure the region and city of a query row are unique.
	 * 
	 * @param array $row
	 */
	private function setLongCityRegionId( &$row )
	{
		static $locationColumns = array('location_region', 'location_country', 'location_city');
		
		// to be on the safe side, remove the location separator from the region/city/country we
		// get from the query
		foreach ($locationColumns as $column)
		{
			$row[$column] = str_replace(self::LOCATION_SEPARATOR, '', $row[$column]);
		}
		
		$row['location_region'] = $row['location_region'].self::LOCATION_SEPARATOR.$row['location_country'];
		$row['location_city'] = $row['location_city'].self::LOCATION_SEPARATOR.$row['location_region'];
	}
	
	/**
	 * Returns a list of country codes for a given continent code.
	 * 
	 * @param string $continent The continent code.
	 * @return array
	 */
	public static function getCountriesForContinent( $continent )
	{
		$continent = strtolower($continent);
		
		$result = array();
		foreach (Piwik_Common::getCountriesList() as $countryCode => $continentCode)
		{
			if ($continent == $continentCode)
			{
				$result[] = $countryCode;
			}
		}
		return $result;
	}
}
