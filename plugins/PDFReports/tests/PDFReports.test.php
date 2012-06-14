<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', dirname(__FILE__).'/../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once PIWIK_PATH_TEST_TO_ROOT . '/tests/core/Database.test.php';
require_once PIWIK_PATH_TEST_TO_ROOT . '/plugins/PDFReports/PDFReports.php';

class Test_Piwik_PDFReports extends Test_Database
{
	protected $idSiteAccess;

	function setUp()
	{
		parent::setUp();

		// setup the access layer
		$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = true;
		//finally we set the user as a super user by default
		Zend_Registry::set('access', $pseudoMockAccess);
		Piwik_PluginsManager::getInstance()->loadPlugins( array('API', 'UserCountry', 'LanguagesManager', 'PDFReports') );
		$plugin = Piwik_PluginsManager::getInstance()->getLoadedPlugin('LanguagesManager');
		$plugin->install();
		$plugin = Piwik_PluginsManager::getInstance()->getLoadedPlugin('PDFReports');
		$plugin->install();
		Piwik_PluginsManager::getInstance()->installLoadedPlugins();
		Piwik_PDFReports_API::$cache = array();

		$this->idSiteAccess = Piwik_SitesManager_API::getInstance()->addSite("Test",array("http://piwik.net"));

		$idSite = Piwik_SitesManager_API::getInstance()->addSite("Test",array("http://piwik.net"));
		FakeAccess::setIdSitesView( array($this->idSiteAccess,2));

	}

	function tearDown()
	{
		Piwik_Query('TRUNCATE '.Piwik_Common::prefixTable('report'));
		Piwik_PDFReports_API::$cache = array();
	}

	function test_addReport_getReports()
	{
		$data = array(
			'idsite' => $this->idSiteAccess,
			'description' => 'test description"',
			'type' => 'email',
			'period' => 'day',
			'format' => 'pdf',
	 		'reports' => array('UserCountry_getCountry'),
			'parameters' => array(
				'displayFormat' => '1',
				'emailMe' => true,
				'additionalEmails' => array('test@test.com', 't2@test.com')
			)
		);

		$dataWebsiteTwo = $data;
		$dataWebsiteTwo['idsite'] = 2;
		$dataWebsiteTwo['period'] = 'month';

		$idReportTwo = $this->_createReport($dataWebsiteTwo);
		// Testing getReports without parameters
		$tmp = Piwik_PDFReports_API::getInstance()->getReports();
		$report = reset($tmp);
		$this->_checkReportsEqual($report, $dataWebsiteTwo);

		$idReport = $this->_createReport($data);

		// Passing 3 parameters
		$tmp = Piwik_PDFReports_API::getInstance()->getReports($this->idSiteAccess, $data['period'], $idReport);
		$report = reset($tmp);
		$this->_checkReportsEqual($report, $data);

		// Passing only idsite
		$tmp = Piwik_PDFReports_API::getInstance()->getReports($this->idSiteAccess);
		$report = reset($tmp);
		$this->_checkReportsEqual($report, $data);

		// Passing only period
		$tmp = Piwik_PDFReports_API::getInstance()->getReports($idSite=false, $data['period']);
		$report = reset($tmp);
		$this->_checkReportsEqual($report, $data);

		// Passing only idreport
		$tmp = Piwik_PDFReports_API::getInstance()->getReports($idSite=false,$period=false, $idReport);
		$report = reset($tmp);
		$this->_checkReportsEqual($report, $data);
	}

	function test_getReports_idReportNotFound()
	{
		try {
			$report = Piwik_PDFReports_API::getInstance()->getReports($idSite=false,$period=false, $idReport = 1);
			var_dump($report);
			$this->fail();
		} catch(Exception $e) {
			$this->pass();
		}
	}

	function test_getReports_invalidPermission()
	{
		$data = $this->_getAddReportData();
		$idReport = $this->_createReport($data);

		try {
			$report = Piwik_PDFReports_API::getInstance()->getReports($idSite=44,$period=false, $idReport);
			$this->fail();
		} catch(Exception $e){
			$this->pass();
		}
	}

	function test_addReport_invalidWebsite()
	{
		$data = $this->_getAddReportData();
		$data['idsite'] = 33;
		try {
			$idReport = $this->_createReport($data);
			$this->fail();
		} catch(Exception $e){
			$this->pass();
		}
	}

	function test_addReport_invalidPeriod()
	{
		$data = $this->_getAddReportData();
		$data['period'] = 'dx';
		try {
			$idReport = $this->_createReport($data);
			$this->fail();
		} catch(Exception $e){
			$this->pass();
		}
	}

	function test_updateReport()
	{
		$dataBefore = $this->_getAddReportData();
		$idReport = $this->_createReport($dataBefore);
		$dataAfter = $this->_getYetAnotherAddReportData();
		$this->_updateReport($idReport, $dataAfter);
		$tmp = Piwik_PDFReports_API::getInstance()->getReports($idSite=false,$period=false, $idReport);
		$newReport = reset($tmp);
		$this->_checkReportsEqual($newReport, $dataAfter);
	}

	function test_deleteReport()
	{
		// Deletes non existing report throws exception
		try {
			Piwik_PDFReports_API::getInstance()->deleteReport($idReport = 1);
			$this->fail();
		} catch(Exception $e) {
			$this->pass();
		}

		$idReport = $this->_createReport($this->_getYetAnotherAddReportData());
		$this->assertEqual(1, count(Piwik_PDFReports_API::getInstance()->getReports()));
		Piwik_PDFReports_API::getInstance()->deleteReport($idReport);
		$this->assertEqual(0, count(Piwik_PDFReports_API::getInstance()->getReports()));
	}


	function _getAddReportData()
	{
		return array(
			'idsite' => $this->idSiteAccess,
			'description' => 'test description"',
			'period' => 'day',
			'type' => 'email',
			'format' => 'pdf',
			'reports' => array('UserCountry_getCountry'),
			'parameters' => array(
				'displayFormat' => '1',
				'emailMe' => true,
				'additionalEmails' => array('test@test.com', 't2@test.com')
			)
		);
	}

	function _getYetAnotherAddReportData()
	{
		return array(
			'idsite' => $this->idSiteAccess,
			'description' => 'very very long and possibly truncated description. very very long and possibly truncated description. very very long and possibly truncated description. very very long and possibly truncated description. very very long and possibly truncated description. ',
			'period' => 'month',
			'type' => 'email',
			'format' => 'pdf',
			'reports' => array('UserCountry_getContinent'),
			'parameters' => array(
				'displayFormat' => '1',
				'emailMe' => false,
				'additionalEmails' => array('blabla@ec.fr')
			)
		);
	}
	function _createReport($data)
	{
		$idReport = Piwik_PDFReports_API::getInstance()->addReport(
			$data['idsite'],
			$data['description'],
			$data['period'],
			$data['type'],
			$data['format'],
			$data['reports'],
			$data['parameters']
		);
		return $idReport;
	}

	function _updateReport($idReport, $data)
	{
		$idReport = Piwik_PDFReports_API::getInstance()->updateReport(
			$idReport,
			$data['idsite'],
			$data['description'],
			$data['period'],
			$data['type'],
			$data['format'],
			$data['reports'],
			$data['parameters']);
		return $idReport;
	}

	function _checkReportsEqual($report, $data)
	{
		foreach($data as $key => $value)
		{
			if($key == 'description') $value = substr($value,0,250);
			$this->assertEqual($value, $report[$key], "Error for $key for report ".var_export($report ,true)." and data ".var_export($data,true)." ---> %s ");
		}
	}
}
