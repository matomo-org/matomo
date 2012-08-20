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
	public function getInformation()
	{
		$info = array(
			'description' => Piwik_Translate('UserCountry_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
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
			'Goals.getReportsWithGoalMetrics' => 'getReportsWithGoalMetrics',
			'API.getReportMetadata' => 'getReportMetadata',
			'API.getSegmentsMetadata' => 'getSegmentsMetadata',
		);
		return $hooks;
	}

	function addWidgets()
	{
		$widgetContinentLabel = Piwik_Translate('UserCountry_WidgetLocation')
							  . ' ('.Piwik_Translate('UserCountry_Continent').')';
		$widgetCountryLabel = Piwik_Translate('UserCountry_WidgetLocation')
							. ' ('.Piwik_Translate('UserCountry_Country').')';
		
		Piwik_AddWidget( 'General_Visitors', $widgetContinentLabel, 'UserCountry', 'getContinent');
		Piwik_AddWidget( 'General_Visitors', $widgetCountryLabel, 'UserCountry', 'getCountry');
	}

	function addMenu()
	{
		Piwik_AddMenu('General_Visitors', 'UserCountry_SubmenuLocations', array('module' => 'UserCountry', 'action' => 'index'));
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
			'sqlSegment' => 'log_visit.location_continent',
			'acceptedValues' => 'eur, asi, amc, amn, ams, afr, ant, oce',
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
			'UserCountry_country',
			'UserCountry_continent',
		);

		$nameToCount = $archiveProcessing->archiveDataTable($dataTableToSum);
		$archiveProcessing->insertNumericRecord('UserCountry_distinctCountries',
												$nameToCount['UserCountry_country']['level0']);
	}

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

		$this->archiveDayAggregateVisits($archiveProcessing);
		$this->archiveDayAggregateGoals($archiveProcessing);
		$this->archiveDayRecordInDatabase($archiveProcessing);
	}

	/**
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 */
	protected function archiveDayAggregateVisits($archiveProcessing)
	{
		$labelSQL = "log_visit.location_country";
		$this->interestByCountry = $archiveProcessing->getArrayInterestForLabel($labelSQL);

		$labelSQL = "log_visit.location_continent";
		$this->interestByContinent = $archiveProcessing->getArrayInterestForLabel($labelSQL);
	}

	/**
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 */
	protected function archiveDayAggregateGoals($archiveProcessing)
	{
		$query = $archiveProcessing->queryConversionsByDimension(array("location_continent","location_country"));

		if($query === false) return;

		while($row = $query->fetch() )
		{
			if(!isset($this->interestByCountry[$row['location_country']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->interestByCountry[$row['location_country']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow($row['idgoal']);
			if(!isset($this->interestByContinent[$row['location_continent']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->interestByContinent[$row['location_continent']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow($row['idgoal']);
			$archiveProcessing->updateGoalStats($row, $this->interestByCountry[$row['location_country']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
			$archiveProcessing->updateGoalStats($row, $this->interestByContinent[$row['location_continent']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
		}
		$archiveProcessing->enrichConversionsByLabelArray($this->interestByCountry);
		$archiveProcessing->enrichConversionsByLabelArray($this->interestByContinent);
	}

	/**
	 * @param Piwik_ArchiveProcessing_Day $archiveProcessing
	 */
	protected function archiveDayRecordInDatabase($archiveProcessing)
	{
		$tableCountry = $archiveProcessing->getDataTableFromArray($this->interestByCountry);
		$archiveProcessing->insertBlobRecord('UserCountry_country', $tableCountry->getSerialized());
		$archiveProcessing->insertNumericRecord('UserCountry_distinctCountries', $tableCountry->getRowsCount());
		destroy($tableCountry);

		$tableContinent = $archiveProcessing->getDataTableFromArray($this->interestByContinent);
		$archiveProcessing->insertBlobRecord('UserCountry_continent', $tableContinent->getSerialized());
		destroy($tableContinent);
	}
}
