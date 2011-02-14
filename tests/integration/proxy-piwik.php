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
// Do not run scheduled tasks during tests
Piwik_Tracker_Config::getInstance()->setTestValue('Tracker', 'scheduled_tasks_min_interval', 0);

// Tests can force the use of 3rd party cookie for ID visitor
if(Piwik_Common::getRequestVar('forceUseThirdPartyCookie', false) == 1)
{
	Piwik_Tracker_Config::getInstance()->setTestValue('Tracker', 'use_third_party_cookies', 1);
}
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

// Disable provider plugin, because it is so slow to do reverse ip lookup in dev environment somehow
Piwik_Tracker::setPluginsNotToLoad(array('Provider'));
include '../../piwik.php';
ob_flush();
