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
require_once "DataFiles/Countries.php";
require_once "UserCountry/functions.php";

/**
 * 
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry_API 
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
	
	public function getCountry( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserCountry_country', $idSite, $period, $date);
		// apply filter on the whole datatable in order the inline search to work (searches are done on "beautiful" label)
		$dataTable->filter('Piwik_DataTable_Filter_ColumnCallbackAddMetadata', array('label', 'code', create_function('$label', 'return $label;')));
		$dataTable->filter('Piwik_DataTable_Filter_ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getFlagFromCode'));
		$dataTable->filter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_CountryTranslate'));
		$dataTable->queueFilter('Piwik_DataTable_Filter_AddConstantMetadata', array('logoWidth', 18));
		$dataTable->queueFilter('Piwik_DataTable_Filter_AddConstantMetadata', array('logoHeight', 12));
		return $dataTable;
	}
	
	public function getContinent( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserCountry_continent', $idSite, $period, $date);
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackAddMetadata', array('label', 'code', create_function('$label', 'return $label;')));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_ContinentTranslate'));
		return $dataTable;
	}
	
	protected function getDataTable($name, $idSite, $period, $date)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getDataTable($name);
		$dataTable->filter('Piwik_DataTable_Filter_Sort', array(Piwik_Archive::INDEX_NB_VISITS));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ReplaceColumnNames');
		return $dataTable;
	}
	
	public function getNumberOfDistinctCountries($idSite, $period, $date)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		return $archive->getDataTableFromNumeric('UserCountry_distinctCountries');
	}
	
	
}
