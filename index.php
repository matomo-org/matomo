<?php
/**
 * PHP Configuration init
 */
error_reporting(E_ALL|E_NOTICE);
date_default_timezone_set('Europe/London');
define('PIWIK_INCLUDE_PATH', '.');

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/modules/'
					. PATH_SEPARATOR . get_include_path());

assert_options(ASSERT_ACTIVE, 	1);
assert_options(ASSERT_WARNING, 	1);
assert_options(ASSERT_BAIL, 	1);

//ini_set('xdebug.collect_vars', 'on');
//ini_set('xdebug.collect_params', '4');
//ini_set('xdebug.dump_globals', 'on');
//ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
//ini_set('xdebug.show_local_vars', 'on');

/**
 * Error / exception handling functions
 */
require_once PIWIK_INCLUDE_PATH . "/modules/ErrorHandler.php";
set_error_handler('Piwik_ErrorHandler');
require_once PIWIK_INCLUDE_PATH . "/modules/ExceptionHandler.php";
set_exception_handler('Piwik_ExceptionHandler');

/**
 * Zend classes
 */
include "Zend/Exception.php";
include "Zend/Loader.php";
require_once "Zend/Debug.php";
require_once "Zend/Auth.php";
require_once "Zend/Auth/Adapter/DbTable.php";

/**
 * Piwik classes
 */
require_once "Timer.php";
$timer = new Piwik_Timer;
require_once "Piwik.php";

require_once "Access.php";
require_once "Auth.php";
require_once "PublicAPI.php";
require_once "Site.php";

//move into a init() method
Piwik::createConfigObject();

// database object
Piwik::createDatabaseObject();

// Create the log objects
Piwik::createLogObject();

Piwik::printMemoryUsage('Start program');
//TODO move all DB related methods in a DB static class
Piwik::createDatabase();
Piwik::createDatabaseObject();
Piwik::dropTables(array(Piwik::prefixTable('log_visit'),Piwik::prefixTable('log_link_visit_action'),Piwik::prefixTable('log_action'),Piwik::prefixTable('log_profiling')));
Piwik::createTables();

// load plugins
Piwik_PluginsManager::getInstance()->setInstallPlugins(); //TODO plugins install to handle in a better way
Piwik::loadPlugins();

// Create auth object
$auth = Zend_Auth::getInstance();
$authAdapter = new Piwik_Auth();
$authAdapter->setTableName(Piwik::prefixTable('user'))
			->setIdentityColumn('login')
			->setCredentialColumn('password')
			->setCredentialTreatment('MD5(?)');

// Set the input credential values (e.g., from a login form)
$authAdapter->setIdentity('root')
            ->setCredential('nintendo');

// Perform the authentication query, saving the result
$access = new Piwik_Access($authAdapter);
Zend_Registry::set('access', $access);

Zend_Registry::get('access')->loadAccess();

Zend_Loader::loadClass('Piwik_Archive');
Zend_Loader::loadClass('Piwik_Date');

Piwik::printMemoryUsage('Before archiving');
$test = new Piwik_Archive;
$period = new Piwik_Period_Day( Piwik_Date::today() );
$site = new Piwik_Site(1);
$test->setPeriod($period);
$test->setSite($site);
Piwik::log($test->get('toto0'));
Piwik::log($test->get('toto1'));


$test = new Piwik_Archive;
$period = new Piwik_Period_Month(Piwik_Date::today());
$site = new Piwik_Site(1);
$test->setPeriod($period);
$test->setSite($site);
Piwik::log($test->get('nb_visits'));
Piwik::log($test->get('toto12'));
//
//$test = new Piwik_Archive;
//$period = new Piwik_Period_Day(Piwik_Date::today());
//$site = new Piwik_Site(12);
//$test->setPeriod($period);
//$test->setSite($site);
//$test->get('nb_visits');
//$test->get('toto12');

//main();
//displayProfiler();
Piwik::printMemoryUsage();
Piwik::printQueryCount();
echo $timer;

//Piwik::uninstall();
//Piwik_Log::dump( Zend_Registry::get('db')->getProfiler()->getQueryProfiles() );

function displayProfiler()
{
	$profiler = Zend_Registry::get('db')->getProfiler();

	$totalTime    = $profiler->getTotalElapsedSecs();
	$queryCount   = $profiler->getTotalNumQueries();
	$longestTime  = 0;
	$longestQuery = null;
	
	foreach ($profiler->getQueryProfiles() as $query) {
	    if ($query->getElapsedSecs() > $longestTime) {
	        $longestTime  = $query->getElapsedSecs();
	        $longestQuery = $query->getQuery();
	    }
	}
	
	echo '<br>Executed ' . $queryCount . ' queries in ' . $totalTime . ' seconds' . "\n";
	echo '<br>Average query length: ' . $totalTime / $queryCount . ' seconds' . "\n";
	echo '<br>Queries per second: ' . $queryCount / $totalTime . "\n";
	echo '<br>Longest query length: ' . $longestTime . "\n";
	echo "<br>Longest query: \n" . $longestQuery . "\n";
}

function main()
{
	Piwik::log("Start process...");
	$api = Piwik_PublicApi::getInstance();
	
	$api->registerClass("Piwik_SitesManager");
	$api->registerClass("Piwik_UsersManager");
	
	$api->SitesManager->getSiteUrlsFromId(1);
	
	$api->SitesManager->addSite("test name site", array("http://localhost", "http://test.com"));
	
	
	Zend_Registry::get('access')->loadAccess();
	
	$api->UsersManager->deleteUser("login");
	$api->UsersManager->addUser("login", "password", "email@geage.com");
}
?>

<br>
<br>
<br>
<a href="piwik.php?idsite=1&download=http://php.net/get&name=test download/ the file">test download </a>
<br>
<a href="piwik.php?idsite=1&download=http://php.net/get">test download - without name var</a>
<br>
<a href="piwik.php?idsite=1&link=http://php.net/&name=php.net website">test link php</a>
<br>
<a href="piwik.php?idsite=1&link=http://php.net/">test link php - without name var</a>
<br>
<!-- Piwik -->
<a href="http://piwik.org" title="Web analytics" onclick="window.open(this.href);return(false);">
<script language="javascript" src="piwik.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
	piwik_action_name = '';
	piwik_idsite = 1;
	piwik_url = "http://localhost/dev/piwiktrunk/piwik.php";
	piwik_log(piwik_action_name, piwik_idsite, piwik_url);
//-->
</script><object>
<noscript><p>Web analytics<img src="http://localhost/dev/piwiktrunk/piwik.php" style="border:0" /></p>
</noscript></object></a>
<!-- /Piwik --> 