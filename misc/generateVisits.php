<?php

error_reporting(E_ALL|E_NOTICE);
define('PIWIK_INCLUDE_PATH', '..');

ignore_user_abort(true);
set_time_limit(0);

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/modules'
					. PATH_SEPARATOR . get_include_path() );
					
$GLOBALS['DEBUGPIWIK'] = false;

ob_start();

// first check that user has privileges to create some random data in the DB -> he must be super user
define('ENABLE_DISPATCH', false);
require_once "index.php";
require_once "FrontController.php";

Piwik_FrontController::getInstance()->init();

Piwik::checkUserIsSuperUser();
// end check that user was super user

require_once "PluginsManager.php";
require_once "Timer.php";
require_once "Cookie.php";
require_once "LogStats.php";
require_once "LogStats/Config.php";
require_once "LogStats/Action.php";
require_once "LogStats/Db.php";
require_once "LogStats/Visit.php";
require_once "LogStats/Generator.php";

// unload all loaded plugins
//Piwik_PluginsManager::getInstance()->unloadPlugins();

// we unload the Provider plugin otherwise it tries to lookup the IP for ahostname, and there is no dns server here
Piwik_PluginsManager::getInstance()->unloadPlugin('Provider');

// we set the DO NOT load plugins so that the LogStats generator doesn't load the plugins we've just disabled.
// if for some reasons you want to load the plugins, comment this line, and disable the plugin Provider in the plugins interface
// (see explanation above)
Piwik_PluginsManager::getInstance()->doNotLoadPlugins();

$generator = new Piwik_LogStats_Generator;
$generator->setMaximumUrlDepth(3);
//$generator->disableProfiler();
$generator->setIdSite( $idSite = 1 );
$minVisits = 5;
$maxVisits = 15;
$nbActions = 15;
$daysToCompute = 1;

$nbActionsTotal = 0;

//$generator->emptyAllLogTables();
$generator->init();

$t = new Piwik_Timer;

/*
 * Generate visits / actions for the last 31 days
 */

// do NOT edit this line
$startTime = time() - ($daysToCompute-1)*86400;
while($startTime <= time())
{
	$visits = rand($minVisits,$maxVisits);
	$actions=$nbActions;
	
	$generator->setTimestampToUse($startTime);
	
	// we add silent fail because of headers already sent error.
	// although this should'nt happen because we use a OB_START at the top of this page...
	// but I couldnt find where those headers were sent...
	$nbActionsTotalThisDay = $generator->generate($visits,$actions);
	
	$actionsPerVisit = round($nbActionsTotalThisDay / $visits);
	print("Generated $visits visits and $actionsPerVisit actions per visit for the ".date("Y-m-d", $startTime)."<br>\n");
	$startTime+=86400;
	$nbActionsTotal+=$nbActionsTotalThisDay;
	flush();
}

echo "<br>Total actions: $nbActionsTotal";
echo "<br>Total requests per sec: ". round($nbActionsTotal / $t->getTime(),0);
echo "<br>".$t;

$generator->end();

ob_end_flush();
