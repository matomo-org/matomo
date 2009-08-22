<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
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
			'name' => 'Visitors Country',
			'description' => 'Reports the Country of the visitors.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
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
		);
		return $hooks;
	}	
	
	function addWidgets()
	{
		Piwik_AddWidget( 'General_Visitors', 'UserCountry_WidgetContinents', 'UserCountry', 'getContinent');
		Piwik_AddWidget( 'General_Visitors', 'UserCountry_WidgetCountries', 'UserCountry', 'getCountry');
	}
	
	function addMenu()
	{
		Piwik_AddMenu('General_Visitors', 'UserCountry_SubmenuLocations', array('module' => 'UserCountry'));
	}
	
	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
		
		$dataTableToSum = array( 
				'UserCountry_country',
				'UserCountry_continent',
		);
		
		$nameToCount = $archiveProcessing->archiveDataTable($dataTableToSum);
		$archiveProcessing->insertNumericRecord('UserCountry_distinctCountries', 
												$nameToCount['UserCountry_country']['level0']);
	}
	
	function archiveDay($notification)
	{
		$archiveProcessing = $notification->getNotificationObject();
		$this->archiveDayAggregateVisits($archiveProcessing);
		$this->archiveDayAggregateGoals($archiveProcessing);
		$this->archiveDayRecordInDatabase($archiveProcessing);
	}
	
	protected function archiveDayAggregateVisits($archiveProcessing)
	{
		$labelSQL = "location_country";
		$this->interestByCountry = $archiveProcessing->getArrayInterestForLabel($labelSQL);
		
		$labelSQL = "location_continent";
		$this->interestByContinent = $archiveProcessing->getArrayInterestForLabel($labelSQL);
	}
	
	protected function archiveDayAggregateGoals($archiveProcessing)
	{
		$query = $archiveProcessing->queryConversionsBySegment("location_continent,location_country");
		while($row = $query->fetch() )
		{
			if(!isset($this->interestByCountry[$row['location_country']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->interestByCountry[$row['location_country']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow();
			if(!isset($this->interestByContinent[$row['location_continent']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->interestByContinent[$row['location_continent']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getNewGoalRow();
			$archiveProcessing->updateGoalStats($row, $this->interestByCountry[$row['location_country']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
			$archiveProcessing->updateGoalStats($row, $this->interestByContinent[$row['location_continent']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
		}
		$archiveProcessing->enrichConversionsByLabelArray($this->interestByCountry);
		$archiveProcessing->enrichConversionsByLabelArray($this->interestByContinent);
	}
	
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
