<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;

/**
 * Adds two sites and tracks several visits all in the past.
 */
class Test_Piwik_Fixture_TwoSitesVisitsInPast extends Test_Piwik_BaseFixture
{
    public $dateTimeFirstDateWebsite1 = '2010-03-06 01:22:33';
    public $dateTimeDateInPastWebsite1 = '2010-01-06 01:22:33';
    public $dateTimeFirstDateWebsite2 = '2010-01-03 20:22:33';
    public $dateTimeDateInPastWebsite2 = '2009-10-30 01:22:33';
    public $idSite = 1;
    public $idSite2 = 2;

    public function setUp()
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown()
    {
        // empty
    }

    public function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTimeFirstDateWebsite1);
        }

        if (!self::siteCreated($idSite = 2)) {
            self::createWebsite($this->dateTimeFirstDateWebsite2);
        }
    }

    protected function trackVisits()
    {
        /**
         * Track Visits normal date for the 2 websites
         */
        // WEBSITE 1
        $t = self::getTracker($this->idSite, $this->dateTimeFirstDateWebsite1, $defaultInit = true);
        $t->setUrl('http://example.org/category/Page1');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page2');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page3');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/Home');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/Contact');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/Contact/ThankYou');
        self::checkResponse($t->doTrackPageView('Hello'));

        // WEBSITE 2
        $t = self::getTracker($this->idSite2, $this->dateTimeFirstDateWebsite2, $defaultInit = true);
        $t->setIp('156.15.13.12');
        $t->setUrl('http://example.org/category/Page1');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page2');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page3');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/Home');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/Contact');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/Contact/ThankYou');
        self::checkResponse($t->doTrackPageView('Hello'));

        /**
         * Track visits in the past (before website creation date) for the 2 websites
         */
        // WEBSITE1
        $t = self::getTracker($this->idSite, $this->dateTimeDateInPastWebsite1, $defaultInit = true);
        $t->setIp('156.5.55.2');
        $t->setUrl('http://example.org/category/Page1');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page1');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page2');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Pagexx');
        self::checkResponse($t->doTrackPageView('Blabla'));

        // WEBSITE2
        $t = self::getTracker($this->idSite2, $this->dateTimeDateInPastWebsite2, $defaultInit = true);
        $t->setIp('156.52.3.22');
        $t->setUrl('http://example.org/category/Page1');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page1');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page2');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Pageyy');
        self::checkResponse($t->doTrackPageView('Blabla'));
        $t->setForceVisitDateTime(Date::factory($this->dateTimeDateInPastWebsite2)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.org/category/Pageyy');
        self::checkResponse($t->doTrackPageView('Blabla'));
    }
}
