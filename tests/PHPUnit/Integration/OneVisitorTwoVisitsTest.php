<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * This use case covers many simple tracking features.
 * - Tracking Goal by manual trigger, and URL matching, with custom revenue
 * - Tracking the same Goal twice only records it once
 * - Tracks 4 page views: 3 clicks and a file download
 * - URLs parameters exclude is tested
 * - In a returning visit, tracks a Goal conversion
 *   URL matching, with custom referer and keyword
 *   NO cookie support
 */
class Test_Piwik_Integration_OneVisitorTwoVisits extends IntegrationTestCase
{
    protected static $idSite   = 1;
    protected static $dateTime = '2010-03-06 11:22:33';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        try {
            self::setUpWebsitesAndGoals();
            self::trackVisits();
        } catch(Exception $e) {
            // Skip whole test suite if an error occurs while setup
            throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage());
        }
    }
	
	public function setUp()
	{
		Piwik_API_Proxy::getInstance()->setHideIgnoredFunctions(false);
	}
	
	public function tearDown()
	{
		Piwik_API_Proxy::getInstance()->setHideIgnoredFunctions(true);
	}
	
    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        OneVisitorTwoVisits
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $enExtraParam = array('expanded' => 1, 'flat' => 1, 'include_aggregate_rows' => 0, 'translateColumnNames' => 1);
        $bulkUrls     = array(
            "idSite=".self::$idSite."&date=2010-03-06&format=json&expanded=1&period=day&method=VisitsSummary.get",
            "idSite=".self::$idSite."&date=2010-03-06&format=xml&expanded=1&period=day&method=VisitsSummary.get",
            "idSite=".self::$idSite."&date=2010-03-06&format=json&expanded=1&period=day&method="
                . "VisitorInterest.getNumberOfVisitsPerVisitDuration"
        );
        foreach ($bulkUrls as &$url)
        {
        	$url = urlencode($url);
        }
        return array(
            array('all', array('idSite' => self::$idSite, 'date' => self::$dateTime)),

            // test API.get (for bug that incorrectly reorders columns of CSV output)
            //   note: bug only affects rows after first
            array('API.get', array('idSite'                 => self::$idSite,
                                   'date'                   => '2009-10-01',
                                   'format'                 => 'csv',
                                   'periods'                => array('month'),
                                   'setDateLastN'           => true,
                                   'otherRequestParameters' => $enExtraParam,
                                   'language'               => 'en',
                                   'testSuffix'             => '_csv')),

            array('API.getBulkRequest', array('otherRequestParameters' => array('urls' => $bulkUrls))),
            
            // test API.getProcessedReport w/ report that is its own 'actionToLoadSubTables'
            array('API.getProcessedReport', array('idSite'		  => self::$idSite,
            									  'date'		  => self::$dateTime,
            									  'periods'		  => array('week'),
            									  'apiModule'	  => 'Actions',
            									  'apiAction'	  => 'getPageUrls',
            									  'supertableApi' => 'Actions.getPageUrls',
            									  'testSuffix'	  => '__subtable')),

			// test hideColumns && showColumns parameters
			array('VisitsSummary.get', array('idSite' => self::$idSite, 'date' => self::$dateTime, 'periods' => 'day',
											 'testSuffix' => '_hideColumns_',
											 'otherRequestParameters' => array(
											 	'hideColumns' => 'nb_visits_converted,max_actions,bounce_count,nb_hits,'
											 		.'nb_visits,nb_actions,sum_visit_length,avg_time_on_site'
											 ))),
			array('VisitsSummary.get', array('idSite' => self::$idSite, 'date' => self::$dateTime, 'periods' => 'day',
											 'testSuffix' => '_showColumns_',
											 'otherRequestParameters' => array(
											 	'showColumns' => 'nb_visits,nb_actions,nb_hits'
											 ))),
			array('VisitsSummary.get', array('idSite' => self::$idSite, 'date' => self::$dateTime, 'periods' => 'day',
											 'testSuffix' => '_hideAllColumns_',
											 'otherRequestParameters' => array(
											 	'hideColumns' => 'nb_visits_converted,max_actions,bounce_count,nb_hits,'
											 		.'nb_visits,nb_actions,sum_visit_length,avg_time_on_site,'
											 		.'bounce_rate,nb_uniq_visitors,nb_actions_per_visit,'
											 ))),
			
			// test hideColumns w/ API.getProcessedReport
			array('API.getProcessedReport', array('idSite' => self::$idSite, 'date' => self::$dateTime,
												  'periods' => 'day', 'apiModule' => 'Actions',
												  'apiAction' => 'getPageTitles', 'testSuffix' => '_hideColumns_',
												  'otherRequestParameters' => array(
												  	'hideColumns' => 'nb_visits_converted,xyzaug,entry_nb_visits,'.
												  		'bounce_rate,nb_hits,nb_visits,avg_time_on_page'
												  ))),
			
			array('API.getProcessedReport', array('idSite' => self::$idSite, 'date' => self::$dateTime,
												  'periods' => 'day', 'apiModule' => 'Actions',
												  'apiAction' => 'getPageTitles', 'testSuffix' => '_showColumns_',
												  'otherRequestParameters' => array(
												  	'showColumns' => 'nb_visits_converted,xuena,entry_nb_visits,'.
												  		'bounce_rate,nb_hits'
												  ))),
			array('API.getProcessedReport', array('idSite' => self::$idSite, 'date' => self::$dateTime,
												  'periods' => 'day', 'apiModule' => 'VisitTime',
												  'apiAction' => 'getVisitInformationPerServerTime',
												  'testSuffix' => '_showColumnsWithProcessedMetrics_',
												  'otherRequestParameters' => array(
												  	'showColumns' => 'nb_visits,revenue'
												  ))),
			
			// test hideColumns w/ expanded=1
			array('Actions.getPageTitles', array('idSite' => self::$idSite, 'date' => self::$dateTime,
												 'periods' => 'day', 'testSuffix' => '_hideColumns_',
												  'otherRequestParameters' => array(
												  	'hideColumns' => 'nb_visits_converted,entry_nb_visits,'.
												  		'bounce_rate,nb_hits,nb_visits,sum_time_spent,'.
												  		'entry_sum_visit_length,entry_bounce_count,exit_nb_visits,'.
												  		'entry_nb_uniq_visitors,exit_nb_uniq_visitors,entry_nb_actions',
												  	'expanded' => '1'
												 ))),
        );
    }
	
	/**
	 * Test that Archive_Single::preFetchBlob won't fetch extra unnecessary blobs.
	 * 
	 * @group        Integration
	 * @group        OneVisitorTwoVisits
	 */
	public function testArchiveSinglePreFetchBlob()
	{
		$archive = Piwik_Archive::build(self::$idSite, 'day', self::$dateTime);
		$archive->preFetchBlob('Actions_actions');
		$cache = $archive->getBlobCache();
		
		$foundSubtable = false;
		
		$this->assertTrue(count($cache) > 0, "empty blob cache");
		foreach ($cache as $name => $value)
		{
			$this->assertTrue(strpos($name, "Actions_actions_url") === false, "found blob w/ name '$name'");
			
			if (strpos($name, "Actions_actions_") !== false)
			{
				$foundSubtable = true;
			}
		}
		
		$this->assertTrue($foundSubtable, "Actions_actions subtable was not loaded");
	}

    protected static function setUpWebsitesAndGoals()
    {
        // tests run in UTC, the Tracker in UTC
        self::createWebsite(self::$dateTime);
    }

    protected static function trackVisits()
    {
        $dateTime = self::$dateTime;
        $idSite   = self::$idSite;

        Piwik_SitesManager_API::getInstance()->setSiteSpecificUserAgentExcludeEnabled(false);
        
        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);

        $t->disableCookieSupport();

        $t->setUrlReferrer('http://referer.com/page.htm?param=valuewith some spaces');

        // testing URL excluded parameters
        $parameterToExclude = 'excluded_parameter';
        Piwik_SitesManager_API::getInstance()->updateSite($idSite, 'new name', $url = array('http://site.com'), $ecommerce = 0, $siteSearch = null,
	        $searchKeywordParameters = null,
	        $searchCategoryParameters = null, $excludedIps = null, $parameterToExclude . ',anotherParameter',
	        $timezone = null, $currency = null, $group = null, $startDate = null,
	        // test that visit won't be excluded since site-specific exclude is not enabled
	        $excludedUserAgents = 'mozilla'
        );

        // Record 1st page view
        $urlPage1 = 'http://example.org/index.htm?excluded_Parameter=SHOULD_NOT_DISPLAY&parameter=Should display';
        $t->setUrl($urlPage1);
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // testing that / and index.htm above record with different URLs
        // Recording the 2nd page after 3 minutes
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.05)->getDatetime());
        $urlPage2 = 'http://example.org/';
        $t->setUrl($urlPage2);
//		$t->setUrlReferrer($urlPage1);
        self::checkResponse($t->doTrackPageView('Second page view - should be registered as URL /'));

//		$t->setUrlReferrer($urlPage2);
        // Click on external link after 6 minutes (3rd action)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
        self::checkResponse($t->doTrackAction('http://dev.piwik.org/svn', 'link'));

        // Click on file download after 12 minutes (4th action)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
        self::checkResponse($t->doTrackAction('http://piwik.org/path/again/latest.zip', 'download'));

        // Click on two more external links, one the same as before (5th & 6th actions)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.22)->getDateTime());
        self::checkResponse($t->doTrackAction('http://outlinks.org/other_outlink', 'link'));
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.25)->getDateTime());
        self::checkResponse($t->doTrackAction('http://dev.piwik.org/svn', 'link'));

        // Create Goal 1: Triggered by JS, after 18 minutes
        $idGoal = Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());

        // Change to Thai  browser to ensure the conversion is credited to FR instead (the visitor initial country)
        $t->setBrowserLanguage('th');
        self::checkResponse($t->doTrackGoal($idGoal, $revenue = 42));

        // Track same Goal twice (after 24 minutes), should only be tracked once
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        self::checkResponse($t->doTrackGoal($idGoal, $revenue = 42));

        $t->setBrowserLanguage('fr');
        // Final page view (after 27 min)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.45)->getDatetime());
        $t->setUrl('http://example.org/index.htm#ignoredFragment');
        self::checkResponse($t->doTrackPageView('Looking at homepage (again)...'));

        // -
        // End of first visit: 24min

        // Create Goal 2: Matching on URL
        Piwik_Goals_API::getInstance()->addGoal($idSite, 'matching purchase.htm', 'url', '(.*)store\/purchase\.(.*)', 'regex', false, $revenue = 1);

        // -
        // Start of returning visit, 1 hour after first page view
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
        $t->setUrl('http://example.org/store/purchase.htm');
        $t->setUrlReferrer('http://search.yahoo.com/search?p=purchase');
        // Temporary, until we implement 1st party cookies in PiwikTracker
        $t->DEBUG_APPEND_URL = '&_idvc=2';

        // Goal Tracking URL matching, testing custom referer including keyword
        self::checkResponse($t->doTrackPageView('Checkout/Purchasing...'));
        // -
        // End of second visit
    }
}
