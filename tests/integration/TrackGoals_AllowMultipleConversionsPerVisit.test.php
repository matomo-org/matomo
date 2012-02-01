<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * Tests API methods with goals that do and don't allow multiple
 * conversions per visit.
 */
class Test_Piwik_Integration_TrackGoals_AllowMultipleConversionsPerVisit extends Test_Integration_Facade
{
	protected $dateTime = null;
	protected $idSite = null;
	protected $idGoal_OneConversionPerVisit = null;
	protected $idGoal_MultipleConversionPerVisit = null;

	public function getApiToTest()
	{
		$apiToCall = array('VisitTime.getVisitInformationPerServerTime', 'VisitsSummary.get');

		return array(
			array($apiToCall, array('idSite' => $this->idSite, 'date' => $this->dateTime))
		);
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'trackGoals_allowMultipleConversionsPerVisit';
	}
	
	public function setUp()
	{
		parent::setUp();

		$this->dateTime = '2009-01-04 00:11:42';
		$this->idSite = $this->createWebsite($this->dateTime);

		// First, a goal that is only recorded once per visit
        $this->idGoal_OneConversionPerVisit = Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'triggered js ONCE', 'title', 'Thank you', 'contains', $caseSensitive=false, $revenue=10, $allowMultipleConversions = false);

        // Second, a goal allowing multiple conversions
        $this->idGoal_MultipleConversionPerVisit = Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'triggered js MULTIPLE ALLOWED', 'manually', '', '', $caseSensitive=false, $revenue=10, $allowMultipleConversions = true);
	}

	public function test_RunAllTests()
	{
		$idSite = $this->idSite;
	
		parent::test_RunAllTests();

        // test delete is working as expected
        $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);
        $this->assertTrue( 2 == count($goals) );
        Piwik_Goals_API::getInstance()->deleteGoal($idSite, $this->idGoal_OneConversionPerVisit);
        Piwik_Goals_API::getInstance()->deleteGoal($idSite, $this->idGoal_MultipleConversionPerVisit);
        $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);
        $this->assertTrue( empty($goals) );
	}
	
	protected function trackVisits()
	{
		$dateTime = $this->dateTime;
		$idSite = $this->idSite;
		$idGoal_OneConversionPerVisit = $this->idGoal_OneConversionPerVisit;
		$idGoal_MultipleConversionPerVisit = $this->idGoal_MultipleConversionPerVisit;
		
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);

        // Record 1st goal, should only have 1 conversion
        $t->setUrl( 'http://example.org/index.htm' );
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t->doTrackPageView('Thank you mate'));
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal_OneConversionPerVisit, $revenue = 10000000));

        // Record 2nd goal, should record both conversions
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.5)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal_MultipleConversionPerVisit, $revenue = 300));
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.6)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal_MultipleConversionPerVisit, $revenue = 366));
        
        // Update & set to not allow multiple
        $goals = Piwik_Goals_API::getInstance()->getGoals($idSite);
        $goal = $goals[$idGoal_OneConversionPerVisit];
        $this->assertTrue($goal['allow_multiple'] == 0);
        Piwik_Goals_API::getInstance()->updateGoal($idSite, $idGoal_OneConversionPerVisit, $goal['name'], @$goal['match_attribute'], @$goal['pattern'], @$goal['pattern_type'], @$goal['case_sensitive'], $goal['revenue'], $goal['allow_multiple'] = 1);
        $this->assertTrue($goal['allow_multiple'] == 1);
        
        // 1st goal should Now be tracked
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.61)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal_OneConversionPerVisit, $revenue = 656));
	}
}
