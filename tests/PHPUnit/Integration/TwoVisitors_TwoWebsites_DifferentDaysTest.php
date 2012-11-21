<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

/**
 * tests Tracker several websites, different days.
 * tests API for period=day/week/month/year, requesting data for both websites,
 * and requesting data for last N periods.
 * Also tests a visit that spans over 2 days.
 * And testing empty URL and empty Page name request
 * Also testing a click on a mailto counted as outlink
 * Also testing metadata API for multiple periods
 */
class Test_Piwik_Integration_TwoVisitors_TwoWebsites_DifferentDays extends IntegrationTestCase
{
    protected static $idSite1 = 1;
    protected static $idSite2 = 2;
    protected static $idGoal1 = 1;
    protected static $idGoal2 = 2;
    protected static $dateTime = '2010-01-03 11:22:33';
    protected static $allowConversions = false;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        try {
            self::setUpWebsitesAndGoals();
			self::setUpScheduledReports(self::$idSite1);
            self::trackVisits();
        } catch(Exception $e) {
            // Skip whole test suite if an error occurs while setup
            throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage());
        }
    }

	/**
	 * @group        Integration
	 * @group        TwoVisitors_TwoWebsites_DifferentDays
	 */
	public function testImagesIncludedInTests()
	{
		$this->alertWhenImagesExcludedFromTests();
	}

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        TwoVisitors_TwoWebsites_DifferentDays
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    protected function getApiToCall()
    {
        return array('VisitFrequency.get',
            'VisitsSummary.get',
            'Referers.getWebsites',
            'Actions.getPageUrls',
            'Actions.getPageTitles',
            'Actions.getOutlinks',
            'Actions.getPageTitle',
            'Actions.getPageUrl',
            'VisitorInterest.getNumberOfVisitsByDaysSinceLast');
    }

    public function getApiForTesting()
    {
        $apiToCall       = $this->getApiToCall();
        $singlePeriodApi = array('VisitsSummary.get', 'Goals.get');

        $periods = array('day', 'week', 'month', 'year');

        $result = array(
            // Request data for the last 6 periods and idSite=all
            array($apiToCall, array('idSite'       => 'all',
                                    'date'         => self::$dateTime,
                                    'periods'      => $periods,
                                    'setDateLastN' => true)),

            // Request data for the last 6 periods and idSite=1
            array($apiToCall, array('idSite'       => self::$idSite1,
                                    'date'         => self::$dateTime,
                                    'periods'      => $periods,
                                    'setDateLastN' => true,
                                    'testSuffix'   => '_idSiteOne_')),

            // We also test a single period to check that this use case (Reports per idSite in the response) works
            array($singlePeriodApi, array('idSite'       => 'all',
                                          'date'         => self::$dateTime,
                                          'periods'      => array('day', 'month'),
                                          'setDateLastN' => false,
                                          'testSuffix'   => '_NotLastNPeriods')),
        );

        // testing metadata API for multiple periods
        $apiToCall = array_diff($apiToCall, array('Actions.getPageTitle', 'Actions.getPageUrl'));
        foreach ($apiToCall as $api) {
            list($apiModule, $apiAction) = explode(".", $api);

            $result[] = array(
                'API.getProcessedReport', array('idSite'       => self::$idSite1,
                                                'date'         => self::$dateTime,
                                                'periods'      => array('day'),
                                                'setDateLastN' => true,
                                                'apiModule'    => $apiModule,
                                                'apiAction'    => $apiAction,
                                                'testSuffix'   => '_' . $api . '_firstSite_lastN')
            );
        }

        return array_merge($result, self::getApiForTestingScheduledReports(self::$dateTime, 'month'));
    }

    public function getOutputPrefix()
    {
        return 'TwoVisitors_twoWebsites_differentDays';
    }

    protected static function setUpWebsitesAndGoals()
    {
        // tests run in UTC, the Tracker in UTC
        $ecommerce = self::$allowConversions ? 1 : 0;

        self::createWebsite(self::$dateTime, $ecommerce, "Site 1");
        self::createWebsite(self::$dateTime, 0, "Site 2");

        if (self::$allowConversions) {
            Piwik_Goals_API::getInstance()->addGoal(self::$idSite1, 'all', 'url', 'http', 'contains', false, 5);
            Piwik_Goals_API::getInstance()->addGoal(self::$idSite2, 'all', 'url', 'http', 'contains');
        }
    }

    protected static function trackVisits()
    {
        $dateTime = self::$dateTime;
        $idSite   = self::$idSite1;
        $idSite2  = self::$idSite2;

        // -
        // First visitor on Idsite 1: two page views
        $datetimeSpanOverTwoDays = '2010-01-03 23:55:00';
        $visitorA                = self::getTracker($idSite, $datetimeSpanOverTwoDays, $defaultInit = true);
        $visitorA->setUrlReferrer('http://referer.com/page.htm?param=valuewith some spaces');
        $visitorA->setUrl('http://example.org/index.htm');
        $visitorA->DEBUG_APPEND_URL = '&_idts=' . Piwik_Date::factory($datetimeSpanOverTwoDays)->getTimestamp();
        self::checkResponse($visitorA->doTrackPageView('first page view'));

        $visitorA->setForceVisitDateTime(Piwik_Date::factory($datetimeSpanOverTwoDays)->addHour(0.1)->getDatetime());
        // testing with empty URL and empty page title
        $visitorA->setUrl('  ');
        self::checkResponse($visitorA->doTrackPageView('  '));

        // -
        // Second new visitor on Idsite 1: one page view
        $visitorB = self::getTracker($idSite, $dateTime, $defaultInit = true);
        $visitorB->enableBulkTracking();
	    $visitorB->setTokenAuth(self::getTokenAuth());
        $visitorB->setIp('100.52.156.83');
        $visitorB->setResolution(800, 300);
        $visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
        $visitorB->setUrlReferrer('');
        $visitorB->setUserAgent('Opera/9.63 (Windows NT 5.1; U; en) Presto/2.1.1');
        $visitorB->setUrl('http://example.org/products');
        $visitorB->DEBUG_APPEND_URL = '&_idts=' . Piwik_Date::factory($dateTime)->addHour(1)->getTimestamp();
        self::assertTrue($visitorB->doTrackPageView('first page view'));

        // -
        // Second visitor again on Idsite 1: 2 page views 2 days later, 2010-01-05
        $visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->getDatetime());
        // visitor_returning is set to 1 only when visit count more than 1
        // Temporary, until we implement 1st party cookies in PiwikTracker
        $visitorB->DEBUG_APPEND_URL .= '&_idvc=2&_viewts=' . Piwik_Date::factory($dateTime)->getTimestamp();

        $visitorB->setUrlReferrer('http://referer.com/Other_Page.htm');
        $visitorB->setUrl('http://example.org/index.htm');
        self::assertTrue($visitorB->doTrackPageView('second visitor/two days later/a new visit'));
        // Second page view 6 minutes later
        $visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.1)->getDatetime());
        $visitorB->setUrl('http://example.org/thankyou');
        self::assertTrue($visitorB->doTrackPageView('second visitor/two days later/second page view'));

        // testing a strange combination causing an error in r3767
        $visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.2)->getDatetime());
        self::assertTrue($visitorB->doTrackAction('mailto:test@example.org', 'link'));
        $visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.25)->getDatetime());
        self::assertTrue($visitorB->doTrackAction('mailto:test@example.org/strangelink', 'link'));

        // Actions.getPageTitle tested with this title
        $visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.25)->getDatetime());
        self::assertTrue($visitorB->doTrackPageView('Checkout / Purchasing...'));
        self::checkResponse($visitorB->doBulkTrack());

        // -
        // First visitor on Idsite 2: one page view, with Website referer
        $visitorAsite2 = self::getTracker($idSite2, Piwik_Date::factory($dateTime)->addHour(24)->getDatetime(), $defaultInit = true);
        $visitorAsite2->setUserAgent('Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0;)');
        $visitorAsite2->setUrlReferrer('http://only-homepage-referer.com/');
        $visitorAsite2->setUrl('http://example2.com/home');
        $visitorAsite2->DEBUG_APPEND_URL = '&_idts=' . Piwik_Date::factory($dateTime)->addHour(24)->getTimestamp();
        self::checkResponse($visitorAsite2->doTrackPageView('Website 2 page view'));
        // test with invalid URL
        $visitorAsite2->setUrl('this is invalid url');
        // and an empty title
        self::checkResponse($visitorAsite2->doTrackPageView(''));

        // Returning visitor on Idsite 2 1 day later, one page view, with chinese referer
//		$t2->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48 + 10)->getDatetime());
//		$t2->setUrlReferrer('http://www.baidu.com/s?wd=%D0%C2+%CE%C5&n=2');
//		$t2->setUrl('http://example2.com/home');
//		self::checkResponse($t2->doTrackPageView('I\'m a returning visitor...'));
    }
}
