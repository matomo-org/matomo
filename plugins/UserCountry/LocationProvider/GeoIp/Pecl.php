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
 * A LocationProvider that uses the PECL implementation of GeoIP.
 * 
 * FIXME: For some reason, if the PECL module is loaded & an organization DB is available, the PHP
 * module won't return organization info. If the PECL module is not loaded, organization info is returned.
 * 
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry_LocationProvider_GeoIp_Pecl extends Piwik_UserCountry_LocationProvider_GeoIp
{
	const ID = 'geoip_pecl';
	const TITLE = 'GeoIp (PECL)';
	
	/**
	 * Uses the GeoIP PECL module to get a visitor's location based on their IP address.
	 * 
	 * This function will return different results based on the data available. If a city
	 * database can be detected by the PECL module, it may return the country code,
	 * region code, city name, area code, latitude, longitude and postal code of the visitor.
	 * 
	 * Alternatively, if only the country database can be detected, only the country code
	 * will be returned.
	 * 
	 * The GeoIP PECL module will detect the following filenames:
	 * - GeoIP.dat
	 * - GeoIPCity.dat
	 * - GeoIPISP.dat
	 * - GeoIPOrg.dat
	 * 
	 * Note how GeoLiteCity.dat, the name for the GeoLite city database, is not detected
	 * by the PECL module.
	 * 
	 * @param array $info Must have an 'ip' field.
	 * @return array
	 */
	public function getLocation( $info )
	{
		$ip = $info['ip'];
		
		$result = array();
		
		// get location data
		if (self::isCityDatabaseAvailable())
		{
			$location = @geoip_record_by_name($ip);
			if (!empty($location))
			{
				$result[self::COUNTRY_CODE_KEY] = $location['country_code'];
				$result[self::REGION_CODE_KEY] = $location['region'];
				$result[self::CITY_NAME_KEY] = utf8_encode($location['city']);
				$result[self::AREA_CODE_KEY] = $location['area_code'];
				$result[self::LATITUDE_KEY] = $location['latitude'];
				$result[self::LONGITUDE_KEY] = $location['longitude'];
				$result[self::POSTAL_CODE_KEY] = $location['postal_code'];
			}
		}
		else if (self::isRegionDatabaseAvailable())
		{
			$location = @geoip_region_by_name($ip);
			if (!empty($location))
			{
				$result[self::REGION_CODE_KEY] = $location['region'];
				$result[self::COUNTRY_CODE_KEY] = $location['country_code'];
			}
		}
		else
		{
			$result[self::COUNTRY_CODE_KEY] = @geoip_country_code_by_name($ip);
		}
		
		// get organization data if the org database is available
		if (self::isOrgDatabaseAvailable())
		{
			$org = @geoip_org_by_name($ip);
			if ($org !== false)
			{
				$result[self::ORG_KEY] = utf8_encode($org);
			}
		}
		
		// get isp data if the isp database is available
		if (self::isISPDatabaseAvailable())
		{
			$isp = @geoip_isp_by_name($ip);
			if ($ip !== false)
			{
				$result[self::ISP_KEY] = utf8_encode($isp);
			}
		}
		
		if (empty($result))
		{
			return false;
		}
		
		$this->completeLocationResult($result);
		return $result;
	}
	
	/**
	 * Returns true if the PECL module is installed and loaded, false if otherwise.
	 * 
	 * @return bool
	 */
	public function isAvailable()
	{
		return function_exists('geoip_db_avail');
	}
	
	/**
	 * Returns true if the PECL module that is installed can be successfully used
	 * to get the location of an IP address.
	 * 
	 * @return bool
	 */
	public function isWorking()
	{
		// if no no location database is available, this implementation is not setup correctly
		if (!self::isLocationDatabaseAvailable())
		{
			return Piwik_Translate('UserCountry_CannotFindPeclGeoIPDb');
		}
		
		// try getting a location as last check
		try
		{
			$this->getLocation(array('ip' => Piwik_IP::getIpFromHeader()));
		}
		catch (Exception $ex)
		{
			return $ex->getMessage();
		}
		
		return true;
	}
	
	/**
	 * Returns information about this location provider. Contains an id, title & description:
	 * 
	 * array(
	 *     'id' => 'geoip_pecl',
	 *     'title' => '...',
	 *     'description' => '...'
	 * );
	 * 
	 * @return array
	 */
	public function getInfo()
	{
		$desc = Piwik_Translate('UserCountry_GeoIpLocationProviderDesc_Pecl1') . '<br/><br/>'
			  . Piwik_Translate('UserCountry_GeoIpLocationProviderDesc_Pecl2');
		return array('id' => self::ID, 'title' => self::TITLE, 'description' => $desc);
	}
	
	/**
	 * Returns true if the PECL module can detect a location database (either a country,
	 * region or city will do).
	 * 
	 * @return bool
	 */
	public static function isLocationDatabaseAvailable()
	{
		return self::isCityDatabaseAvailable()
			|| self::isRegionDatabaseAvailable()
			|| self::isCountryDatabaseAvailable();
	}
	
	/**
	 * Returns true if the PECL module can detect a city database.
	 * 
	 * @return bool
	 */
	public static function isCityDatabaseAvailable()
	{
		return geoip_db_avail(GEOIP_CITY_EDITION_REV0)
			|| geoip_db_avail(GEOIP_CITY_EDITION_REV1);
	}
	
	/**
	 * Returns true if the PECL module can detect a region database.
	 * 
	 * @return bool
	 */
	public static function isRegionDatabaseAvailable()
	{
		return geoip_db_avail(GEOIP_REGION_EDITION_REV0)
			|| geoip_db_avail(GEOIP_REGION_EDITION_REV1);
	}
	
	/**
	 * Returns true if the PECL module can detect a country database.
	 * 
	 * @return bool
	 */
	public static function isCountryDatabaseAvailable()
	{
		return geoip_db_avail(GEOIP_COUNTRY_EDITION);
	}
	
	/**
	 * Returns true if the PECL module can detect an organization database.
	 * 
	 * @return bool
	 */
	public static function isOrgDatabaseAvailable()
	{
		return geoip_db_avail(GEOIP_ORG_EDITION);
	}
	
	/**
	 * Returns true if the PECL module can detect an ISP database.
	 * 
	 * @return bool
	 */
	public static function isISPDatabaseAvailable()
	{
		return geoip_db_avail(GEOIP_ISP_EDITION);
	}
}
