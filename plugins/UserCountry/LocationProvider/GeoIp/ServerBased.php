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
 * A LocationProvider that uses an GeoIP module installed in an HTTP Server.
 * 
 * To make this provider available, make sure the GEOIP_COUNTRY_CODE server
 * variable is set.
 * 
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry_LocationProvider_GeoIp_ServerBased extends Piwik_UserCountry_LocationProvider_GeoIp
{
	const ID = 'geoip_serverbased';
	const TITLE = 'GeoIp (%s)';
	
	private static $geoIpServerVars = array(
		parent::COUNTRY_CODE_KEY => 'GEOIP_COUNTRY_CODE',
		parent::COUNTRY_NAME_KEY => 'GEOIP_COUNTRY_NAME',
		parent::REGION_CODE_KEY => 'GEOIP_REGION',
		parent::REGION_NAME_KEY => 'GEOIP_REGION_NAME',
		parent::CITY_NAME_KEY => 'GEOIP_CITY',
		parent::AREA_CODE_KEY => 'GEOIP_AREA_CODE',
		parent::LATITUDE_KEY => 'GEOIP_LATITUDE',
		parent::LONGITUDE_KEY => 'GEOIP_LONGITUDE',
		parent::POSTAL_CODE_KEY => 'GEOIP_POSTAL_CODE',
		parent::ISP_KEY => 'GEOIP_ISP',
		parent::ORG_KEY => 'GEOIP_ORGANIZATION',
	);
	
	/**
	 * Uses a GeoIP database to get a visitor's location based on their IP address.
	 * 
	 * This function will return different results based on the data used and based
	 * on how the GeoIP module is configured.
	 * 
	 * If a region database is used, it may return the country code, region code,
	 * city name, area code, latitude, longitude and postal code of the visitor.
	 * 
	 * Alternatively, only the country code may be returned for another database.
	 * 
	 * If your HTTP server is not configured to include all GeoIP information, some
	 * information will not be available to Piwik.
	 * 
	 * @param array $info Must have an 'ip' field.
	 * @return array
	 */
	public function getLocation( $info )
	{
		// geoip modules that are built into servers can't use a forced IP. in this case we try
		// to fallback to another version.
		if ($info['ip'] != $_SERVER['REMOTE_ADDR'])
		{
			$fallbacks = array(
				Piwik_UserCountry_LocationProvider_GeoIp_Pecl::ID,
				Piwik_UserCountry_LocationProvider_GeoIp_Php::ID
			);
			foreach ($fallbacks as $fallbackProviderId)
			{
				$otherProvider = Piwik_UserCountry_LocationProvider::getProviderById($fallbackProviderId);
				if ($otherProvider)
				{
					return $otherProvider->getLocation($info);
				}
			}
			
			return false;
		}
		
		$result = array();
		foreach (self::$geoIpServerVars as $resultKey => $geoipVarName)
		{
			if (!empty($_SERVER[$geoipVarName]))
			{
				$result[$resultKey] = $_SERVER[$geoipVarName];
			}
		}
		$this->completeLocationResult($result);
		return $result;
	}
	
	/**
	 * Checks if an HTTP server module has been installed. It checks by looking for
	 * the GEOIP_COUNTRY_CODE server variable.
	 * 
	 * @return bool
	 */
	public function isAvailable()
	{
		if (!empty($_SERVER['GEOIP_COUNTRY_CODE']))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Returns true. There is no way to tell if this provider is broken.
	 * 
	 * @return true
	 */
	public function isWorking()
	{
		return true;
	}
	
	/**
	 * Returns information about this location provider. Contains an id, title & description:
	 * 
	 * array(
	 *     'id' => 'geoip_serverbased',
	 *     'title' => '...',
	 *     'description' => '...'
	 * );
	 * 
	 * @return array
	 */
	public function getInfo()
	{
		$title = sprintf(self::TITLE, Piwik_Translate('Piwik_UserCountry_HttpServerModule'));
		$desc = Piwik_Translate('UserCountry_GeoIpLocationProviderDesc_ServerBased1', array('<strong>', '</strong>'))
			  . '<br/><br/>'
			  . Piwik_Translate('UserCountry_GeoIpLocationProviderDesc_ServerBased2',
			  		array('<strong><em>', '</em></strong>', '<strong><em>', '</em></strong>'));
		
		return array('id' => self::ID, 'title' => $title, 'description' => $desc);
	}
}
