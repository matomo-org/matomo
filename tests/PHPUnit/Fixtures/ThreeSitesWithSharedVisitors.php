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
 * Adds three sites and tracks some visits w/ visitors that visit each site.
 */
class ThreeSitesWithSharedVisitors extends Fixture
{
    public $idSite = 1;
    public $idSite1 = 2;
    public $idSite2 = 3;
    public $dateTime = '2010-03-06 11:22:33';

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
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite($this->dateTime);
        }

        if (!self::siteCreated($this->idSite1)) {
            self::createWebsite($this->dateTime);
        }

        if (!self::siteCreated($this->idSite2)) {
            self::createWebsite($this->dateTime);
        }
    }

    private function trackVisits()
    {
        $dateTime = $this->dateTime;
        $idSite = $this->idSite;

        // two visits to site 1 & 3
        $visitor1 = self::getTracker($idSite, $dateTime, $defaultInit = true);
        $visitor1->setForceVisitDateTime(Date::factory($this->dateTime)->getDatetime());
        $visitor1->setUrl('http://helios.org/alpha');
        $visitor1->doTrackPageView("page title");

        $visitor1->setIdSite($this->idSite2);
        $visitor1->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1)->getDatetime());
        $visitor1->setUrl('http://taura.org/');
        $visitor1->doTrackPageView("page title");

        // one visit to site 1
        $visitor2 = self::getTracker($idSite, $dateTime, $defaultInit = true);
        $visitor2->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2)->getDatetime());
        $visitor2->setUrl('http://helios.org/beta');
        $visitor2->doTrackPageView("page title 2");

        // two visits to site 2 and 3
        $visitor3 = self::getTracker($this->idSite1, $dateTime, $defaultInit = true);
        $visitor3->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(3)->getDatetime());
        $visitor3->setUrl('http://virgon.org/');
        $visitor3->doTrackPageView("page title 2");

        $visitor3->setIdSite($this->idSite2);
        $visitor3->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(4)->getDatetime());
        $visitor3->setUrl('http://taura.org/');
        $visitor3->doTrackPageView("page title");
    }
}
