<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * Tests the class Piwik_API_DataTableLabelFilter.
 * This is not possible as unit test, since it loads data from the API.
 */
class Test_Piwik_Integration_LabelFilter extends Test_Integration_Facade
{
	protected $dateTime = '2010-03-06 11:22:33';
	protected $idSite = null;
	
	public function getApiToTest()
	{
        $labelsToTest = array(
            // first level
            'nonExistent', 
            'dir', 
            '/0',
            '/ééé"\'... <this is cool>!',
            
            // second level
            'dir->>-nonExistent', 
            'dir->>-/file.php?foo=bar&foo2=bar',
			
            // 4 levels
            'dir2->>-sub->>-0->>-/file.php'
        );
        
        $return = array();
        foreach ($labelsToTest as $label) {
//        	var_dump(urlencode($label));
            $return[] = array('Actions.getPageUrls', array(
                'testSuffix' => '_'.preg_replace('/[^a-z0-9]*/mi', '', $label),
                'idSite' => $this->idSite,
                'date' => $this->dateTime,
                'otherRequestParameters' => array(
                    'label' => urlencode($label),
                    'expanded' => 0
                )
            ));
        }
        
        $label = 'dir';
        $return[] = array('Actions.getPageUrls', array(
            'testSuffix' => '_'.$label.'_range',
            'idSite' => $this->idSite,
            'date' => $this->dateTime,
            'otherRequestParameters' => array(
                'date' => '2010-03-06,2010-03-08',
                'label' => urlencode($label),
                'expanded' => 0
            )
        ));
        
		return $return;
	}
    
	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'LabelFilter';
	}
	
	public function setUp()
	{
		parent::setUp();
		$this->idSite = $this->createWebsite($this->dateTime);
	}

	protected function trackVisits()
	{
		$dateTime = $this->dateTime;
    	$idSite = $this->idSite;
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true, $useThirdPartyCookie = 1);
        
        $t->setUrl('http://example.org/%C3%A9%C3%A9%C3%A9%22%27...%20%3Cthis%20is%20cool%3E!');
        $this->checkResponse($t->doTrackPageView('incredible title!'));
        
        $t->setUrl('http://example.org/dir/file.php?foo=bar&foo2=bar');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
        $this->checkResponse($t->doTrackPageView('incredible title!'));
        
        $t->setUrl('http://example.org/dir/file.php?foo=bar&foo2=bar2');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t->doTrackPageView('incredible title!'));
        
        $t->setUrl('http://example.org/dir2/file.php?foo=bar&foo2=bar');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $this->checkResponse($t->doTrackPageView('incredible title!'));
        
        $t->setUrl('http://example.org/dir2/sub/0/file.php');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $this->checkResponse($t->doTrackPageView('incredible title!'));
        
        $t->setUrl('http://example.org/0');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $this->checkResponse($t->doTrackPageView('I am URL zero!'));
        
	}
}

