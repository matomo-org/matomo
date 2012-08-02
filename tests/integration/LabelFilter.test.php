<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * Tests the class Piwik_API_DataTableManipulator_LabelFilter.
 * This is not possible as unit test, since it loads data from the archive.
 */
class Test_Piwik_Integration_LabelFilter extends Test_Integration_Facade
{
	protected $dateTime = '2010-03-06 11:22:33';
	protected $idSite = null;
	
	public function getApiToTest()
	{
		$labelsToTest = array(
            // first level
            'shouldBeNoData' => 'nonExistent',  
            'dir' => '  dir   ', 
            '0' => urlencode('/0'),
			
			// TODO the label in the API output is ...&amp;#039;... why does it only work this way?
            'thisiscool' => urlencode('/ééé&quot;&#039;... &lt;this is cool&gt;!'),
            
            // second level
            'dirnonExistent' => 'dir>nonExistent', 
            'dirfilephpfoobarfoo2bar' => 'dir>'.urlencode('/file.php?foo=bar&foo2=bar'),
			
            // 4 levels
            'dir2sub0filephp' => 'dir2>sub>0>'.urlencode('/file.php')
        );
        
        $return = array();
        foreach ($labelsToTest as $suffix => $label)
		{
            $return[] = array('Actions.getPageUrls', array(
                'testSuffix' => '_'.$suffix,
                'idSite' => $this->idSite,
                'date' => $this->dateTime,
                'otherRequestParameters' => array(
                    'label' => $label,
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
                'label' => $label,
                'expanded' => 0
            )
        ));
        
		$return[] = array('Actions.getPageTitles', array(
            'testSuffix' => '_titles',
            'idSite' => $this->idSite,
            'date' => $this->dateTime,
            'otherRequestParameters' => array(
				// encode once for test framework and once for the label filter.
				// note: title has no blank prefixed here. in the report it has.
                'label' => urlencode('incredible title! <>,;'),
                'expanded' => 0
            )
        ));
        
		$return[] = array('Actions.getPageTitles', array(
            'testSuffix' => '_titlesRecursive',
            'idSite' => $this->idSite,
            'date' => $this->dateTime,
            'otherRequestParameters' => array(
                'label' => 
					'   '. // test trimming
					urlencode('incredible parent title! <>,;').
					'>'.
					urlencode('subtitle <>,;'),
                'expanded' => 0
            )
        ));
	
		$keyword = '&lt;&gt;&amp;\&quot;the pdo extension is required for this adapter but the extension is not loaded';
		$searchEngineTest = array(
            'testSuffix' => '_keywords_html',
            'idSite' => $this->idSite,
            'date' => $this->dateTime,
            'otherRequestParameters' => array(
               	'label' => 'Google>'.urlencode($keyword),
                'expanded' => 0
            )
        );
		$return[] = array('Referers.getSearchEngines', $searchEngineTest);
	
		$searchEngineTest['otherRequestParameters']['label'] = 'Google>'.urlencode(html_entity_decode($keyword));
		$return[] = array('Referers.getSearchEngines', $searchEngineTest);
	
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
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
        
		$t->setUrlReferrer('http://www.google.com.vn/url?sa=t&rct=j&q=%3C%3E%26%5C%22the%20pdo%20extension%20is%20required%20for%20this%20adapter%20but%20the%20extension%20is%20not%20loaded&source=web&cd=4&ved=0FjAD&url=http%3A%2F%2Fforum.piwik.org%2Fread.php%3F2%2C1011&ei=y-HHAQ&usg=AFQjCN2-nt5_GgDeg&cad=rja');
        $t->setUrl('http://example.org/%C3%A9%C3%A9%C3%A9%22%27...%20%3Cthis%20is%20cool%3E!');
        $this->checkResponse($t->doTrackPageView('incredible title! <>,;'));
        
        $t->setUrl('http://example.org/dir/file.php?foo=bar&foo2=bar');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
        $this->checkResponse($t->doTrackPageView('incredible title! <>,;'));
        
        $t->setUrl('http://example.org/dir/file.php?foo=bar&foo2=bar2');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t->doTrackPageView('incredible parent title! <>,; / subtitle <>,;'));
        
        $t->setUrl('http://example.org/dir2/file.php?foo=bar&foo2=bar');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $this->checkResponse($t->doTrackPageView('incredible title! <>,;'));
        
        $t->setUrl('http://example.org/dir2/sub/0/file.php');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $this->checkResponse($t->doTrackPageView('incredible title! <>,;'));
        
        $t->setUrl('http://example.org/0');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $this->checkResponse($t->doTrackPageView('I am URL zero!'));
        
	}
}

