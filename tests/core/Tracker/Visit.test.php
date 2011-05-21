<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

require_once 'Tracker/Visit.php';

require_once "Database.test.php";
class Test_Piwik_TrackerVisit extends Test_Database
{
	public function setUp()
	{
		parent::setUp();
		$GLOBALS['PIWIK_TRACKER_MODE'] = true;

		// setup the access layer
		$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = true;
		Zend_Registry::set('access', $pseudoMockAccess);
	}

	public function tearDown()
	{
		parent::tearDown();
		$GLOBALS['PIWIK_TRACKER_MODE'] = false;
	}

	function test_isVisitorIpExcluded()
	{
		$excludedIps = array(
			'12.12.12.12' => array(
				'12.12.12.12' => true,
				'12.12.12.11' => false,
				'12.12.12.13' => false,
				'0.0.0.0' => false,
				'255.255.255.255' => false
			),
			'12.12.12.12/32' => array(
				'12.12.12.12' => true,
				'12.12.12.11' => false,
				'12.12.12.13' => false,
				'0.0.0.0' => false,
				'255.255.255.255' => false
			),
			'12.12.12.*' => array(
				'12.12.12.0' => true,
				'12.12.12.255' => true,
				'12.12.12.12' => true,
				'12.12.11.255' => false,
				'12.12.13.0' => false,
				'0.0.0.0' => false,
				'255.255.255.255' => false,
			),
			'12.12.12.0/24' => array(
				'12.12.12.0' => true,
				'12.12.12.255' => true,
				'12.12.12.12' => true,
				'12.12.11.255' => false,
				'12.12.13.0' => false,
				'0.0.0.0' => false,
				'255.255.255.255' => false,
			),
// add some ipv6 addresses!
		);
		$visit = new Test_Piwik_TrackerVisit_public();
		foreach($excludedIps as $excludedIp => $tests)
		{
			$idsite = Piwik_SitesManager_API::getInstance()->addSite("name","http://piwik.net/",$ecommerce=0, $excludedIp);
			$visit->setRequest(array('idsite' => $idsite));

			// test that IPs within the range, or the given IP, are excluded
			foreach($tests as $ip => $expected)
			{
				$testIpIsExcluded = Piwik_IP::P2N($ip);
				$this->assertTrue($visit->public_isVisitorIpExcluded($testIpIsExcluded) === $expected, $ip . " is not excluded in " . $excludedIp);
			}
		}
	}
}

class Test_Piwik_TrackerVisit_public extends Piwik_Tracker_Visit {
	public function public_isVisitorIpExcluded( $ip )
	{
		return $this->isVisitorIpExcluded($ip);
	}
}
