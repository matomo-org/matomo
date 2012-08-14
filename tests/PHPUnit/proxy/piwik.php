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
ob_start();

define('PIWIK_INCLUDE_PATH', '../../..');
define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);

require_once PIWIK_INCLUDE_PATH .'/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH .'/core/Loader.php';

// Config files forced to use the test database
// Note that this also provides security for Piwik installs containing tests files: 
// this proxy will not record any data in the production database.
Piwik::createConfigObject();
Piwik_Config::getInstance()->setTestEnvironment();
Piwik_Config::getInstance()->PluginsInstalled['PluginsInstalled'] = array();

Piwik_Tracker::setTestEnvironment();
Piwik_DataTable_Manager::getInstance()->deleteAll();
Piwik_Option::getInstance()->clearCache();
Piwik_Site::clearCache();
Piwik_Common::deleteTrackerCache();

include PIWIK_INCLUDE_PATH . '/piwik.php';
ob_flush();
