<?php
/*
 * PHP Configuration init
 */
error_reporting(E_ALL|E_NOTICE);
date_default_timezone_set('Europe/London');
define('PIWIK_INCLUDE_PATH', '.');

require_once PIWIK_INCLUDE_PATH . "/modules/ErrorHandler.php";
set_error_handler('Piwik_ErrorHandler');

function Piwik_ExceptionHandler(Exception $exception) {
  echo "<div style='font-size:11pt'><pre>Uncaught exception: " , $exception->getMessage(), "\n";
  echo $exception->__toString();
  exit;
}
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
Zend_Loader::loadClass('Zend_Controller_Front');
Zend_Loader::loadClass('Zend_Registry');
Zend_Loader::loadClass('Zend_Config_Ini');
Zend_Loader::loadClass('Zend_Db');
Zend_Loader::loadClass('Zend_Db_Table');
Zend_Loader::loadClass('Zend_Debug');
Zend_Loader::loadClass('Zend_Auth');
Zend_Loader::loadClass('Zend_Acl');
Zend_Loader::loadClass('Zend_Acl_Resource');
Zend_Loader::loadClass('Zend_Acl_Role');
Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');

/*
 * Piwik classes
 */
Zend_Loader::loadClass('Piwik_Access');
Zend_Loader::loadClass('Piwik_Apiable');
Zend_Loader::loadClass('Piwik_Log');
Zend_Loader::loadClass('Piwik_Config');
Zend_Loader::loadClass('Piwik_PublicApi');
Zend_Loader::loadClass('Piwik');


Piwik::createConfigObject();
Piwik::createDatabaseObject();
Piwik::createLogObject();

Piwik::createTables();

/*Piwik_UsersManager::deleteUser("login");
Piwik_UsersManager::deleteUser("login2");
Piwik_UsersManager::addUser("login","password1", "alias", "ema@i.coml");
Piwik_UsersManager::addUser("login2","password2", "alias23", "ema2@i.coml");

Piwik_SitesManager::replaceSiteUrls(1, array());
Piwik_SitesManager::addSiteUrls(1, array("https://1", "http://2"));
//var_dump(Piwik_SitesManager::getSiteUrlsFromId(4));
//Piwik_SitesManager::addSite("many urls", array("https://t", "http://localhost/", "http://domain76.com/ijndex/"));

Piwik_UsersManager::setUserRole("admin", "login", array(3,5));
Piwik_UsersManager::setUserRole("admin", "login", array(6,7));
*/

// Create auth object
$auth = Zend_Auth::getInstance();
$authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('db'));
$authAdapter->setTableName(Piwik::prefixTable('user'))
			->setIdentityColumn('login')
			->setCredentialColumn('password')
			->setCredentialTreatment('MD5(?)');

// Set the input credential values (e.g., from a login form)
$authAdapter->setIdentity('login')
            ->setCredential('password1');

// Perform the authentication query, saving the result
$access = new Piwik_Access($authAdapter);
Zend_Registry::set('access', $access);

main();
//Piwik::uninstall();

function main()
{
	Piwik::log("Start process...");
	$api = Piwik_PublicApi::getInstance();
	$api->registerClass("Piwik_SitesManager");
	$api->registerClass("Piwik_UsersManager");
	
	$api->SitesManager->getSiteUrlsFromId(1); 
	$api->SitesManager->addSite("test name site", array("http://localhost", "http://test.com"));
	$api->UsersManager->addUser(2, "login", "password");
}

?>