<?php

error_reporting(E_ALL|E_NOTICE);
define('PIWIK_INCLUDE_PATH', '..');
define('PIWIK_PLUGINS_PATH', PIWIK_INCLUDE_PATH . '/plugins');
define('PIWIK_DATAFILES_INCLUDE_PATH', PIWIK_INCLUDE_PATH . "/modules/DataFiles");

ignore_user_abort(true);
set_time_limit(0);

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/modules'
					. PATH_SEPARATOR . get_include_path() );

require_once "Event/Dispatcher.php";
require_once "Common.php";
require_once "PluginsManager.php";

require_once "LogStats.php";
require_once "LogStats/Config.php";
require_once "LogStats/Action.php";
require_once "Cookie.php";
require_once "LogStats/Db.php";
require_once "LogStats/Visit.php";

$GLOBALS['DEBUGPIWIK'] = false;

require_once "Timer.php";
require_once "LogStats/Generator.php";

ob_start();
Piwik_PluginsManager::getInstance()->doNotLoadPlugins();	
$generator = new Piwik_LogStats_Generator;
$generator->setMaximumUrlDepth(4);
//$generator->disableProfiler();
//$generator->emptyAllLogTables();
$generator->init();

$t = new Piwik_Timer;

/*
 * Generate visits / actions for the last 31 days
 */
$daysToCompute = 1;
$startTime = time() - ($daysToCompute-1)*86400;
$nbActionsTotal = 0;
while($startTime <= time())
{
	$visits = rand(1,2);
	$actions = 10;
	$visits = rand(10,30);
	$actions = 5;
	
	$generator->setTimestampToUse($startTime);
	$nbActionsTotalThisDay = $generator->generate($visits,$actions);
	
	$actionsPerVisit = round($nbActionsTotalThisDay / $visits);
	print("Generated $visits visits and $actionsPerVisit actions per visit for the ".date("Y-m-d", $startTime)."<br>\n");
	$startTime+=86400;
	$nbActionsTotal+=$nbActionsTotalThisDay;
}


echo "<br>Total actions: $nbActionsTotal";
echo "<br>Total requests per sec: ". round($nbActionsTotal / $t->getTime(),0);
echo "<br>".$t;

$generator->end();

ob_end_flush();
