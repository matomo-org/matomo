<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds two websites and tracks visits from two visitors on different days.
 */
class TwoSitesTwoVisitorsDifferentDays extends Fixture
{
    public $idSite1 = 1;
    public $idSite2 = 2;
    public $idGoal1 = 1;
    public $idGoal2 = 2;
    public $dateTime = '2010-01-03 11:22:33';

    public $allowConversions = false;
    const URL_IS_GOAL_WITH_CAMPAIGN_PARAMETERS = 'http://example.org/index.htm?pk_campaign=goal-matching-url-parameter';


    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
        self::setUpScheduledReports($this->idSite1);
        $this->trackVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsitesAndGoals()
    {
        $ecommerce = $this->allowConversions ? 1 : 0;

        // tests run in UTC, the Tracker in UTC
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime, $ecommerce, "Site 1");
        }

        if (!self::siteCreated($idSite = 2)) {
            // set https url in website config, which should convert action urls to https in api response
            self::createWebsite($this->dateTime, 0, "Site 2", ['http://piwik.net', 'http://example2.com', 'https://example2.com']);
        }

        if ($this->allowConversions) {
            if (!self::goalExists($idSite = 1, $idGoal = 1)) {
                APIGoals::getInstance()->addGoal($this->idSite1, 'all', 'url', 'http', 'contains', false, 5);
            }

            if (!self::goalExists($idSite = 1, $idGoal = 2)) {
                APIGoals::getInstance()->addGoal($this->idSite2, 'all', 'url', 'http', 'contains');
            }
            if (!self::goalExists($idSite = 1, $idGoal = 3)) {
                APIGoals::getInstance()->addGoal($this->idSite1, 'matching URL with campaign parameter', 'url', self::URL_IS_GOAL_WITH_CAMPAIGN_PARAMETERS, 'contains');
            }
        }

        APISitesManager::getInstance()->updateSite(
            $this->idSite1, "Site 1", $urls = null, $ecommerce = null, $siteSearch = null,
            $searchKeywordParameters = null, $searchCategoryParameters = null, $excludedIps = null,
            $excludedQueryParameters = null, $timezone = null, $currency = null, $group = null,
            $startDate = null, $excludedUserAgents = null, $keepURLFragments = 2); // KEEP_URL_FRAGMENT_NO No for idSite 1
        APISitesManager::getInstance()->updateSite(
            $this->idSite2, "Site 2", $urls = null, $ecommerce = null, $siteSearch = null,
            $searchKeywordParameters = null, $searchCategoryParameters = null, $excludedIps = null,
            $excludedQueryParameters = null, $timezone = null, $currency = null, $group = null,
            $startDate = null, $excludedUserAgents = null, $keepURLFragments = 1); // KEEP_URL_FRAGMENT_YES Yes for idSite 2
    }

    public function trackVisits()
    {
        $dateTime = $this->dateTime;
        $idSite = $this->idSite1;
        $idSite2 = $this->idSite2;

        $this->trackVisitsSite1($idSite, $dateTime);
        $this->trackVisitsSite2($idSite2, $dateTime);
    }

    /**
     * @param $idSite
     * @param $dateTime
     */
    private function trackVisitsSite1($idSite, $dateTime)
    {
        // -
        // First visitor on Idsite 1: two page views
        $datetimeSpanOverTwoDays = '2010-01-03 23:55:00';
        $visitorA = self::getTracker($idSite, $datetimeSpanOverTwoDays, $defaultInit = true);
        $visitorA->setUrlReferrer('http://referrer.com/page.htm?param=valuewith some spaces');
        $visitorA->setUrl('http://example.org/index.htm#ignoredFragment');
        $visitorA->DEBUG_APPEND_URL = '&_idts=' . Date::factory($datetimeSpanOverTwoDays)->getTimestamp();
        $visitorA->setPerformanceTimings(36, 228, 335, 1015, 209, 301);
        self::checkResponse($visitorA->doTrackPageView('first page view'));

        $visitorA->setForceVisitDateTime(Date::factory($datetimeSpanOverTwoDays)->addHour(0.1)->getDatetime());
        // testing with empty URL and empty page title
        $visitorA->setUrl('  ');
        self::checkResponse($visitorA->doTrackPageView('  '));

        // -
        // Second new visitor on Idsite 1: one page view
        $visitorB = self::getTracker($idSite, $dateTime, $defaultInit = true);
        $visitorB->enableBulkTracking();
        $visitorB->setIp('100.52.156.83');
        $visitorB->setResolution(800, 300);
        $visitorB->setForceVisitDateTime(Date::factory($dateTime)->addHour(1)->getDatetime());
        $visitorB->setUrlReferrer('');
        $visitorB->setUserAgent('Opera/9.63 (Windows NT 5.1; U; en) Presto/2.1.1');
        $visitorB->setUrl('http://example.org/products');
        $visitorB->DEBUG_APPEND_URL = '&_idts=' . Date::factory($dateTime)->addHour(1)->getTimestamp();
        $visitorB->setPerformanceTimings(62, 305, 440, 1159, 356, 440);
        self::assertTrue($visitorB->doTrackPageView('first page view'));

        // -
        // Second visitor again on Idsite 1: 2 page views 2 days later, 2010-01-05
        // If you are thinking of *decreasing* this value, just DON'T (it's important for our test case)
        $daysToGenerateVisitsFor = 10;
        for($days = 2; $days < $daysToGenerateVisitsFor; $days++) {
            $hoursOffset = $days * 24;

            $visitorB->setForceVisitDateTime(Date::factory($dateTime)->addHour($hoursOffset)->getDatetime());

            $protocol = (0 === $days % 2) ? 'http' : 'https';
            $visitorB->setUrlReferrer($protocol . '://referrer.com/Other_Page.htm');
            if( in_array($days, array(2,3,4,$daysToGenerateVisitsFor - 1)) ) {
                $visitorB->setUrl( self::URL_IS_GOAL_WITH_CAMPAIGN_PARAMETERS );
            } else {
                $visitorB->setUrl('http://example.org/index.htm');
            }

            $visitorB->setPerformanceTimings(27, 268, 356, 1025, 296, 335);
            self::assertTrue($visitorB->doTrackPageView('second visitor/two days later/a new visit'));
            // Second page view 6 minutes later
            $visitorB->setForceVisitDateTime(Date::factory($dateTime)->addHour($hoursOffset)->addHour(0.1)->getDatetime());
            $visitorB->setUrl('http://example.org/thankyou');
            $visitorB->setPerformanceTimings(0, 199, 289, 998, 198, 299);
            self::assertTrue($visitorB->doTrackPageView('second visitor/two days later/second page view😀💩😀💩'));

            // testing a strange combination causing an error in r3767
            $visitorB->setForceVisitDateTime(Date::factory($dateTime)->addHour($hoursOffset)->addHour(0.2)->getDatetime());
            self::assertTrue($visitorB->doTrackAction('mailto:test@example.org', 'link'));
            $visitorB->setForceVisitDateTime(Date::factory($dateTime)->addHour($hoursOffset)->addHour(0.25)->getDatetime());
            self::assertTrue($visitorB->doTrackAction('mailto:test@example.org/strangelink', 'link'));

            // Actions.getPageTitle tested with this title
            $visitorB->setForceVisitDateTime(Date::factory($dateTime)->addHour($hoursOffset)->addHour(0.25)->getDatetime());
            $visitorB->setPerformanceTimings(33, 356, 452, 1499, 356, 269);
            self::assertTrue($visitorB->doTrackPageView('Checkout / Purchasing...'));
            self::checkBulkTrackingResponse($visitorB->doBulkTrack());
        }
    }

    /**
     * @param $idSite2
     * @param $dateTime
     */
    private function trackVisitsSite2($idSite2, $dateTime)
    {
        // -
        // First visitor on Idsite 2: one page view, with Website referrer
        $visitorAsite2 = self::getTracker($idSite2, Date::factory($dateTime)->addHour(24)->getDatetime(), $defaultInit = true);
        $visitorAsite2->setUserAgent('Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0;)');
        $visitorAsite2->setUrlReferrer('http://only-homepage-referrer.com/');
        $visitorAsite2->setUrl('http://example2.com/home#notIgnoredFragment#');
        $visitorAsite2->DEBUG_APPEND_URL = '&_idts=' . Date::factory($dateTime)->addHour(24)->getTimestamp();
        $visitorAsite2->setPerformanceTimings(33, 144, 318, 289, 35, 50);
        self::checkResponse($visitorAsite2->doTrackPageView('Website 2 page view'));

        // test with invalid URL
        $visitorAsite2->setUrl('this is invalid url');
        // and an empty title
        $visitorAsite2->setPerformanceTimings(0, 258, 444, 325, 999, 120);
        self::checkResponse($visitorAsite2->doTrackPageView(''));

        // track a page view with a domain alias to test the aggregation of both actions
        $visitorAsite2->setUrl('http://example2alias.org/home#notIgnoredFragment#');
        $visitorAsite2->setPerformanceTimings(21, 344, 299, 245, 189, 350);
        self::checkResponse($visitorAsite2->doTrackPageView(''));
    }
}
