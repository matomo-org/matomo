<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PagePerformance\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\SegmentEditor\API as APISegmentEditor;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds two sites and tracks several visits all in the past.
 */
class VisitsWithPagePerformanceMetrics extends Fixture
{
    public $dateTime = '2010-03-12 01:22:33';
    public $idSite = 1;

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();

        APISegmentEditor::getInstance()->add(
            'my segment',
            'actions>=1',
            $idSite = 1,
            $autoArchive = false,
            $enabledAllUsers = true
        );
    }

    public function tearDown(): void
    {
        // empty
    }

    public function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }
    }

    protected function trackVisits()
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setUrl('http://example.org/category/Page1');
        $t->setPerformanceTimings(12, 150, 333, 1101, 369, 150);
        self::checkResponse($t->doTrackPageView('Page Title 1'));
        $t->setUrl('http://example.org/Contact');
        $t->setPerformanceTimings(0, 99, 298, 999, 412, 232);
        self::checkResponse($t->doTrackPageView('Contact'));
        $t->setUrl('http://example.org/Contact/ThankYou');
        $t->setPerformanceTimings(36, 77, 412, 1055, 333, 77);
        self::checkResponse($t->doTrackPageView('Contact'));

        $dateTime = Date::factory($this->dateTime)->subDay(1)->addHour(2.6)->getDatetime();

        $t = self::getTracker($this->idSite, $dateTime, $defaultInit = true);
        $t->setUrl('http://example.org/category/Page1');
        $t->setPerformanceTimings(12, 222, 211, 888, 299, 99);
        self::checkResponse($t->doTrackPageView('Page Title 1'));
        $t->setUrl('http://example.org/Contact');
        $t->setPerformanceTimings(6, 99, 298, 1300, 348, 199);
        self::checkResponse($t->doTrackPageView('Contact'));
        $t->setUrl('http://example.org/Contact/ThankYou');
        $t->setPerformanceTimings(36, 77, 412, 1140, 444, 120);
        self::checkResponse($t->doTrackPageView('Contact'));

        $dateTime = Date::factory($this->dateTime)->subDay(2)->addHour(6.5)->getDatetime();

        $t = self::getTracker($this->idSite, $dateTime, $defaultInit = true);
        $t->setUrl('http://example.org/category/Page1');
        $t->setPerformanceTimings(29, 355, 444, 1300, 512, 333);
        self::checkResponse($t->doTrackPageView('Page Title 1'));
        $t->setUrl('http://example.org/Contact');
        $t->setPerformanceTimings(66, 111, 278, 988, 355, 66);
        self::checkResponse($t->doTrackPageView('Contact'));
        $t->setUrl('http://example.org/Contact/ThankYou');
        $t->setPerformanceTimings(23, 211, 399, 998, 355, 222);
        self::checkResponse($t->doTrackPageView('Contact'));

        $dateTime = Date::factory($this->dateTime)->subDay(3)->addHour(4.1)->getDatetime();

        $t = self::getTracker($this->idSite, $dateTime, $defaultInit = true);
        $t->setUrl('http://example.org/category/Page1');
        $t->setPerformanceTimings(66, 277, 388, 1025, 436, 299);
        self::checkResponse($t->doTrackPageView('Page Title 1'));
        $t->setUrl('http://example.org/Contact');
        $t->setPerformanceTimings(98, 99, 199, 899, 236, 100);
        self::checkResponse($t->doTrackPageView('Contact'));
        $t->setUrl('http://example.org/Contact/ThankYou');
        $t->setPerformanceTimings(30, 123, 255, 1200, 233, 258);
        self::checkResponse($t->doTrackPageView('Contact'));

        $dateTime = Date::factory($this->dateTime)->subDay(4)->addHour(4.1)->getDatetime();

        $t = self::getTracker($this->idSite, $dateTime, $defaultInit = true);
        $t->setUrl('http://example.org/category/Page1');
        $t->setPerformanceTimings(13, 158, 136, 1235, 359, 248);
        self::checkResponse($t->doTrackPageView('Page Title 1'));
        $t->setUrl('http://example.org/Contact');
        $t->setPerformanceTimings(35, 132, 205, 1125, 236, 135);
        self::checkResponse($t->doTrackPageView('Contact'));
        $t->setUrl('http://example.org/Contact/ThankYou');
        $t->setPerformanceTimings(40, 269, 195, 963, 195, 215);
        self::checkResponse($t->doTrackPageView('Contact'));

        $dateTime = Date::factory($this->dateTime)->subDay(15)->addHour(2.6)->getDatetime();

        $t = self::getTracker($this->idSite, $dateTime, $defaultInit = true);
        $t->setUrl('http://example.org/category/Page1');
        $t->setPerformanceTimings(19, 222, 211, 888, 299, 99);
        self::checkResponse($t->doTrackPageView('Page Title 1'));
        $t->setUrl('http://example.org/Contact');
        $t->setPerformanceTimings(22, 99, 298, 1300, 348, 199);
        self::checkResponse($t->doTrackPageView('Contact'));
        $t->setUrl('http://example.org/Contact/ThankYou');
        $t->setPerformanceTimings(69, 77, 412, 1140, 444, 120);
        self::checkResponse($t->doTrackPageView('Contact'));
    }
}
