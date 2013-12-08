<?php
/**
 *  Proxy to normal piwik.php, but in testing mode
 *
 *  - Use the tests database to record Tracking data
 *  - Allows to overwrite the Visitor IP, and Server datetime
 *
 */

use Piwik\Config;
use Piwik\DataTable\Manager;
use Piwik\Option;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Site;
use Piwik\Tracker;
use Piwik\Tracker\Cache;

require realpath(dirname(__FILE__)) . "/includes.php";

// Wrapping the request inside ob_start() calls to ensure that the Test
// calling us waits for the full request to process before unblocking
ob_start();

Config::getInstance()->setTestEnvironment();

try {
    $trackerPlugins = Config::getInstance()->Plugins_Tracker['Plugins_Tracker'];
}catch(Exception $e) {
    $trackerPlugins = array();
}
$trackerPlugins[] = 'DevicesDetection';
Config::getInstance()->Plugins_Tracker['Plugins_Tracker'] = $trackerPlugins;

GeoIp::$geoIPDatabaseDir = 'tests/lib/geoip-files';

Tracker::setTestEnvironment();
Manager::getInstance()->deleteAll();
Option::clearCache();
Site::clearCache();
Cache::deleteTrackerCache();

include PIWIK_INCLUDE_PATH . '/piwik.php';
ob_end_flush();
