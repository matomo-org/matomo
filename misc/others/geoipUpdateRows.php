<?php
ini_set("memory_limit", "512M");
error_reporting(E_ALL|E_NOTICE);

define('PIWIK_DOCUMENT_ROOT', dirname(__FILE__)=='/'?'':dirname(__FILE__) .'/../..');
if(file_exists(PIWIK_DOCUMENT_ROOT . '/bootstrap.php'))
{
	require_once PIWIK_DOCUMENT_ROOT . '/bootstrap.php';
}
if(!defined('PIWIK_USER_PATH'))
{
	define('PIWIK_USER_PATH', PIWIK_DOCUMENT_ROOT);
}
if(!defined('PIWIK_INCLUDE_PATH'))
{
	define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
}

ignore_user_abort(true);
set_time_limit(0);
@date_default_timezone_set('UTC');

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH . '/core/testMinimumPhpVersion.php';
require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';

$GLOBALS['PIWIK_TRACKER_DEBUG'] = false;
define('PIWIK_ENABLE_DISPATCH', false);

Piwik_Config::getInstance()->log['logger_message'][] = 'screen';
Piwik_FrontController::getInstance()->init();

// try getting the pecl location provider
$provider = new Piwik_UserCountry_LocationProvider_GeoIp_Pecl();
if (!$provider->isAvailable())
{
	Piwik::log("[note] The GeoIP PECL extension is not installed.");
	$provider = null;
}
else
{
	$workingOrError = $provider->isWorking();
	if ($workingOrError !== true)
	{
		Piwik::log("[note] The GeoIP PECL extension is broken: $workingOrError");
		if (Piwik_Common::isPhpCliMode())
		{
			Piwik::log("[note] Make sure your command line PHP is configured to use the PECL extension.");
		}
		$provider = null;
	}
}

// use php api if pecl extension cannot be used
if (is_null($provider))
{
	Piwik::log("[note] Falling back to PHP API. This may become too slow for you. If so, you can read this link on how to install the PECL extension: http://piwik.org/faq/how-to/#faq_164");
	
	$provider = new Piwik_UserCountry_LocationProvider_GeoIp_Php();
	if (!$provider->isAvailable())
	{
		Piwik::log("[note] The GeoIP PHP API is not available. This means you do not have a GeoIP location database in your ./misc directory. The database must be named either GeoIP.dat or GeoIPCity.dat based on the type of database it is.");
		$provider = null;
	}
	else
	{
		$workingOrError = $provider->isWorking();
		if ($workingOrError !== true)
		{
			Piwik::log("[note] The GeoIP PHP API is broken: $workingOrError");
			$provider = null;
		}
	}
}

if (is_null($provider))
{
	Piwik::log("\n[error] There is no location provider that can be used with this script. Only the GeoIP PECL module or the GeoIP PHP API can be used at present. Please install and configure one of these first.");
	exit;
}

$info = $provider->getInfo();
Piwik::log("[note] Found working provider: {$info['id']}");

// when script run via browser, check for Super User
if (!Piwik_Common::isPhpCliMode()) 
{
    try {
    	Piwik::checkUserIsSuperUser();
    } catch(Exception $e) {
    	Piwik::log('[error] You must be logged in as Super User to run this script. Please login in to Piwik and refresh this page.');
    	exit;
    }
}

// perform update
$logVisitFieldsToUpdate = array('location_country'   => Piwik_UserCountry_LocationProvider::COUNTRY_CODE_KEY,
								'location_region'	 => Piwik_UserCountry_LocationProvider::REGION_CODE_KEY,
								'location_city'      => Piwik_UserCountry_LocationProvider::CITY_NAME_KEY,
								'location_latitude'  => Piwik_UserCountry_LocationProvider::LATITUDE_KEY,
								'location_longitude' => Piwik_UserCountry_LocationProvider::LONGITUDE_KEY);

$query = "SELECT count(*) FROM ".Piwik_Common::prefixTable('log_visit');
$count = Piwik_FetchOne($query);
$start = 0;
$limit = 1000;

Piwik::log("\n$count rows to process in ".Piwik_Common::prefixTable('log_visit')
	. " and ".Piwik_Common::prefixTable('log_conversion')."...");
flush();
for (; $start < $count; $start += $limit)
{
	$rows = Piwik_FetchAll("SELECT idvisit, location_ip, ".implode(',', array_keys($logVisitFieldsToUpdate))."
						FROM ".Piwik_Common::prefixTable('log_visit')." 
						LIMIT $start, $limit");
	if(!count($rows))
	{
		continue;
	}

	foreach ( $rows as $i => $row )
	{
		$fieldsToSet = array();
		foreach ($logVisitFieldsToUpdate as $field => $ignore)
		{
			if (empty($fieldsToSet[$field]))
			{
				$fieldsToSet[] = $field;
			}
		}
		
		// skip if it already has a location
		if (empty($fieldsToSet))
		{
			continue;
		}
		
		$ip = Piwik_IP::N2P($row['location_ip']);
		$location = $provider->getLocation(array('ip' => $ip));
		
		if (!empty($location[Piwik_UserCountry_LocationProvider::COUNTRY_CODE_KEY]))
		{
			$location[Piwik_UserCountry_LocationProvider::COUNTRY_CODE_KEY] =
				strtolower($location[Piwik_UserCountry_LocationProvider::COUNTRY_CODE_KEY]);
		}
		$row['location_country'] = strtolower($row['location_country']);
		
		$columnsToSet = array();
		$bind = array();
		foreach ($logVisitFieldsToUpdate as $column => $locationKey)
		{
			if (!empty($location[$locationKey])
				&& $location[$locationKey] != $row[$column])
			{
				$columnsToSet[] = $column.' = ?';
				$bind[] = $location[$locationKey];
			}
		}
		
		if (empty($columnsToSet))
		{
			continue;
		}
		
		$bind[] = $row['idvisit'];
		
		// update log_visit
		$sql = "UPDATE ".Piwik_Common::prefixTable('log_visit')."
				   SET ".implode(', ', $columnsToSet)."
				 WHERE idvisit = ?";
		Piwik_Query($sql, $bind);
		
		// update log_conversion
		$sql = "UPDATE ".Piwik_Common::prefixTable('log_conversion')."
				   SET ".implode(', ', $columnsToSet)."
				 WHERE idvisit = ?";
		Piwik_Query($sql, $bind);
	}
	Piwik::log(round($start * 100 / $count) . "% done...");
	flush();
}
Piwik::log("100% done!");
Piwik::log("");
Piwik::log("[note] Now that you've geolocated your old visits, you need to force your reports to be re-processed. See this FAQ entry: http://piwik.org/faq/how-to/#faq_59");

