<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;
use Piwik\Tracker\Visit;

/**
 * Adds one site and tracks a couple visits using a custom visitor ID.
 */
class Test_Piwik_Fixture_FewVisitsWithSetVisitorId extends Test_Piwik_BaseFixture
{
    public $idSite = 1;
    public $dateTime = '2010-03-06 11:22:33';

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
        // tests run in UTC, the Tracker in UTC
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }
    }

    private function trackVisits()
    {
        $dateTime = $this->dateTime;
        $idSite = $this->idSite;

        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);

        // First, some basic tests
        self::settingInvalidVisitorIdShouldThrow($t);

        // We create VISITOR A
        $t->setUrl('http://example.org/index.htm');
        $t->setVisitorId(Visit::generateUniqueVisitorId());
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // VISITOR B: few minutes later, we trigger the same tracker but with a custom visitor ID,
        // => this will create a new visit B
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.05)->getDatetime());
        $t->setUrl('http://example.org/index2.htm');
        $t->setVisitorId(Visit::generateUniqueVisitorId());
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // This new visit B will have 2 page views
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.org/index3.htm');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // total = 2 visitors, 3 page views

    }

    private static function settingInvalidVisitorIdShouldThrow(PiwikTracker $t)
    {
        try {
            $t->setVisitorId('test');
            $this->fail('should throw');
        } catch (Exception $e) {
            //OK
        }
        try {
            $t->setVisitorId('61e8');
            $this->fail('should throw');
        } catch (Exception $e) {
            //OK
        }
        try {
            $t->setVisitorId('61e8cc2d51fea26dabcabcabc');
            $this->fail('should throw');
        } catch (Exception $e) {
            //OK
        }
    }
}
