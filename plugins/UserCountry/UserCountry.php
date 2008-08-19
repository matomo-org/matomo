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
	
	public function postLoad()
	{
		Piwik_AddWidget( 'UserCountry', 'getContinent', Piwik_Translate('UserCountry_WidgetContinents'));
		Piwik_AddWidget( 'UserCountry', 'getCountry', Piwik_Translate('UserCountry_WidgetCountries'));

		Piwik_AddMenu('General_Visitors', 'UserCountry_SubmenuLocations', array('module' => 'UserCountry'));
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archivePeriod',
		);
		return $hooks;
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

//		echo $tableCountry;
		
		$recordName = 'UserCountry_continent';
		$labelSQL = "location_continent";
		$tableContinent = $archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $tableContinent->getSerialized());
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
		
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors') );
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
		$view = Piwik_ViewDataTable::factory( 'graphVerticalBar' );
		$view->init( 'UserCountry', __FUNCTION__, "UserCountry.getContinent" );
		$view->disableExcludeLowPopulation();
		$view->disableSearchBox();
		$view->disableOffsetInformation();
		$view->disableSort();
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors') );
		$view->setSortedColumn( 1 );
		
		return $this->renderView($view, $fetch);
	}
	
}

