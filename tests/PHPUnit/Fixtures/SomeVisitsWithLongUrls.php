<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds one site and tracks 7 visits w/ some long-ish urls (as page urls and
 * referrer urls).
 */
class SomeVisitsWithLongUrls extends Fixture
{
    public $dateTime = '2010-03-06 01:22:33';
    public $idSite = 1;

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown(): void
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
        // tests run in UTC, the Tracker in UTC
        $dateTime = $this->dateTime;
        $idSite = $this->idSite;

        // Visit 1: keyword and few URLs
        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);
        $t->setUrlReferrer('http://bing.com/search?q=Hello world');

        // Generate a few page views that will be truncated
        $t->setUrl('http://example.org/category/Page1');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page2');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page3');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page3');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page4');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page4');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page4');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category.htm');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/page.htm');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/page.htm');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/page.htm');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/contact.htm');
        self::checkResponse($t->doTrackPageView('Hello'));

        // VISIT 2 = Another keyword
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(1)->getDatetime());
        $t->setUrlReferrer('http://www.google.com.vn/url?q=Salut');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Visit 3 = Another keyword
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(2)->getDatetime());
        $t->setUrlReferrer('http://www.google.com.vn/url?q=Kia Ora');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Visit 4 = Kia Ora again
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(3)->getDatetime());
        $t->setUrlReferrer('http://www.google.com.vn/url?q=Kia Ora');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Visit 5 = Another search engine
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(4)->getDatetime());
        $t->setUrlReferrer('http://nz.search.yahoo.com/search?p=Kia Ora');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Visit 6 = Another search engine
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(5)->getDatetime());
        $t->setUrlReferrer('http://images.search.yahoo.com/search/images;_ylt=A2KcWcNKJzF?p=Kia%20Ora%20');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Visit 7 = Another search engine
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(6)->getDatetime());
        $t->setUrlReferrer('http://nz.bing.com/images/search?q=+++Kia+ora+++');
        self::checkResponse($t->doTrackPageView('incredible title!'));
    }
}
