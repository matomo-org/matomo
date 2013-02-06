<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Same as OneVisitorTwoVisits.test.php, but with cookie support, which incurs some slight changes
 * in the reporting data (more accurate unique visitor count, better referer tracking for goals, etc.)
 */
class Test_Piwik_Integration_OneVisitorTwoVisits_WithCookieSupport extends IntegrationTestCase
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

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        OneVisitorTwoVisits_WithCookieSupport
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array(
            'VisitTime', 'VisitsSummary', 'VisitorInterest', 'VisitFrequency', 'UserSettings',
            'UserCountry', 'Referers', 'Provider', 'Goals', 'CustomVariables', 'CoreAdminHome',
            'Actions', 'Live.getLastVisitsDetails');

        return array(
            array($apiToCall, array('idSite' => self::$idSite, 'date' => self::$dateTime))
        );
    }

    public function getOutputPrefix()
    {
        return 'OneVisitorTwoVisits_withCookieSupport';
    }

    protected static function setUpWebsitesAndGoals()
    {
        // tests run in UTC, the Tracker in UTC
        self::createWebsite(self::$dateTime);
    }

    protected static function trackVisits()
    {
        $t                   = self::getTracker(self::$idSite, self::$dateTime, $defaultInit = true, $useThirdPartyCookie = 1);
        $t->DEBUG_APPEND_URL = '&forceUseThirdPartyCookie=1';

        $dateTime = self::$dateTime;
        $idSite   = self::$idSite;

        $t->disableCookieSupport();

        $t->setUrlReferrer('http://referer.com/page.htm?param=valuewith some spaces');

        // testing URL excluded parameters
        $parameterToExclude = 'excluded_parameter';
        Piwik_SitesManager_API::getInstance()->updateSite($idSite, 'new name', $url = array('http://site.com'), $ecommerce = 0, $ss = 1, $ss_kwd = '', $ss_cat = 'notparam', $excludedIps = null, $parameterToExclude . ',anotherParameter');

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
	    // Site Search request
	    $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.42)->getDatetime());
	    $t->setUrl('http://example.org/index.htm?q=Banks Own The World');
	    self::checkResponse($t->doTrackPageView('Site Search request'));

	    // Final page view (after 27 min)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.45)->getDatetime());
        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('Looking at homepage after site search...'));

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
