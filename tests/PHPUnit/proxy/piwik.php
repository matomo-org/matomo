<?php
/**
 *  Proxy to normal piwik.php, but in testing mode
 *
 *  - Use the tests database to record Tracking data
 *  - Allows to overwrite the Visitor IP, and Server datetime
 *
 */

use Piwik\Application\Environment;
use Piwik\DataTable\Manager;
use Piwik\Option;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp2;
use Piwik\Site;
use Piwik\Tests\Framework\TestingEnvironmentManipulator;
use Piwik\Tests\Framework\TestingEnvironmentVariables;
use Piwik\Tracker;

require realpath(dirname(__FILE__)) . "/includes.php";

// Wrapping the request inside ob_start() calls to ensure that the Test
// calling us waits for the full request to process before unblocking
ob_start();

try {
    $globalObservers = array(
        array('Environment.bootstrapped', function () {
            Tracker::setTestEnvironment();
            Manager::getInstance()->deleteAll();
            Option::clearCache();
            Site::clearCache();
        })
    );

    Environment::setGlobalEnvironmentManipulator(new TestingEnvironmentManipulator(new TestingEnvironmentVariables(), $globalObservers));

    GeoIp2::$geoIPDatabaseDir = 'tests/lib/geoip-files';

    include PIWIK_INCLUDE_PATH . '/piwik.php';
} catch (Exception $ex) {
    echo "Unexpected error during tracking: " . $ex->getMessage() . "\n" . $ex->getTraceAsString() . "\n";
}

if (ob_get_level() > 1) {
    ob_end_flush();
}

