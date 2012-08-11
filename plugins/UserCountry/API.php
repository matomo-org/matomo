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
 * @see plugins/UserCountry/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

/**
 * The UserCountry API lets you access reports about your visitors' Countries and Continents.
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry_API 
{
	static private $instance = null;
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	public function getCountry( $idSite, $period, $date, $segment = false )
	{
		$dataTable = $this->getDataTable('UserCountry_country', $idSite, $period, $date, $segment);
		// apply filter on the whole datatable in order the inline search to work (searches are done on "beautiful" label)
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'code', create_function('$label', 'return $label;')));
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getFlagFromCode'));
		$dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_CountryTranslate'));
		$dataTable->queueFilter('AddConstantMetadata', array('logoWidth', 16));
		$dataTable->queueFilter('AddConstantMetadata', array('logoHeight', 11));
		return $dataTable;
	}
	
	public function getContinent( $idSite, $period, $date, $segment = false )
	{
		$dataTable = $this->getDataTable('UserCountry_continent', $idSite, $period, $date, $segment);
		$dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_ContinentTranslate'));
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'code', create_function('$label', 'return $label;')));
		return $dataTable;
	}
	
	protected function getDataTable($name, $idSite, $period, $date, $segment)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date, $segment );
		$dataTable = $archive->getDataTable($name);
		$dataTable->filter('Sort', array(Piwik_Archive::INDEX_NB_VISITS));
		$dataTable->queueFilter('ReplaceColumnNames');
		return $dataTable;
	}
	
	public function getNumberOfDistinctCountries($idSite, $period, $date, $segment = false)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date, $segment );
		return $archive->getDataTableFromNumeric('UserCountry_distinctCountries');
	}
}
