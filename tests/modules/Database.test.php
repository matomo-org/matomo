<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}

Mock::generate('Piwik_Access');


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
	static public function loadAccess()
	{}
	static public function checkUserHasAdminAccess( $idSites )
	{
		if(!self::$superUser)
		{
			$websitesAccess=self::$idSitesAdmin;
		}
		else
		{
			$websitesAccess=Piwik_SitesManager_API::getAllSitesId();
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
			$websitesAccess=Piwik_SitesManager_API::getAllSitesId();
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
	static public function getIdentity()
	{
		return self::$identity;
	}
	
	static public function getSitesIdWithAdminAccess()
	{
		if(self::$superUser)
		{
			return Piwik_SitesManager_API::getAllSitesId();
		}
		return  self::$idSitesAdmin;
	}
	
	static public function getSitesIdWithViewAccess()
	{
		if(self::$superUser)
		{
			return Piwik_SitesManager_API::getAllSitesId();
		}
		return  self::$idSitesView;
	}
	static public function getSitesIdWithAtLeastViewAccess()
	{
		if(self::$superUser)
		{
			return Piwik_SitesManager_API::getAllSitesId();
		}
		return  array_merge(self::$idSitesView,self::$idSitesAdmin);
	}
}

class Test_Database extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
		print("For EACH TEST the Database is created before and dropped at the end of the test method.<br>");
	}
	
	public function setUp()
	{
		Piwik::createConfigObject();
		
		// setup database	
		Piwik::createDatabaseObject();
		
		Zend_Registry::get('config')->setTestEnvironment();	
		Zend_Registry::get('config')->doWriteFileWhenUpdated = false;
		
		Piwik::createLogObject();
		
		Piwik::dropDatabase();
		Piwik::createDatabase();
		Piwik::createDatabaseObject();
		
		Piwik::createTables();
	}
	
	public function tearDown()
	{
		Piwik::dropDatabase();
	}
}

