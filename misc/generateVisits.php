<?php
/*
 * The script can be used to generate huge number of visits and actions
 * for a given number of days.
 */
$minVisitors = 200;
$maxVisitors = 200;
$nbActions = 3;
$daysToCompute = 10;

//-----------------------------------------------------------------------------
error_reporting(E_ALL|E_NOTICE);
define('PIWIK_INCLUDE_PATH', '..');
ignore_user_abort(true);
set_time_limit(0);
set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/core'
					. PATH_SEPARATOR . get_include_path() );
					
$GLOBALS['DEBUGPIWIK'] = false;
ob_start();

// first check that user has privileges to create some random data in the DB -> he must be super user
define('ENABLE_DISPATCH', false);
require_once "index.php";
require_once "FrontController.php";

$idSite = Piwik_Common::getRequestVar('idSite', 1, 'int');

try {
	Piwik_FrontController::getInstance()->init();
} catch( Exception $e) {
	echo $e->getMessage();
	exit;
}
Piwik::checkUserIsSuperUser();

require_once "PluginsManager.php";
require_once "Timer.php";
require_once "Cookie.php";
require_once "Tracker.php";
require_once "Tracker/Config.php";
require_once "Tracker/Action.php";
require_once "Tracker/Db.php";
require_once "Tracker/Visit.php";
require_once "Tracker/Generator.php";
require_once "Tracker/GoalManager.php";

//Piwik_PluginsManager::getInstance()->unloadPlugins();

// we have to unload the Provider plugin otherwise it tries to lookup the IP for ahostname, and there is no dns server here
Piwik_PluginsManager::getInstance()->unloadPlugin('Provider');

// we set the DO NOT load plugins so that the Tracker generator doesn't load the plugins we've just disabled.
// if for some reasons you want to load the plugins, comment this line, and disable the plugin Provider in the plugins interface
Piwik_PluginsManager::getInstance()->doNotLoadPlugins();

$generator = new Piwik_Tracker_Generator;
$generator->setMaximumUrlDepth(3);
//$generator->disableProfiler();
$generator->setIdSite( $idSite );

$nbActionsTotal = 0;
//$generator->emptyAllLogTables();
$generator->init();

$t = new Piwik_Timer;

$startTime = time() - ($daysToCompute-1)*86400;
while($startTime <= time())
{
	$visitors = rand($minVisitors, $maxVisitors);
	$actions = $nbActions;
	$generator->setTimestampToUse($startTime);
	
	$nbActionsTotalThisDay = $generator->generate($visitors, $actions);
	$actionsPerVisit = round($nbActionsTotalThisDay / $visitors);
	print("Generated $visitors unique visitors and $actionsPerVisit actions per visit for the ".date("Y-m-d", $startTime)."<br>\n");
	$startTime+=86400;
	$nbActionsTotal+=$nbActionsTotalThisDay;
	sleep(1);
}

echo "<br>Total actions: $nbActionsTotal";
echo "<br>Total requests per sec: ". round($nbActionsTotal / $t->getTime(),0);
echo "<br>".$t;

$generator->end();
ob_end_flush();
