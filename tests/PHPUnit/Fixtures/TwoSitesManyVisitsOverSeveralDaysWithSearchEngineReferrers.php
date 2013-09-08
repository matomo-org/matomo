<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;
use Piwik\Plugins\Goals\API;

/**
 * Adds one website and tracks visits on different days over a month
 * using referrer URLs with search engines.
 */
class Test_Piwik_Fixture_TwoSitesManyVisitsOverSeveralDaysWithSearchEngineReferrers extends Test_Piwik_BaseFixture
{
    public $today = '2010-03-06 11:22:33';
    public $idSite = 1;
    public $idSite2 = 2;
    public $keywords = array(
        'free > proprietary', // testing a keyword containing >
        'peace "," not war', // testing a keyword containing ,
        'justice )(&^#%$ NOT corruption!',
    );

    public function setUp()
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown()
    {
        // empty
    }

    private function setUpWebsitesAndGoals()
    {
        $siteCreated = '2010-02-01 11:22:33';

        self::createWebsite($siteCreated);
        API::getInstance()->addGoal($this->idSite, 'triggered php', 'manually', '', '');
        API::getInstance()->addGoal(
            $this->idSite, 'another triggered php', 'manually', '', '', false, false, true);

        self::createWebsite($siteCreated);
    }

    private function trackVisits()
    {
        $dateTime = $this->today;
        $idSite = $this->idSite;
        $idSite2 = $this->idSite2;

        $mozillaUserAgent = "Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.1.6) Gecko/20100101 Firefox/6.0";
        $operaUserAgent = "Opera/9.80 (iPod; U; CPU iPhone OS 4_3_3 like Mac OS X; ja-jp) Presto/2.9.181 Version/12.00";

        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);
        $t->setTokenAuth(self::getTokenAuth());
        $t->enableBulkTracking();
        for ($daysIntoPast = 30; $daysIntoPast >= 0; $daysIntoPast--) {
            // Visit 1: referrer website + test page views
            $visitDateTime = Date::factory($dateTime)->subDay($daysIntoPast)->getDatetime();

            $t->setNewVisitorId();
            $t->setIdSite($idSite);
            $t->setUserAgent($mozillaUserAgent);

            $t->setUrlReferrer('http://www.referrer' . ($daysIntoPast % 5) . '.com/theReferrerPage' . ($daysIntoPast % 2) . '.html');
            $t->setUrl('http://example.org/my/dir/page' . ($daysIntoPast % 4) . '?foo=bar&baz=bar');
            $t->setForceVisitDateTime($visitDateTime);
            $t->setGenerationTime($daysIntoPast * 100 + 100);
            self::assertTrue($t->doTrackPageView('incredible title ' . ($daysIntoPast % 3)));

            // Trigger goal n°1 once
            self::assertTrue($t->doTrackGoal(1));

            // Trigger goal n°2 twice
            self::assertTrue($t->doTrackGoal(2));
            $t->setForceVisitDateTime(Date::factory($visitDateTime)->addHour(0.1)->getDatetime());
            self::assertTrue($t->doTrackGoal(2));

            // VISIT 2: search engine
            $t->setForceVisitDateTime(Date::factory($visitDateTime)->addHour(3)->getDatetime());
            $t->setUrlReferrer('http://google.com/search?q=' . urlencode($this->keywords[$daysIntoPast % 3]));
            $t->setGenerationTime($daysIntoPast * 100 + 200);
            self::assertTrue($t->doTrackPageView('not an incredible title '));

            // VISIT 1 for idSite = 2
            $t->setIdSite($idSite2);
            $t->setNewVisitorId();
            $t->setUserAgent($daysIntoPast % 2 == 0 ? $mozillaUserAgent : $operaUserAgent);

            $t->setForceVisitDateTime($visitDateTime);
            $t->setUrl('http://example.org/');
            $t->setGenerationTime($daysIntoPast * 100 + 300);
            self::assertTrue($t->doTrackPageView('so-so page title'));
        }
        self::checkBulkTrackingResponse($t->doBulkTrack());
    }
}
