<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IntranetMeasurable\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\IntranetMeasurable\Type;
use Piwik\Tests\Framework\Fixture;

/**
 * Generates tracker testing data for our TrackingTest
 *
 * This Simple fixture adds one website and tracks one visit with couple pageviews and an ecommerce conversion
 */
class IntranetSitesWithVisits extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;
    public $idSiteNotIntranet = 2;

    public function setUp(): void
    {
        $this->setUpWebsites();
        $this->trackVisits($this->idSite);
        $this->trackVisits($this->idSiteNotIntranet);
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsites()
    {
        if (!self::siteCreated($this->idSite)) {
            Fixture::createWebsite(
                '2014-01-02 03:04:05',
                $ecommerce = 0,
                $siteName = false,
                $siteUrl = false,
                $siteSearch = 1,
                $searchKeywordParameters = null,
                $searchCategoryParameters = null,
                $timezone = null,
                Type::ID
            );
        }

        if (!self::siteCreated($this->idSiteNotIntranet)) {
            Fixture::createWebsite('2014-01-02 03:04:05');
        }
    }

    private function configureSameDevice(\MatomoTracker $t)
    {
        // to make purpose of test more clear we configure the device partially...
        $t->setIp('56.11.55.70');
        $t->setResolution(500, 200);
        $t->setPlugins(true, false, true, false, true);
        $t->setUserAgent('Mozilla/5.0 (Windows NT 6.0) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.112 Safari/535.1');

        return $t;
    }

    protected function trackVisits($idSite)
    {
        // two visits... intranet will trust visitorId and generate two visits
        // regular website will only generate one visit and prefer configId
        $t = self::getTracker($idSite, $this->dateTime, $defaultInit = true);
        $this->configureSameDevice($t);
        $t->randomVisitorId = '1234567890123456';

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.2)->getDatetime());
        $t->setUrl('http://example.com/sub/page');
        self::checkResponse($t->doTrackPageView('Second page view'));

        // different IP and different device but same visitor id... should still match this as unique visitor for intranet site
        // but not the other site
        $t = self::getTracker($idSite, $this->dateTime, $defaultInit = true);
        $this->configureSameDevice($t);
        $t->randomVisitorId = '1234567890123457';

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/sub/page');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.2)->getDatetime());
        $t->setUrl('http://example.com/?search=this is a site search query');
        self::checkResponse($t->doTrackPageView('Site search query'));
    }
}
