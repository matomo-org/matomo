<?php
/*
 * The script can be used to generate huge number of visits and actions
 * for a given number of days.
 */

if(file_exists('../bootstrap.php'))
{
	require_once '../bootstrap.php';
}

if(empty($_GET['choice']) || $_GET['choice'] != 'yes') {
    echo "<div style='color:red;font-size:large'>WARNING!</div> <br>You are about to generate fake visits which will be recorded in your Piwik database.
    <br>It will <b>not</b> be possible to easily delete these visits from the piwik logs.
    <br><br>Are you sure you want to generate fake visits?
    <br><br>
    <a href='../index.php'><b>NO</b>, I do not want to generate fake visits</a>
    <br><br>
    <a href='?choice=yes'><b>YES</b>, I want to generate fake visits</a>
    <br><br>
    Note: you can edit the source code of this file to specify how many visits to generate, how many days, etc.
	";
    return;
}


// TODO - generator should generate pages with slash, then test that period archiving doesn't show the unique page view
// TODO - should generate goals with keyword or referer that are not found for this day, to simulate a referer 5 days ago and conversion today
$minVisitors = 20;
$maxVisitors = 100;
$nbActions = 10;
$daysToCompute = 1;
$idSite = 1;


//-----------------------------------------------------------------------------
error_reporting(E_ALL|E_NOTICE);
if(!defined('PIWIK_INCLUDE_PATH'))
{
	define('PIWIK_INCLUDE_PATH', '..');
}
ignore_user_abort(true);

if(!defined('PIWIK_INCLUDE_SEARCH_PATH'))
{
	define('PIWIK_INCLUDE_SEARCH_PATH', PIWIK_INCLUDE_PATH . '/core'
		. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs'
		. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins');
	@ini_set('include_path', PIWIK_INCLUDE_SEARCH_PATH);
	@set_include_path(PIWIK_INCLUDE_SEARCH_PATH);
}

$GLOBALS['PIWIK_TRACKER_DEBUG'] = false;
ob_start();

// first check that user has privileges to create some random data in the DB -> he must be super user
define('PIWIK_ENABLE_DISPATCH', false);
require_once PIWIK_INCLUDE_PATH . "/index.php";
require_once "FrontController.php";

Piwik::setMaxExecutionTime(0);
$idSite = Piwik_Common::getRequestVar('idSite', $idSite, 'int');

try {
	Piwik_FrontController::getInstance()->init();
} catch( Exception $e) {
	echo $e->getMessage();
	exit;
}
Piwik::checkUserIsSuperUser();

require_once "PluginsManager.php";
require_once "Tracker.php";

//Piwik_PluginsManager::getInstance()->unloadPlugins();

// we have to unload the Provider plugin otherwise it tries to lookup the IP for ahostname, and there is no dns server here
if(Piwik_PluginsManager::getInstance()->isPluginActivated('Provider'))
{
	Piwik_PluginsManager::getInstance()->unloadPlugin('Provider');
}

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
