<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}
Mock::generate('Piwik_Access');

/**
 * Tests exending Test_Database are much slower to run: the setUp will 
 * create all Piwik tables in a freshly empty test database.
 * 
 * This allows each test method to start from a clean DB and setup initial state to 
 * then test it.
 * 
 */
class Test_Database extends UnitTestCase
{
	static $warningDisplayed = false;
	function __construct( $title = '')
	{
		parent::__construct( $title );
		try {
    		Piwik::createConfigObject();
    		Zend_Registry::get('config')->setTestEnvironment();	
    		Piwik_Tracker_Config::getInstance()->setTestEnvironment();
    		Piwik::createDatabaseObject();
    		Piwik::createLogObject();
    
    		Piwik::dropDatabase();
    		Piwik::createDatabase();
    		Piwik::disconnectDatabase();
    		Piwik::createDatabaseObject();
    		Piwik::createTables();
    		Piwik_PluginsManager::getInstance()->installLoadedPlugins();
		} catch(Exception $e) {
			echo $e->getMessage();
			echo "<br/><b>TEST INITIALIZATION FAILED!";
			throw $e;
		}
	}
	public function __destruct()
	{
	}
	public function setUp()
	{
		Piwik_Common::deleteAllCache();
	}
	
	public function tearDown()
	{
		Piwik_Option::getInstance()->clearCache();
		Piwik_Common::deleteAllCache();
		Piwik::truncateAllTables();
	}
	
	public function testHelloWorld()
	{
		$this->assertTrue(true);
	}
	
	public function testHelloWorld2()
	{
		$this->assertTrue(true);
	}
	
}


class FakeAccess
{
	static public $superUser = false;
	static public $idSitesAdmin = array();
	static public $idSitesView = array();
	static public $identity = 'superUserLogin';
	
	static public function setIdSitesAdmin($ids)
	{
		self::$superUser = false;
		self::$idSitesAdmin = $ids;
	}
	static public function setIdSitesView($ids)
	{
		self::$superUser = false;
		self::$idSitesView = $ids;
	}
	
	static public function checkUserIsSuperUser()
	{
		if(!self::$superUser)
		{
			throw new Exception("checkUserIsSuperUser Fake exception // string not to be tested");
		}
	}
	
	static public function setSuperUser($bool = true)
	{
		self::$superUser = $bool;
	}

	static public function reloadAccess()
	{}

	static public function checkUserHasAdminAccess( $idSites )
	{
		if(!self::$superUser)
		{
			$websitesAccess=self::$idSitesAdmin;
		}
		else
		{
			$websitesAccess=Piwik_SitesManager_API::getInstance()->getAllSitesId();
		}
		
		if(!is_array($idSites))
		{
			$idSites = Piwik_Site::getIdSitesFromIdSitesString($idSites);
		}
		foreach($idSites as $idsite)
		{
			if(!in_array($idsite, $websitesAccess))
			{
				throw new Exception("checkUserHasAdminAccess Fake exception // string not to be tested");
			}
		}
	}
	
	//means at least view access
	static public function checkUserHasViewAccess( $idSites )
	{
		if(!self::$superUser)
		{
			$websitesAccess=array_merge(self::$idSitesView,self::$idSitesAdmin);
		}
		else
		{
			$websitesAccess=Piwik_SitesManager_API::getInstance()->getAllSitesId();
		}
		
		if(!is_array($idSites))
		{
			$idSites=array($idSites);
		}
		foreach($idSites as $idsite)
		{
			if(!in_array($idsite, $websitesAccess))
			{
				throw new Exception("checkUserHasViewAccess Fake exception // string not to be tested");
			}
		}
	}

	static public function checkUserHasSomeViewAccess()
	{
		if(!self::$superUser)
		{
			if( count(self::$idSitesView) == 0 )
			{
				throw new Exception("checkUserHasSomeViewAccess Fake exception // string not to be tested");
			}
		}
		else
		{
			return;
		}
	}

	//means at least view access
	static public function checkUserHasSomeAdminAccess()
	{
		if(!self::$superUser)
		{
			if( count(self::$idSitesAdmin) == 0 )
			{
				throw new Exception("checkUserHasSomeAdminAccess Fake exception // string not to be tested");
			}
		}
		else
		{
			return; //super user has some admin rights
		}
	}
	
	static public function getLogin()
	{
		return self::$identity;
	}
	
	static public function getSitesIdWithAdminAccess()
	{
		if(self::$superUser)
		{
			return Piwik_SitesManager_API::getInstance()->getAllSitesId();
		}
		return  self::$idSitesAdmin;
	}
	
	static public function getSitesIdWithViewAccess()
	{
		if(self::$superUser)
		{
			return Piwik_SitesManager_API::getInstance()->getAllSitesId();
		}
		return  self::$idSitesView;
	}
	static public function getSitesIdWithAtLeastViewAccess()
	{
		if(self::$superUser)
		{
			return Piwik_SitesManager_API::getInstance()->getAllSitesId();
		}
		return  array_merge(self::$idSitesView,self::$idSitesAdmin);
	}
}

