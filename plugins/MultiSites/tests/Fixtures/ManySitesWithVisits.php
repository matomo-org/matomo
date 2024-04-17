<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MultiSites\tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Generates tracker testing data for our ControllerTest
 *
 * This Simple fixture adds one website and tracks one visit with couple pageviews and an ecommerce conversion
 */
class ManySitesWithVisits extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;

    public function setUp(): void
    {
        $this->setUpWebsite();
        $this->trackFirstVisit($this->idSite);
        $this->trackSecondVisit($this->idSite);
        $this->trackFirstVisit($siteId = 2);
        $this->trackSecondVisit($siteId = 3);
        $this->trackSecondVisit($siteId = 3);
        $this->trackSecondVisit($siteId = 4);
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsite()
    {
        for ($i = 1; $i <= 15; $i++) {
            if (!self::siteCreated($i)) {
                $idSite = self::createWebsite($this->dateTime, $ecommerce = 1, 'Site ' . $i);
                $this->assertSame($i, $idSite);
            }
        }
    }

    protected function trackFirstVisit($idSite)
    {
        $t = self::getTracker($idSite, $this->dateTime, $defaultInit = true);

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.2)->getDatetime());
        $t->setUrl('http://example.com/sub/page');
        self::checkResponse($t->doTrackPageView('Second page view'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.25)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU_ID', $name = 'Test item!', $category = 'Test & Category', $price = 777, $quantity = 33);
        self::checkResponse($t->doTrackEcommerceOrder('TestingOrder', $grandTotal = 33 * 77));
    }

    protected function trackSecondVisit($idSite)
    {
        $t = self::getTracker($idSite, $this->dateTime, $defaultInit = true);
        $t->setIp('56.11.55.73');

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.com/sub/page');
        self::checkResponse($t->doTrackPageView('Viewing homepage'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.2)->getDatetime());
        $t->setUrl('http://example.com/?search=this is a site search query');
        self::checkResponse($t->doTrackPageView('Site search query'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.3)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU_ID2', $name = 'A durable item', $category = 'Best seller', $price = 321);
        self::checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 33 * 77));
    }
}
