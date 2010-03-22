<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}
Mock::generate('Piwik_Access');

class Test_Database extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
		print("The test class extends Test_Database: the test Piwik database is created once in the constructor, and all tables are truncated at the end of EACH unit test method.<br>");
		
		Piwik::createConfigObject();
		Zend_Registry::get('config')->setTestEnvironment();	
		Zend_Registry::get('config')->disableSavingConfigurationFileUpdates();
		Piwik_Tracker_Config::getInstance()->setTestEnvironment();
		Piwik::createDatabaseObject();
		Piwik::createLogObject();

		Piwik::dropDatabase();
		Piwik::createDatabase();
		Piwik::disconnectDatabase();
		Piwik::createDatabaseObject();
		Piwik::createTables();
	}
	public function __destruct()
	{
	}
	public function setUp()
	{
	}
	
	public function tearDown()
	{
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

