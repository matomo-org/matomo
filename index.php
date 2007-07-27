<?php
/*
 * PHP Configuration init
 */
error_reporting(E_ALL|E_NOTICE);
date_default_timezone_set('Europe/London');
define('PIWIK_INCLUDE_PATH', '.');

require_once PIWIK_INCLUDE_PATH . "/modules/ErrorHandler.php";
set_error_handler('Piwik_ErrorHandler');
require_once PIWIK_INCLUDE_PATH . "/modules/ExceptionHandler.php";
set_exception_handler('Piwik_ExceptionHandler');

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/core/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/modules'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/core/models'
					. PATH_SEPARATOR . get_include_path());

assert_options(ASSERT_ACTIVE, 	1);
assert_options(ASSERT_WARNING, 	1);
assert_options(ASSERT_BAIL, 	1);

/*
 * Zend classes
 */
include "Zend/Exception.php";
include "Zend/Loader.php";
Zend_Loader::loadClass('Zend_Registry');
Zend_Loader::loadClass('Zend_Config_Ini');
Zend_Loader::loadClass('Zend_Db');
Zend_Loader::loadClass('Zend_Db_Table');
Zend_Loader::loadClass('Zend_Debug');
Zend_Loader::loadClass('Zend_Auth');
Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');

/*
 * Piwik classes
 */
Zend_Loader::loadClass('Piwik_Access');
Zend_Loader::loadClass('Piwik_APIable'); 
Zend_Loader::loadClass('Piwik_Log');
Zend_Loader::loadClass('Piwik_Auth');
Zend_Loader::loadClass('Piwik_Config');
Zend_Loader::loadClass('Piwik_PublicAPI');
Zend_Loader::loadClass('Piwik');

//move into a init() method
Piwik::createConfigObject();
Piwik::createDatabaseObject();
Piwik::createLogObject();

//TODO move all DB related methods in a DB static class
Piwik::createDatabase();

Piwik::createTables();




//$logger = new Piwik_Log_APICalls;
$logger = new Piwik_Log_Messages;

$configAPI = Zend_Registry::get('config')->log->api_calls;

foreach($configAPI as $recordTo)
{
	switch($recordTo)
	{
		case 'screen':
			$logger->addWriteToScreen();
		break;
		
		case 'database':
			$logger->addWriteToDatabase();
		break;
		
		case 'file':
			$logger->addWriteToFile();		
		break;
		
		default:
			throw new Exception("TODO");
		break;
	}
}

Zend_Registry::set('logger', $logger);


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

$access->loadAccess();


main();
//Piwik::uninstall();

Piwik_Log::dump( Zend_Registry::get('db')->getProfiler()->getQueryProfiles() );

function main()
{
	Piwik::log("Start process...");
	$api = Piwik_PublicApi::getInstance();
	
	$api->registerClass("Piwik_SitesManager");
	$api->registerClass("Piwik_UsersManager");
	
	$api->SitesManager->getSiteUrlsFromId(1);
	
	$api->SitesManager->addSite("test name site", array("http://localhost", "http://test.com"));
	$api->UsersManager->deleteUser("login");
	$api->UsersManager->addUser("login", "password", "email@geage.com");
}

?>