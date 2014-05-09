<?php
/**
 *  Proxy to normal piwik.php, but in testing mode
 *
 *  - Use the tests database to record Tracking data
 *  - Allows to overwrite the Visitor IP, and Server datetime
 *
 */

use Piwik\DataTable\Manager;
use Piwik\Option;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Site;
use Piwik\Tracker\Cache;
use Piwik\Tracker;

require realpath(dirname(__FILE__)) . "/includes.php";

// Wrapping the request inside ob_start() calls to ensure that the Test
// calling us waits for the full request to process before unblocking
ob_start();

Piwik_TestingEnvironment::addHooks();

GeoIp::$geoIPDatabaseDir = 'tests/lib/geoip-files';

Tracker::setTestEnvironment();
Manager::getInstance()->deleteAll();
Option::clearCache();
Site::clearCache();
Cache::deleteTrackerCache();

include PIWIK_INCLUDE_PATH . '/piwik.php';
ob_end_flush();
