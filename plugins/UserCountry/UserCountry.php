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
			'name' => 'UserCountry',
			'description' => 'UserCountry',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
			'translationAvailable' => true,
		);
		
		return $info;
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

require_once "ViewDataTable.php";
class Piwik_UserCountry_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('UserCountry/index.tpl');
		
		/* User Country */
		$view->urlSparklineCountries = $this->getUrlSparkline('getLastDistinctCountriesGraph');
		$view->numberDistinctCountries = $this->getNumberOfDistinctCountries(true);
		
		$view->dataTableCountry = $this->getCountry(true);
		$view->dataTableContinent = $this->getContinent(true);
		
		echo $view->render();
	}
	
	/**
	 * User Country
	 */
	function getCountry( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( 'UserCountry', __FUNCTION__, "UserCountry.getCountry" );
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 1 );
		$view->disableSearchBox();
		
		// sorting by label is not correct as the labels are the ISO codes before being
		// mapped to the country names
//		$view->disableSort();
		$view->setLimit( 5 );
		
		return $this->renderView($view, $fetch);
	}

	function getNumberOfDistinctCountries( $fetch = false)
	{
		return $this->getNumericValue('UserCountry.getNumberOfDistinctCountries');
	}

	function getLastDistinctCountriesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph('UserCountry',__FUNCTION__, "UserCountry.getNumberOfDistinctCountries");
		return $this->renderView($view, $fetch);
	}
	
	function getContinent( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( 'UserCountry', __FUNCTION__, "UserCountry.getContinent" );
		$view->disableExcludeLowPopulation();
		$view->disableSearchBox();
		$view->disableOffsetInformation();
		$view->disableSort();
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 1 );
		
		return $this->renderView($view, $fetch);
	}
	
}

Piwik_AddWidget( 'UserCountry', 'getContinent', 'Visitor continents');
Piwik_AddWidget( 'UserCountry', 'getCountry', 'Visitor countries');

Piwik_AddMenu('Visitors', 'Locations', array('module' => 'UserCountry'));

