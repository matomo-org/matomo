<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
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
		Piwik_AddWidget( 'UserCountry', 'getContinent', Piwik_Translate('UserCountry_WidgetContinents'));
		Piwik_AddWidget( 'UserCountry', 'getCountry', Piwik_Translate('UserCountry_WidgetCountries'));
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
		$record = new Piwik_ArchiveProcessing_Record_Numeric(
												'UserCountry_distinctCountries', 
												$nameToCount['UserCountry_country']['level0']
											);
	}
	
	function archiveDay($notification)
	{
		$archiveProcessing = $notification->getNotificationObject();
		
		$recordName = 'UserCountry_country';
		$labelSQL = "location_country";
		$tableCountry = $archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_ArchiveProcessing_Record_Numeric('UserCountry_distinctCountries', $tableCountry->getRowsCount());
		$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $tableCountry->getSerialized());

		$recordName = 'UserCountry_continent';
		$labelSQL = "location_continent";
		$tableContinent = $archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $tableContinent->getSerialized());
	}
}

