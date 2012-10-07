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
 * Base type for all GeoIP LocationProviders.
 * 
 * @package Piwik_UserCountry
 */
abstract class Piwik_UserCountry_LocationProvider_GeoIp extends Piwik_UserCountry_LocationProvider
{
	const GEOIP_DATABASE_DIR = 'files-geolocation';
	
	/**
	 * Stores possible database file names categorized by the type of information
	 * GeoIP databases hold.
	 * 
	 * @var array
	 */
	public static $dbNames = array(
		'loc' => array('GeoIPCity.dat', 'GeoLiteCity.dat', 'GeoIP.dat'),
		'isp' => array('GeoIPISP.dat'),
		'org' => array('GeoIPOrg.dat'),
	);
	
	/**
	 * Cached region name array. Data is from geoipregionvars.php.
	 * 
	 * @var array
	 */
	private static $regionNames = null;
	
	/**
	 * Attempts to fill in some missing information in a GeoIP location.
	 * 
	 * This method will call LocationProvider::completeLocationResult and then
	 * try to set the region name of the location if the country code & region
	 * code are set.
	 * 
	 * @param array $location The location information to modify.
	 */
	public function completeLocationResult( &$location )
	{
		parent::completeLocationResult($location);
		
		// set region name if region code is set
		if (empty($location[self::REGION_NAME_KEY])
			&& !empty($location[self::REGION_CODE_KEY])
			&& !empty($location[self::COUNTRY_CODE_KEY]))
		{
			$countryCode = $location[self::COUNTRY_CODE_KEY];
			$regionCode = (string)$location[self::REGION_CODE_KEY];
			$location[self::REGION_NAME_KEY] = self::getRegionNameFromCodes($countryCode, $regionCode);
		}
	}
	
	/**
	 * Returns a region name for a country code + region code.
	 * 
	 * @param string $countryCode
	 * @param string $regionCode
	 * @return string The region name or 'Unknown' (translated).
	 */
	public static function getRegionNameFromCodes( $countryCode, $regionCode )
	{
		$regionNames = self::getRegionNames();
		
		$countryCode = strtoupper($countryCode);
		$regionCode = strtoupper($regionCode);
		
		if (isset($regionNames[$countryCode][$regionCode]))
		{
			return $regionNames[$countryCode][$regionCode];
		}
		else
		{
			return Piwik_Translate('General_Unknown');
		}
	}
	
	/**
	 * Returns an array of region names mapped by country code & region code.
	 * 
	 * @return array
	 */
	public static function getRegionNames()
	{
		if (is_null(self::$regionNames))
		{
			require_once PIWIK_INCLUDE_PATH . '/libs/MaxMindGeoIP/geoipregionvars.php';
			self::$regionNames = $GEOIP_REGION_NAME;
		}
		
		return self::$regionNames;
	}

	/**
	 * Returns the path of an existing GeoIP database or false if none can be found.
	 * 
	 * @param array $possibleFileNames The list of possible file names for the GeoIP database.
	 * @return string|false
	 */
	public static function getPathToGeoIpDatabase( $possibleFileNames )
	{
		foreach ($possibleFileNames as $filename)
		{
			$path = PIWIK_INCLUDE_PATH.'/'.self::GEOIP_DATABASE_DIR.'/'.$filename;
			if (file_exists($path))
			{
				return $path;
			}
		}
		return false;
	}
}


/**
 * @see plugins/UserCountry/LocationProvider/GeoIp/ServerBased.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/LocationProvider/GeoIp/ServerBased.php';

/**
 * @see plugins/UserCountry/LocationProvider/GeoIp/Php.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/LocationProvider/GeoIp/Php.php';

/**
 * @see plugins/UserCountry/LocationProvider/GeoIp/Pecl.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/LocationProvider/GeoIp/Pecl.php';

