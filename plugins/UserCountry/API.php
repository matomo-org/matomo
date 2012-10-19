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
		$recordName = Piwik_UserCountry::VISITS_BY_COUNTRY_RECORD_NAME;
		$dataTable = $this->getDataTable($recordName, $idSite, $period, $date, $segment);
		
		// apply filter on the whole datatable in order the inline search to work (searches
		// are done on "beautiful" label)
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'code'));
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getFlagFromCode'));
		$dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_CountryTranslate'));
		$dataTable->queueFilter('AddConstantMetadata', array('logoWidth', 16));
		$dataTable->queueFilter('AddConstantMetadata', array('logoHeight', 11));
		
		return $dataTable;
	}
	
	public function getContinent( $idSite, $period, $date, $segment = false )
	{
		$recordName = Piwik_UserCountry::VISITS_BY_COUNTRY_RECORD_NAME;
		$dataTable = $this->getDataTable($recordName, $idSite, $period, $date, $segment);
		
		$getContinent = array('Piwik_Common', 'getContinent');
		$dataTable->filter('GroupBy', array('label', $getContinent));
		
		$dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_ContinentTranslate'));
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'code'));
		
		return $dataTable;
	}
	
	/**
	 * Returns visit information for every region with at least one visit.
	 * 
	 * @param int|string $idSite
	 * @param string $period
	 * @param string $date
	 * @param string|bool $segment
	 * @return Piwik_DataTable
	 */
	public function getRegion( $idSite, $period, $date, $segment = false )
	{
		$recordName = Piwik_UserCountry::VISITS_BY_REGION_RECORD_NAME;
		$dataTable = $this->getDataTable($recordName, $idSite, $period, $date, $segment);
		
		$separator = Piwik_UserCountry::LOCATION_SEPARATOR;
		$unk = Piwik_Tracker_Visit::UNKNOWN_CODE;
		
		// split the label and put the elements into the 'region' and 'country' metadata fields
		$dataTable->filter('ColumnCallbackAddMetadata',
			array('label', 'region', 'Piwik_UserCountry_getElementFromStringArray', array($separator, 0, $unk)));
		$dataTable->filter('ColumnCallbackAddMetadata',
			array('label', 'country', 'Piwik_UserCountry_getElementFromStringArray', array($separator, 1, $unk)));
		
		// add country name metadata
		$dataTable->filter('MetadataCallbackAddMetadata',
			array('country', 'country_name', 'Piwik_CountryTranslate', $applyToSummaryRow = false));
		
		// get the region name of each row and put it into the 'region_name' metadata
		$dataTable->filter('ColumnCallbackAddMetadata',
			array('label', 'region_name', 'Piwik_UserCountry_getRegionName', $params = null,
				  $applyToSummaryRow = false));
		
		// add the country flag as a url to the 'logo' metadata field
		$dataTable->filter('MetadataCallbackAddMetadata', array('country', 'logo', 'Piwik_getFlagFromCode'));
		
		// prettify the region label
		$dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_UserCountry_getPrettyRegionName'));
		
		$dataTable->queueFilter('ReplaceSummaryRowLabel');
		
		return $dataTable;
	}
	
	/**
	 * Returns visit information for every city with at least one visit.
	 * 
	 * @param int|string $idSite
	 * @param string $period
	 * @param string $date
	 * @param string|bool $segment
	 * @return Piwik_DataTable
	 */
	public function getCity( $idSite, $period, $date, $segment = false )
	{
		$recordName = Piwik_UserCountry::VISITS_BY_CITY_RECORD_NAME;
		$dataTable = $this->getDataTable($recordName, $idSite, $period, $date, $segment);
		
		$separator = Piwik_UserCountry::LOCATION_SEPARATOR;
		$unk = Piwik_Tracker_Visit::UNKNOWN_CODE;
		
		// split the label and put the elements into the 'city_name', 'region', 'country',
		// 'lat' & 'long' metadata fields
		$dataTable->filter('ColumnCallbackAddMetadata',
			array('label', 'city_name', 'Piwik_UserCountry_getElementFromStringArray',
				  array($separator, 0, Piwik_Translate('General_Unknown')) ));
		$dataTable->filter('ColumnCallbackAddMetadata',
			array('label', 'region', 'Piwik_UserCountry_getElementFromStringArray', array($separator, 1, $unk)));
		$dataTable->filter('ColumnCallbackAddMetadata',
			array('label', 'country', 'Piwik_UserCountry_getElementFromStringArray', array($separator, 2, $unk)));
		$dataTable->filter('ColumnCallbackAddMetadata',
			array('label', 'lat', 'Piwik_UserCountry_getElementFromStringArray', array($separator, 3)));
		$dataTable->filter('ColumnCallbackAddMetadata',
			array('label', 'long', 'Piwik_UserCountry_getElementFromStringArray', array($separator, 4)));
		
		// add country name & region name metadata
		$dataTable->filter('MetadataCallbackAddMetadata',
			array('country', 'country_name', 'Piwik_CountryTranslate', $applyToSummaryRow = false));
		
		$getRegionName = array('Piwik_UserCountry_LocationProvider_GeoIp', 'getRegionNameFromCodes');
		$dataTable->filter('MetadataCallbackAddMetadata', array(
			array('country', 'region'), 'region_name', $getRegionName, $applyToSummaryRow = false));
		
		// add the country flag as a url to the 'logo' metadata field
		$dataTable->filter('MetadataCallbackAddMetadata', array('country', 'logo', 'Piwik_getFlagFromCode'));
		
		// prettify the label
		$dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_UserCountry_getPrettyCityName'));
		
		$dataTable->queueFilter('ReplaceSummaryRowLabel');
		
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
