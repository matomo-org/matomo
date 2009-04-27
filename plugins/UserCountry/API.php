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
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'code', create_function('$label', 'return $label;')));
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getFlagFromCode'));
		$dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_CountryTranslate'));
		$dataTable->queuefilter('AddConstantMetadata', array('logoWidth', 18));
		$dataTable->queuefilter('AddConstantMetadata', array('logoHeight', 12));
		return $dataTable;
	}
	
	public function getContinent( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserCountry_continent', $idSite, $period, $date);
		$dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_ContinentTranslate'));
		$dataTable->queuefilter('ColumnCallbackAddMetadata', array('label', 'code', create_function('$label', 'return $label;')));
		return $dataTable;
	}
	
	protected function getDataTable($name, $idSite, $period, $date)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getDataTable($name);
		$dataTable->filter('Sort', array(Piwik_Archive::INDEX_NB_VISITS));
		$dataTable->queuefilter('ReplaceColumnNames');
		return $dataTable;
	}
	
	public function getNumberOfDistinctCountries($idSite, $period, $date)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		return $archive->getNumeric('UserCountry_distinctCountries');
	}
	
	
}
