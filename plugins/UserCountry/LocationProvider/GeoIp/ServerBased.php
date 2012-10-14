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
 * To make this provider available, make sure the GEOIP_ADDR server
 * variable is set.
 * 
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry_LocationProvider_GeoIp_ServerBased extends Piwik_UserCountry_LocationProvider_GeoIp
{
	const ID = 'geoip_serverbased';
	const TITLE = 'GeoIP (%s)';
	const TEST_SERVER_VAR = 'GEOIP_ADDR';
	
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
		$myIP = Piwik_IP::getIpFromHeader();
		if ($info['ip'] != $myIP
			&& (!isset($info['disable_fallbacks'])
				|| !$info['disable_fallbacks']))
		{
			printDebug("The request is for IP address: ".$info['ip'] . " but your IP is: $myIP. GeoIP Server Module (apache/nginx) does not support this use case... ");
			$fallbacks = array(
				Piwik_UserCountry_LocationProvider_GeoIp_Pecl::ID,
				Piwik_UserCountry_LocationProvider_GeoIp_Php::ID
			);
			foreach ($fallbacks as $fallbackProviderId)
			{
				$otherProvider = Piwik_UserCountry_LocationProvider::getProviderById($fallbackProviderId);
				if ($otherProvider)
				{
					printDebug("Used $fallbackProviderId to detect this visitor IP");
					return $otherProvider->getLocation($info);
				}
			}
			printDebug("FAILED to lookup the geo location of this IP address, as no fallback location providers is configured. We recommend to configure Geolocation PECL module to fix this error.");
			
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
	 * Returns an array describing the types of location information this provider will
	 * return.
	 * 
	 * There's no way to tell exactly what database the HTTP server is using, so we just
	 * assume country and continent information is available. This can make diagnostics
	 * a bit more difficult, unfortunately.
	 * 
	 * @return array
	 */
	public function getSupportedLocationInfo()
	{
		$result = array();
		
		// assume country info is always available. it's an error if it's not.
		$result[self::COUNTRY_CODE_KEY] = true;
		$result[self::COUNTRY_NAME_KEY] = true;
		$result[self::CONTINENT_CODE_KEY] = true;
		$result[self::CONTINENT_NAME_KEY] = true;
		
		return $result;
	}
	
	/**
	 * Checks if an HTTP server module has been installed. It checks by looking for
	 * the GEOIP_ADDR server variable.
	 * 
	 * There's a special check for the Apache module, but we can't check specifically
	 * for anything else.
	 * 
	 * @return bool
	 */
	public function isAvailable()
	{
		// check if apache module is installed
		if (function_exists('apache_get_modules'))
		{
			foreach (apache_get_modules() as $name)
			{
				if (strpos($name, 'geoip') !== false)
				{
					return true;
				}
			}
		}
		
		return !empty($_SERVER[self::TEST_SERVER_VAR]);
	}
	
	/**
	 * Returns true if the GEOIP_ADDR server variable is defined.
	 * 
	 * @return bool
	 */
	public function isWorking()
	{
		if (empty($_SERVER[self::TEST_SERVER_VAR]))
		{
			return Piwik_Translate("UserCountry_CannotFindGeoIPServerVar", self::TEST_SERVER_VAR.' $_SERVER');
		}
		
		return true; // can't check for another IP
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
		if (function_exists('apache_note'))
		{
			$serverDesc = 'Apache';
		}
		else
		{
			$serverDesc = Piwik_Translate('UserCountry_HttpServerModule');
		}
		
		$title = sprintf(self::TITLE, $serverDesc);
		$desc = Piwik_Translate('UserCountry_GeoIpLocationProviderDesc_ServerBased1', array('<strong>', '</strong>'))
			  . '<br/><br/>'
			  . Piwik_Translate('UserCountry_GeoIpLocationProviderDesc_ServerBased2',
			  		array('<strong><em>', '</em></strong>', '<strong><em>', '</em></strong>'));
		$installDocs =
			  '<em><a href="http://piwik.org/faq/how-to/#faq_165">'
			. Piwik_Translate('UserCountry_HowToInstallApacheModule')
			. '</a></em><br/><em>'
			. '<a href="http://piwik.org/faq/how-to/#faq_166">'
			. Piwik_Translate('UserCountry_HowToInstallNginxModule')
			. '</a></em>';
		
		return array('id' => self::ID,
					  'title' => $title,
					  'description' => $desc,
					  'order' => 3,
					  'install_docs' => $installDocs);
	}
}
