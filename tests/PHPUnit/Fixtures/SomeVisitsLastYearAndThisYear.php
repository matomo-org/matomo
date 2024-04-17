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
 * Fixture that adds one site and tracks one pageview for today.
 */
class SomeVisitsLastYearAndThisYear extends Fixture
{
    public $idSite = 1;
    private $year;

    public function setUp(): void
    {
        Fixture::createSuperUser();
        $this->setUpWebsites();
        $this->trackVisits();

        $this->year = Date::today()->toString('Y');
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsites()
    {
        if (!self::siteCreated($idSite = 1)) {
            $dt = Date::factory($this->year . '-01-01')->subYear(1);
            self::createWebsite($dt);
        }
    }

    private function trackVisits()
    {

        // This year, 5 visits
        for ($i = 0; $i < 5; $i++) {
            $dateTime = Date::factory($this->year . '-01-01')->toString();
            $t = self::getTracker($this->idSite, $dateTime, $defaultInit = true);

            $t->setUrl('http://example.org/index.htm');
            self::checkResponse($t->doTrackPageView('0'));
        }

        // Last Year
        for ($i = 0; $i < 5; $i++) {
            $dateTime = Date::factory($this->year . '-01-01')->subYear(1)->toString();
            $t = self::getTracker($this->idSite, $dateTime, $defaultInit = true);

            $t->setUrl('http://example.org/index.htm');
            self::checkResponse($t->doTrackPageView('0'));
        }
    }
}
