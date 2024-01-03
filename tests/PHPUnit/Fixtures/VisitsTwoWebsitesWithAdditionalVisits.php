<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Tests\Framework\Fixture;

/**
 * Adds two sites and tracks several visits with possibility to add new visits to the same days
 */
class VisitsTwoWebsitesWithAdditionalVisits extends Fixture
{
    public $dateTimeFirstDateWebsite1 = '2010-03-06 01:22:33';
    public $dateTimeFirstDateWebsite2 = '2010-01-06 02:22:33';

    public $idSite1 = 1;
    public $idSite2 = 2;

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown(): void
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
        $t = self::getTracker($this->idSite1, $this->dateTimeFirstDateWebsite1, $defaultInit = true);
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
        $t->setIp('156.52.3.22');
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
    }



    public function trackMoreVisits($idSite)
    {
        /**
         * Track Visits normal date for the 2 websites
         */

        switch ($idSite) {
            case $this->idSite1:
                $t = self::getTracker($this->idSite1, $this->dateTimeFirstDateWebsite1, $defaultInit = true);
                $t->setIp('156.15.13.12');
                $t->setUrl('http://example.org/category/Page1');
                self::checkResponse($t->doTrackPageView('Hello'));
                $t->setUrl('http://example.org/category/Page2');
                self::checkResponse($t->doTrackPageView('Hello'));
                $t->setUrl('http://example.org/category/NewPage');
                self::checkResponse($t->doTrackPageView('New Page'));
                $t->setUrl('http://example.org/Home');
                self::checkResponse($t->doTrackPageView('Hello'));
                $t->setUrl('http://example.org/Contact');
                self::checkResponse($t->doTrackPageView('Hello'));
                $t->setUrl('http://example.org/Contact/ThankYou');
                self::checkResponse($t->doTrackPageView('Hello'));
                break;

            case $this->idSite2:
                $t = self::getTracker($this->idSite2, $this->dateTimeFirstDateWebsite2, $defaultInit = true);
                $t->setIp('156.5.55.2');
                $t->setUrl('http://example.org/category/Page1');
                self::checkResponse($t->doTrackPageView('Hello'));
                $t->setUrl('http://example.org/category/Page2');
                self::checkResponse($t->doTrackPageView('Hello'));
                $t->setUrl('http://example.org/category/NewPage');
                self::checkResponse($t->doTrackPageView('New Page'));
                $t->setUrl('http://example.org/Home');
                self::checkResponse($t->doTrackPageView('Hello'));
                $t->setUrl('http://example.org/Contact');
                self::checkResponse($t->doTrackPageView('Hello'));
                $t->setUrl('http://example.org/Contact/ThankYou');
                self::checkResponse($t->doTrackPageView('Hello'));
                break;
        }
    }
}
