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
			'12.12.12.12',
			'13.13.13.*',
			'14.14.*.*',
			'15.*.*.*',
			'255.1.1.1',
			'255.150.150.150',
			'155.*.*.*',
			'255.255.100.*'
		);
		$visit = new Test_Piwik_TrackerVisit_public();
		foreach($excludedIps as $idExcludedId => $excludedIp) 
		{
    		$idsite = Piwik_SitesManager_API::getInstance()->addSite("name","http://piwik.net/", $excludedIp);
    		$visit->setRequest(array('idsite' => $idsite));
    		
    		// test that IPs within the range, or the given IP, are excluded
    		$ips = $this->getIpsFromWildcardIp($excludedIp);
    		foreach($ips as $testIpIsExcluded)
    		{
        		$testIpIsExcluded = ip2long($testIpIsExcluded);
        		$this->assertTrue($testIpIsExcluded !== false);
        		$testIpIsExcluded = sprintf("%u", $testIpIsExcluded);
    			$this->assertTrue($visit->public_isVisitorIpExcluded($testIpIsExcluded), Piwik_Common::long2ip($testIpIsExcluded) . " is not excluded");
    		}
    		
    		// test that all other IPs (set as being exclusively out of any other IP ranges)
    		// are included in the tracking
    		foreach($excludedIps as $idIncludedIp => $includedIp)
    		{
    			if($idIncludedIp == $idExcludedId)
    			{
    				continue;
    			}
        		$ips = $this->getIpsFromWildcardIp($includedIp);
        		foreach($ips as $testIpIsIncluded)
        		{
            		$testIpIsIncluded = ip2long($testIpIsIncluded);
            		$this->assertTrue($testIpIsIncluded !== false);
            		$testIpIsIncluded = sprintf("%u", $testIpIsIncluded);
        			$this->assertFalse($visit->public_isVisitorIpExcluded($testIpIsIncluded), Piwik_Common::long2ip($testIpIsIncluded) . " is excluded by the rule ". $excludedIp);
        		}
    		}
		}
	}
	
	/**
	 * Given an IP (containing wildcards or not), returns IP within the range (replacing wildcards with proper values)
	 * @param $wildcardIp 145.65.*.*
	 * @return array (145.65.1.1, 145.65.255.255, etc.)
	 */
	private function getIpsFromWildcardIp($wildcardIp)
	{
		if(substr_count($wildcardIp, '*') === 0 )
		{
			return array($wildcardIp);
		}
		
		$ips = array();
		foreach(array(1,50,100,255) as $byte)
		{
			$ips[] = str_replace('*', $byte, $wildcardIp); 
		}
		return $ips;
	}
}

class Test_Piwik_TrackerVisit_public extends Piwik_Tracker_Visit {
	public function public_isVisitorIpExcluded( $ip )
	{
		return $this->isVisitorIpExcluded($ip);
	}
}
