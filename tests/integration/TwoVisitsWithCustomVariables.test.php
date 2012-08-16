<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * Tests w/ two visits & custom variables.
 */
class Test_Piwik_Integration_TwoVisitsWithCustomVariables extends Test_Integration_Facade
{
	protected $dateTime = '2010-01-03 11:22:33';
	protected $width = 1111;
	protected $height = 222;

	protected $idSite = null;
	protected $idGoal1 = null;
	protected $idGoal2 = null;
	protected $visitorId = null;
	
	protected $useEscapedQuotes = true;
	protected $doExtraQuoteTests = true;

	public function getApiToTest()
	{
    	$apiToCall = array('VisitsSummary.get', 'CustomVariables.getCustomVariables');

		$return = array(
					array($apiToCall, array('idSite' => 'all', 'date' => $this->dateTime, 'periods' => array('day', 'week'),
									'setDateLastN' => true)),			
		);
		
		return $return;
	}

	public function getAnotherApiToTest()
	{
		$table = Piwik_CustomVariables_API::getInstance()->getCustomVariables($this->idSite, 'day', '2010-01-03');
		$idsubtable = reset($table->getRows())->getIdSubDataTable();

		// test getProcessedReport w/ custom variables
		return array(
					array('API.getProcessedReport', array('idSite' => $this->idSite, 'date' => $this->dateTime,
														  'periods' => 'day', 'apiModule' => 'CustomVariables',
														  'apiAction' => 'getCustomVariablesValuesFromNameId',
														  'testSuffix' => '__subtable',
														  'otherRequestParameters' => array('idSubtable' => $idsubtable) )),
		);
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'twoVisitsWithCustomVariables';
	}
	
	public function setUp()
	{
		parent::setUp();

	    // tests run in UTC, the Tracker in UTC
    	$this->idSite = $this->createWebsite($this->dateTime);
		$this->idGoal1 = Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'triggered js', 'manually', '', '');
		$this->idGoal2 = Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'second goal', 'manually', '', '');
	}

	protected function trackVisits()
	{
		$dateTime = $this->dateTime;
		$idSite = $this->idSite;
		$idGoal = $this->idGoal1;
		$idGoal2 = $this->idGoal2;

    	ob_start();
        $visitorA = $this->getTracker($idSite, $dateTime, $defaultInit = true);
        // Used to test actual referer + keyword position in Live!
        $visitorA->setUrlReferrer(urldecode('http://www.google.com/url?sa=t&source=web&cd=1&ved=0CB4QFjAA&url=http%3A%2F%2Fpiwik.org%2F&rct=j&q=this%20keyword%20should%20be%20ranked&ei=V8WfTePkKKLfiALrpZWGAw&usg=AFQjCNF_MGJRqKPvaKuUokHtZ3VvNG9ALw&sig2=BvKAdCtNixsmfNWXjsNyMw'));
        
        // no campaign, but a search engine to attribute the goal conversion to
        $attribution = array(
        	'',
        	'',
        	1302306504,
        	'http://www.google.com/search?q=piwik&ie=utf-8&oe=utf-8&aq=t&rls=org.mozilla:en-GB:official&client=firefox-a'
        );
        $visitorA->setAttributionInfo(json_encode($attribution));
        
        $visitorA->setResolution($this->width, $this->height);
        
        // At first, visitor custom var is set to LoggedOut
        $visitorA->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
    	$visitorA->setUrl('http://example.org/homepage');
    	$visitorA->setCustomVariable($id = 1, $name = 'VisitorType', $value = 'LoggedOut');
        $this->checkResponse($visitorA->doTrackPageView('Homepage'));
        $this->checkResponse($visitorA->doTrackGoal($idGoal2));
                
        // After login, set to LoggedIn, should overwrite previous value
        $visitorA->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
    	$visitorA->setUrl('http://example.org/user/profile');
    	$visitorA->setCustomVariable($id = 1, $name = 'VisitorType', $value = 'LoggedIn');
    	$visitorA->setCustomVariable($id = 4, $name = 'Status user', $value = 'Loggedin', $scope = 'page');
    	if ($this->useEscapedQuotes)
    	{
    		$lookingAtProfile = 'looking at &quot;profile page&quot;';
    	}
    	else
    	{
    		$lookingAtProfile = 'looking at profile page';
    	}
    	$visitorA->setCustomVariable($id = 5, $name = 'Status user', $value = $lookingAtProfile, $scope = 'page');
        $this->checkResponse($visitorA->doTrackPageView('Profile page'));
        
    	$visitorA->setCustomVariable($id = 2, $name = 'SET WITH EMPTY VALUE', $value = '');
    	$visitorA->setCustomVariable($id = 1, $name = 'Language', $value = 'FR', $scope = 'page');
    	$visitorA->setCustomVariable($id = 2, $name = 'SET WITH EMPTY VALUE PAGE SCOPE', $value = '', $scope = 'page');
    	$visitorA->setCustomVariable($id = 4, $name = 'Status user', $value = "looking at \"profile page\"", $scope = 'page');
    	$visitorA->setCustomVariable($id = 3, $name = 'Value will be VERY long and truncated', $value = 'abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----abcdefghijklmnopqrstuvwxyz----');
        $this->checkResponse($visitorA->doTrackPageView('Profile page for user *_)%'));
    	$this->checkResponse($visitorA->doTrackGoal($idGoal));
    	
    	if ($this->doExtraQuoteTests)
    	{
	    	$visitorA->setCustomVariable($id = 2, $name = 'var1', $value = 'looking at "profile page"',
	    								 $scope = 'page');
    		$visitorA->setCustomVariable($id = 3, $name = 'var2', $value = '\'looking at "\profile page"\'',
    									 $scope = 'page');
    		$visitorA->setCustomVariable($id = 4, $name = 'var3', $value = '\\looking at "\profile page"\\',
    									 $scope = 'page');
    		$this->checkResponse($visitorA->doTrackPageView('Concurrent page views'));
		}
    	
        // - 
    	// Second new visitor on Idsite 1: one page view 
        $visitorB = $this->getTracker($idSite, $dateTime, $defaultInit = true);
        $visitorB->setUrlReferrer('');
        
        $attribution = array(
        	' CAMPAIGN NAME -%20YEAH! ',
        	' CAMPAIGN%20KEYWORD - RIGHT... ',
        	1302306504,
        	'http://www.example.org/test/really?q=yes'
        );
        $visitorB->setAttributionInfo(json_encode($attribution));
        $visitorB->setResolution($this->width, $this->height);
    	$visitorB->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6');
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
    	$visitorB->setCustomVariable($id = 1, $name = 'VisitorType', $value = 'LoggedOut');
    	$visitorB->setCustomVariable($id = 2, $name = 'Othercustom value which should be truncated abcdefghijklmnopqrstuvwxyz', $value = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz');
    	$visitorB->setCustomVariable($id = -2, $name = 'not tracked', $value = 'not tracked');
    	$visitorB->setCustomVariable($id = 6, $name = 'not tracked', $value = 'not tracked');
    	$visitorB->setCustomVariable($id = 6, $name = array('not tracked'), $value = 'not tracked');
    	$visitorB->setUrl('http://example.org/homepage');
    	$this->checkResponse($visitorB->doTrackGoal($idGoal, 1000));
    	
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1.1)->getDatetime());
    	$this->checkResponse($visitorB->doTrackPageView('Homepage'));
    	
    	// DIFFERENT test -
    	// testing that starting the visit with an outlink works (doesn't trigger errors)
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2)->getDatetime());
    	$this->checkResponse($visitorB->doTrackAction('http://test.com', 'link'));

    	// hack
    	$this->visitorId = $visitorB->getVisitorId();
	}
}
