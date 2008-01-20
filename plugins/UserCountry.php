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
	public function __construct()
	{
		parent::__construct();
	}

	public function getInformation()
	{
		$info = array(
			'name' => 'UserCountry',
			'description' => 'UserCountry',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
			'translationAvailable' => true,
		);
		
		return $info;
	}
	
	function install()
	{
		// add column hostname / hostname ext in the visit table
	}
	
	function uninstall()
	{
		// add column hostname / hostname ext in the visit table
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archiveMonth',
		);
		return $hooks;
	}
	
	
	function archiveMonth( $notification )
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
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tableCountry->getSerialized());

//		echo $tableCountry;
		
		$recordName = 'UserCountry_continent';
		$labelSQL = "location_continent";
		$tableContinent = $archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tableContinent->getSerialized());
//		echo $tableContinent;
//		Piwik::printMemoryUsage("End of ".get_class($this)." "); 
	}
}