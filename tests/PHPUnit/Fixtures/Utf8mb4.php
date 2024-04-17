<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 *
 */
class Utf8mb4 extends Fixture
{
    public $idSite = 1;
    public $dateTime = '2010-01-04 00:11:42';

    public $trackInvalidRequests = true;

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
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1)->getDatetime());
        $t->setUrlReferrer('http://www.google.com/search?q=😡');
        $t->setUrl('http://example.org/foo/🙙.html');
        self::checkResponse($t->doTrackPageView('incredible 🚜'));

        $t->addEcommerceItem('sku 🛸', 'name 🛩', 'category 🛤', 95);
        self::checkResponse($t->doTrackEcommerceCartUpdate(100));
    }
}
