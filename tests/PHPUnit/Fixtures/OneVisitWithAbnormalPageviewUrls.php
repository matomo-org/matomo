<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;

/**
 * Adds one site and tracks one visit w/ pageview URLs that are not normalized.
 * These URLs use different protocols and a mix of lowercase & uppercase letters.
 */
class Test_Piwik_Fixture_OneVisitWithAbnormalPageviewUrls extends Test_Piwik_BaseFixture
{
    public $dateTime = '2010-03-06 11:22:33';
    public $idSite = 1;

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
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }
    }

    private function trackVisits()
    {
        $dateTime = $this->dateTime;
        $idSite = $this->idSite;
        $t = self::getTracker($idSite, $dateTime, $defaultInit = true, $useThirdPartyCookie = 1);

        $t->setUrlReferrer('http://www.google.com/search?q=piwik');
        $t->setUrl('http://example.org/foo/bar.html');
        self::checkResponse($t->doTrackPageView('http://incredible.title/'));

        $t->setUrl('https://example.org/foo/bar.html');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.2)->getDatetime());
        self::checkResponse($t->doTrackPageView('https://incredible.title/'));

        $t->setUrl('https://wWw.example.org/foo/bar2.html');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.3)->getDatetime());
        self::checkResponse($t->doTrackPageView('http://www.incredible.title/'));

        $t->setUrl('http://WwW.example.org/foo/bar2.html');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.4)->getDatetime());
        self::checkResponse($t->doTrackPageView('https://www.incredible.title/'));

        $t->setUrl('http://www.example.org/foo/bar3.html');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.5)->getDatetime());
        self::checkResponse($t->doTrackPageView('incredible.title/'));

        $t->setUrl('https://example.org/foo/bar4.html');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.6)->getDatetime());
        self::checkResponse($t->doTrackPageView('incredible.title/'));
    }
}
