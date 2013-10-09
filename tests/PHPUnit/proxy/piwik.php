<?php
/**
 *  Proxy to normal piwik.php, but in testing mode
 *
 *  - Use the tests database to record Tracking data
 *  - Allows to overwrite the Visitor IP, and Server datetime
 *
 * @see Main.test.php
 *
 */

// Wrapping the request inside ob_start() calls to ensure that the Test
// calling us waits for the full request to process before unblocking
use Piwik\Config;
use Piwik\DataTable\Manager;
use Piwik\Option;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Tracker;
use Piwik\Site;
use Piwik\Tracker\Cache;

ob_start();

define('PIWIK_INCLUDE_PATH', '../../..');
define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';
require_once PIWIK_INCLUDE_PATH . '/core/functions.php';

// Config files forced to use the test database
// Note that this also provides security for Piwik installs containing tests files: 
// this proxy will not record any data in the production database.
Config::getInstance()->setTestEnvironment();
Config::getInstance()->PluginsInstalled['PluginsInstalled'] = array();
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
