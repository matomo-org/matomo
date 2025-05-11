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
 * Fixture that adds one site and tracks couple of visits with changing visitorid
 */
class VisitsWithChangingVisitorId extends Fixture
{
    public $idSite = 1;
    public $date = '2025-01-04 10:00:00';

    protected $orderId = 99;

    public function setUp(): void
    {
        Fixture::createSuperUser();
        $this->setUpWebsites();
        $this->trackVisit(Date::factory($this->date));
        $this->trackVisit(Date::factory($this->date)->addDay(1));
        $this->trackVisit(Date::factory($this->date)->addDay(2));
        $this->trackVisitWithEcommerce(Date::factory($this->date));
        $this->trackVisitWithEcommerce(Date::factory($this->date)->addDay(1));
        $this->trackVisitWithEcommerce(Date::factory($this->date)->addDay(2));
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsites()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite('2025-01-01', 1);
        }
    }

    private function trackVisit($date)
    {
        $t = self::getTracker($this->idSite, $date->getDatetime(), true);
        $t->setIp('156.52.3.22');
        $t->setUserAgent('Mozilla/5.0 (Linux; U; Android 2.3.7; fr-fr; HTC Desire Build/GRI40; MildWild CM-8.0 JG Stable) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1');
        $t->setResolution(1080, 960);
        $t->setBrowserHasCookies(true);
        $t->setPlugins(false, true);

        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('index'));
        self::checkResponse($t->doTrackEvent('category', 'action'));

        $t->setForceVisitDateTime($date->addPeriod(1, 'minute')->getDatetime());

        // set a new random id - should continue current visit
        $t->setNewVisitorId();

        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('index'));
        self::checkResponse($t->doTrackEvent('category', 'action'));
    }

    private function trackVisitWithEcommerce($date)
    {
        $t = self::getTracker($this->idSite, $date->addHour(1)->getDatetime(), true);

        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('index'));

        $t->setForceVisitDateTime($date->addHour(1)->addPeriod(30, 'seconds')->getDatetime());

        $t->setUrl('http://example.org/product.htm');
        $t->setEcommerceView('custom sku', 'PRODUCT name', 'Electronics & Cameras', 500);
        self::checkResponse($t->doTrackPageView('product'));

        $t->setForceVisitDateTime($date->addHour(1)->addPeriod(45, 'seconds')->getDatetime());

        $t->addEcommerceItem('custom sku', 'PRODUCT name', 'Electronics & Cameras', 500, 1);
        $t->addEcommerceItem('another sku', 'PRODUCT name', 'Electronics & Cameras', 230, 2);
        self::checkResponse($t->doTrackEcommerceCartUpdate(960));

        $t->setForceVisitDateTime($date->addHour(1)->addPeriod(50, 'seconds')->getDatetime());

        self::checkResponse($t->doTrackEcommerceOrder(++$this->orderId, 960));

        $t->setForceVisitDateTime($date->addHour(1)->addPeriod(66, 'seconds')->getDatetime());

        // setting userid should update the visitor id with the next tracking request
        $t->setUserId('userid');

        $t->setUrl('http://example.org/product.htm');
        $t->setEcommerceView('custom sku', 'PRODUCT name', 'Electronics & Cameras', 500);
        self::checkResponse($t->doTrackPageView('product'));

        $t->setForceVisitDateTime($date->addHour(1)->addPeriod(76, 'seconds')->getDatetime());

        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('index'));

        $t->setForceVisitDateTime($date->addHour(1)->addPeriod(89, 'seconds')->getDatetime());

        $t->addEcommerceItem('custom sku', 'PRODUCT name', 'Electronics & Cameras', 500, 2);
        $t->addEcommerceItem('another sku', 'PRODUCT name', 'Electronics & Cameras', 230, 1);
        self::checkResponse($t->doTrackEcommerceCartUpdate(1230));

        $t->setForceVisitDateTime($date->addHour(1)->addPeriod(96, 'seconds')->getDatetime());

        self::checkResponse($t->doTrackEcommerceOrder(++$this->orderId, 1230));
    }
}
