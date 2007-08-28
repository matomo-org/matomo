<?php
/**
 * PHP Configuration init
 */
error_reporting(E_ALL|E_NOTICE);
date_default_timezone_set('Europe/London');
define('PIWIK_INCLUDE_PATH', '.');
define('PIWIK_PLUGINS_PATH', PIWIK_INCLUDE_PATH . '/plugins');
define('PIWIK_DATAFILES_INCLUDE_PATH', PIWIK_INCLUDE_PATH . "/modules/DataFiles");

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/modules/'
					. PATH_SEPARATOR . get_include_path());

assert_options(ASSERT_ACTIVE, 	1);
assert_options(ASSERT_WARNING, 	1);
assert_options(ASSERT_BAIL, 	1);

/**
 * Error / exception handling functions
 */
require_once PIWIK_INCLUDE_PATH . "/modules/ErrorHandler.php";
require_once PIWIK_INCLUDE_PATH . "/modules/ExceptionHandler.php";
set_error_handler('Piwik_ErrorHandler');
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
require_once "API/Proxy.php";
require_once "Site.php";
require_once "Translate.php";

//move into a init() method
Piwik::createConfigObject();

// database object
Piwik::createDatabaseObject();

// Create the log objects
Piwik::createLogObject();

Piwik::printMemoryUsage('Start program');
//TODO move all DB related methods in a DB static class

//Piwik::createDatabase();
//Piwik::createDatabaseObject();

$doNotDrop = array(
		Piwik::prefixTable('log_visit'),
		Piwik::prefixTable('log_link_visit_action'),
		Piwik::prefixTable('log_action'),
		Piwik::prefixTable('log_profiling'),
		Piwik::prefixTable('archive'),
);

Piwik::dropTables($doNotDrop);
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

main();
displayProfiler();
Piwik::printMemoryUsage();
Piwik::printQueryCount();
echo $timer;

//Piwik::uninstall();

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
	echo '<br>Longest query: <br>' . $longestQuery . "\n";
}


function main()
{
	Piwik::log(
			'<a href="http://localhost/dev/piwiktrunk/?method=UserSettings.getResolution&idSite=1&date=2007-08-25&period=week&format=xml&filter_limit=&filter_offset=&filter_column=label&filter_pattern=12">
			http://localhost/dev/piwiktrunk/?method=UserSettings.getResolution&idSite=1&date=2007-08-25&period=week&format=xml&filter_limit=&filter_offset=&filter_column=label&filter_pattern=12
			</a>
			<br>'
	);
	
	Piwik::log("Start process...");
	$api = Piwik_API_Proxy::getInstance();
	
	$api->registerClass("SitesManager");
	$api->registerClass("UsersManager");
	
	$api->SitesManager->getSiteUrlsFromId(1);
	
	$api->SitesManager->addSite("test name site", array("http://localhost", "http://test.com"));
	
	Zend_Registry::get('access')->loadAccess();
	
	$api->UsersManager->deleteUser("login");
	$api->UsersManager->addUser("login", "password", "email@geage.com");
	
	require_once "API/Request.php";
	
	Piwik::log("getResolution");
	$request = new Piwik_API_Request('
			method=UserSettings.getResolution
			&idSite=1
			&date=2007-08-25
			&period=week
			&format=console
			&filter_limit=
			&filter_offset=
			&filter_column=label
			&filter_pattern=
		');
	print(($request->process()));
	
	Piwik::log("getOS");
	$request = new Piwik_API_Request('method=UserSettings.getOS

			&idSite=1
			&date=2007-08-25
			&period=week
			&format=xml
			&filter_limit=
			&filter_offset=
			&filter_column=label
			&filter_pattern=
	');
	dump(htmlentities($request->process()));
	
	Piwik::log("getConfiguration");
	$request = new Piwik_API_Request('
				method=UserSettings.getConfiguration
				&idSite=1
				&date=2007-08-25
				&period=week
				&format=xml
				&filter_limit=10
				&filter_offset=0
				&filter_column=label
				&filter_pattern=
		');
	dump(htmlentities($request->process()));
	
	Piwik::log("getBrowser");
	$request = new Piwik_API_Request('
				method=UserSettings.getBrowser
				&idSite=1
				&date=2007-08-25
				&period=week
				&format=xml
				&filter_limit=
				&filter_offset=
				&filter_column=label
				&filter_pattern=
	');
	dump(htmlentities($request->process()));
	
	Piwik::log("getBrowserType");
	$request = new Piwik_API_Request('
				method=UserSettings.getBrowserType
				&idSite=1
				&date=2007-08-25
				&period=week
				&format=xml
				&filter_limit=
				&filter_offset=
				&filter_column=label
				&filter_pattern=
	');
	dump(htmlentities($request->process()));
	
	Piwik::log("getWideScreen");
	$request = new Piwik_API_Request('
				method=UserSettings.getWideScreen
				&idSite=1
				&date=2007-08-25
				&period=week
				&format=xml
				&filter_limit=
				&filter_offset=
				&filter_column=label
				&filter_pattern=
	');
	dump(htmlentities($request->process()));
	
	Piwik::log("getPlugin");
	$request = new Piwik_API_Request('
				method=UserSettings.getPlugin
				&idSite=1
				&date=2007-08-25
				&period=week
				&format=xml
				&filter_limit=
				&filter_offset=
				&filter_column=label
				&filter_pattern=
	');
	dump(htmlentities($request->process()));
	
	Piwik::log("getActions");
	$request = new Piwik_API_Request(
		'method=Actions.getActions
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=html
		&filter_limit=10
		&filter_offset=0
	'
	);
//	echo(($request->process()));

	Piwik::log("getActions EXPANDED");
	$request = new Piwik_API_Request(
		'method=Actions.getActions
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=html
		&expanded=true
		&filter_column=label
		&filter_pattern=a
		&filter_limit=10
		&filter_offset=0
		
	'
	);
//	echo(($request->process()));
	
	Piwik::log("getActions EXPANDED SUBTABLE");
	$request = new Piwik_API_Request(
		'method=Actions.getActions
		&idSubtable=5477
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=html
		&expanded=false
		
	'
	);
//	echo(($request->process()));
	
	Piwik::log("getDownloads");
	$request = new Piwik_API_Request(
		'method=Actions.getDownloads
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
//	dump(htmlentities($request->process()));
	Piwik::log("getOutlinks");
	$request = new Piwik_API_Request(
		'method=Actions.getOutlinks
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
//	dump(htmlentities($request->process()));
	Piwik::log("getProvider");
	$request = new Piwik_API_Request(
		'method=Provider.getProvider
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getCountry");
	$request = new Piwik_API_Request(
		'method=UserCountry.getCountry
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getContinent");
	$request = new Piwik_API_Request(
		'method=UserCountry.getContinent
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	
	Piwik::log("getContinent");
	$request = new Piwik_API_Request(
		'method=VisitFrequency.getSummary
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getNumberOfVisitsPerVisitDuration");
	$request = new Piwik_API_Request(
		'method=VisitorInterest.getNumberOfVisitsPerVisitDuration
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getNumberOfVisitsPerPage");
	$request = new Piwik_API_Request(
		'method=VisitorInterest.getNumberOfVisitsPerPage
		&idSite=1
		&date=2007-08-25
		&period=month
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	
	
	Piwik::log("getVisitInformationPerServerTime");
	$request = new Piwik_API_Request(
		'method=VisitTime.getVisitInformationPerServerTime
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	
	Piwik::log("getRefererType");
	$request = new Piwik_API_Request(
		'method=Referers.getRefererType
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getKeywords");
	$request = new Piwik_API_Request(
		'method=Referers.getKeywords
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	Piwik::log("getSearchEnginesFromKeywordId");
	$request = new Piwik_API_Request(
		'method=Referers.getSearchEnginesFromKeywordId
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&idSubtable=1886
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getSearchEngines");
	$request = new Piwik_API_Request(
		'method=Referers.getSearchEngines
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	
	
	Piwik::log("getKeywordsFromSearchEngineId");
	$request = new Piwik_API_Request(
		'method=Referers.getKeywordsFromSearchEngineId
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
		&idSubtable=1779
	'
	);
	dump(htmlentities($request->process()));
	
	
	
	Piwik::log("getCampaigns");
	$request = new Piwik_API_Request(
		'method=Referers.getCampaigns
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	
	
	
	Piwik::log("getKeywordsFromCampaignId");
	$request = new Piwik_API_Request(
		'method=Referers.getKeywordsFromCampaignId
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
		&idSubtable=2251
	'
	);
	dump(htmlentities($request->process()));
	
	
	Piwik::log("getWebsites");
	$request = new Piwik_API_Request(
		'method=Referers.getWebsites
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	
	
	
	Piwik::log("getUrlsFromWebsiteId");
	$request = new Piwik_API_Request(
		'method=Referers.getUrlsFromWebsiteId
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
		&idSubtable=2432
	'
	);
	dump(htmlentities($request->process()));
	
	Piwik::log("getPartners");
	$request = new Piwik_API_Request(
		'method=Referers.getPartners
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
	'
	);
	dump(htmlentities($request->process()));
	
	
	
	Piwik::log("getUrlsFromPartnerId");
	$request = new Piwik_API_Request(
		'method=Referers.getUrlsFromPartnerId
		&idSite=1
		&date=2007-08-25
		&period=week
		&format=xml
		&filter_limit=10
		&filter_offset=0
		&idSubtable=3090
	'
	);
	dump(htmlentities($request->process()));
	
	
	$referersNumeric=array(
		'getNumberOfDistinctSearchEngines',	
		'getNumberOfDistinctKeywords',
		'getNumberOfDistinctCampaigns',
		'getNumberOfDistinctWebsites',
		'getNumberOfDistinctWebsitesUrls',
		'getNumberOfDistinctPartners',
		'getNumberOfDistinctPartnersUrls',
	);
	foreach($referersNumeric as $name)
	{
		Piwik::log("$name");
		$request = new Piwik_API_Request(
			"method=Referers.$name
			&idSite=1
			&date=2007-08-20
			&period=day
			&format=xml
			&filter_limit=10
			&filter_offset=0
		"
		);
		dump(htmlentities($request->process()));
	}
	
}

function dump($var)
{
	print("<pre>");
	var_export($var);
	print("</pre>");
}

?>

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