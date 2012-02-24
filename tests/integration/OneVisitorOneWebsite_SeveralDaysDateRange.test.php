<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * testing period=range use case. Recording data before and after, checking that the requested range is processed correctly 
 */
class Test_Piwik_Integration_OneVisitorOneWebsite_SeveralDaysDateRange extends Test_Integration_Facade
{
	protected $dateTimes = array(
		'2010-12-14 01:00:00',
		'2010-12-15 01:00:00',
		'2010-12-25 01:00:00',
		'2011-01-15 01:00:00',
		'2011-01-16 01:00:00',
	);
	protected $idSite = null;

	public function getApiToTest()
	{
		return array(
			// range test
			array('MultiSites.getAll', array('idSite' => $this->idSite, 'date' => '2010-12-15,2011-01-15',
											 'periods' => array('range'))),
			
			// test several dates (tests use of IndexedByDate w/ 'date1,date2,etc.')
			array('MultiSites.getAll', array('idSite' => $this->idSite, 'date' => '2010-12-10',
											 'periods' => array('day'), 'setDateLastN' => true,
											 'testSuffix' => '_IndexedByDate'))
		);
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'oneVisitor_oneWebsite_severalDays_DateRange';
	}
	
	public function setUp()
	{
		parent::setUp();
		
		$this->idSite = $this->createWebsite($this->dateTimes[0]);
	}

	protected function trackVisits()
	{
		$dateTimes = $this->dateTimes;
    	$idSite = $this->idSite;

    	$i = 0;
    	foreach($dateTimes as $dateTime)
    	{
    		$i++;
	        $visitor = $this->getTracker($idSite, $dateTime, $defaultInit = true);
    		// Fake the visit count cookie 
	        $visitor->setDebugStringAppend("&_idvc=$i");
	        
	        $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
	    	$visitor->setUrl('http://example.org/homepage');
	        $this->checkResponse($visitor->doTrackPageView('ou pas'));
	        
	        // Test change the IP, the visit should not be split but recorded to the same idvisitor
	        $visitor->setIp('200.1.15.22');
	        
	        $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
	    	$visitor->setUrl('http://example.org/news');
	        $this->checkResponse($visitor->doTrackPageView('ou pas'));
	
	        $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
	    	$visitor->setUrl('http://example.org/news');
	        $this->checkResponse($visitor->doTrackPageView('ou pas'));
    	}
	}
}

