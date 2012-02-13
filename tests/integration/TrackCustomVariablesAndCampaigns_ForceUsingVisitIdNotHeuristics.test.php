<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * Test tracker & API when forcing the use of visit ID instead of heuristics. Also
 * tests campaign tracking.
 */
class Test_Piwik_Integration_TrackCustomVariablesAndCampaigns_ForceUsingVisitIdNotHeuristics extends Test_Integration_Facade
{
	protected $dateTime = '2009-01-04 00:11:42';
	protected $idSite = null;
	protected $idGoal = null;

	public function getApiToTest()
	{
		$apiToCall = array('VisitsSummary.get', 'Referers.getCampaigns', 'Referers.getWebsites');

		return array(
	        // TOTAL should be: 1 visit, 1 converted goal, 1 page view
	        array($apiToCall, array('idSite' => $this->idSite, 'date' => $this->dateTime))
		);
	}

	public function getControllerActionsToTest()
	{
		return array();
	}

	public function getOutputPrefix()
	{
		return 'PiwikTracker_trackForceUsingVisitId_insteadOfHeuristics_alsoTestsCampaignTracking';
	}
	
	public function setUp()
	{
		parent::setUp();
		
		$this->idSite = $this->createWebsite($this->dateTime);
		$this->idGoal = Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'triggered js', 'manually', '', '');
	}

	protected function trackVisits()
	{
		$dateTime = $this->dateTime;
		$idSite = $this->idSite;
        $idGoal = $this->idGoal;
		
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);

        // Record 1st page view
        $t->setUrl( 'http://example.org/index.htm?utm_campaign=GA Campaign&piwik_kwd=Piwik kwd&utm_term=GA keyword SHOULD NOT DISPLAY#pk_campaign=NOT TRACKED!!&pk_kwd=NOT TRACKED!!' );
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));
        
        $visitorId = $t->getVisitorId();
        $this->assertTrue(strlen($visitorId) == 16);
        
        // test setting/getting the first party cookie via the PHP Tracking Client 
        $_COOKIE['_pk_id_1_1fff'] = 'ca0afe7b6b692ff5.1302307497.1.1302307497.1302307497';
        $_COOKIE['_pk_ref_1_1fff'] = '["YEAH","RIGHT!",1302307497,"http://referrer.example.org/page/sub?query=test&test2=test3"]';
        $_COOKIE['_pk_cvar_1_1fff'] = '{"1":["VAR 1 set, var 2 not set","yes"],"3":["var 3 set","yes!!!!"]}';
        $this->assertTrue($t->getVisitorId() == 'ca0afe7b6b692ff5');
        $this->assertTrue($t->getAttributionInfo() == $_COOKIE['_pk_ref_1_1fff']);
        $this->assertTrue($t->getCustomVariable(1) == array("VAR 1 set, var 2 not set", "yes"));
        $this->assertTrue($t->getCustomVariable(2) == false);
        $this->assertTrue($t->getCustomVariable(3) == array("var 3 set", "yes!!!!"));
        $this->assertTrue($t->getCustomVariable(4) == false);
        $this->assertTrue($t->getCustomVariable(5) == false);
        $this->assertTrue($t->getCustomVariable(6) == false);
        $this->assertTrue($t->getCustomVariable(-1) == false);
        unset($_COOKIE['_pk_id_1_1fff']);
        unset($_COOKIE['_pk_ref_1_1fff']);
        unset($_COOKIE['_pk_cvar_1_1fff']);
        
        // Create a new Tracker object, with different attributes
        $t2 = $this->getTracker($idSite, $dateTime, $defaultInit = false);
        
        // Make sure the ID is different at first
        $visitorId2 = $t2->getVisitorId();
        $this->assertTrue($visitorId != $visitorId2);
        
        // Then force the visitor ID 
        $t2->setVisitorId($visitorId);
        
        // And Record a Goal: The previous visit should be updated rather than a new visit Created 
        $t2->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t2->doTrackGoal($idGoal, $revenue = 42.256));
        
        // Yet another visitor, this time with a manual goal conversion, which should be credited to the campaign
        $t3 = $this->getTracker($idSite, $dateTime);
        $t3->setUrlReferrer('http://example.org/referrer');
        $t3->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1.3)->getDatetime());
        // fake a website ref cookie, the campaign should be credited for conversion, not referrer.example.com nor example.org 
        $t3->DEBUG_APPEND_URL = '&_ref=http%3A%2F%2Freferrer.example.com%2Fpage%2Fsub%3Fquery%3Dtest%26test2%3Dtest3';
        $t3->setUrl( 'http://example.org/index.htm#pk_campaign=CREDITED TO GOAL PLEASE' );
        $this->checkResponse($t3->doTrackGoal($idGoal, 42));
	}
}

