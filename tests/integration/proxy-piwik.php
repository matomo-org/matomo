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

define('PIWIK_INCLUDE_PATH', '../..');
define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);

require_once PIWIK_INCLUDE_PATH .'/core/Loader.php';
require_once PIWIK_INCLUDE_PATH .'/libs/upgradephp/upgrade.php';

// Config files forced to use the test database
// Note that this also provides security for Piwik installs containing tests files: 
// this proxy will not record any data in the production database.
Piwik::createConfigObject();
Zend_Registry::get('config')->setTestEnvironment();	
Piwik_Tracker_Config::getInstance()->setTestEnvironment();

// Custom IP to use for this visitor
$customIp = Piwik_Common::getRequestVar('cip', false);
if(!empty($customIp)) 
{
	Piwik_Tracker::setForceIp($customIp);
}

// Custom server date time to use
$customDatetime = Piwik_Common::getRequestVar('cdt', false);
if(!empty($customDatetime))
{
	Piwik_Tracker::setForceDateTime($customDatetime);
}

include '../../piwik.php';
ob_flush();
