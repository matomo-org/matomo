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
class OneVisit extends Fixture
{
    public $idSite = 1;

    public function setUp(): void
    {
        Fixture::createSuperUser();
        $this->setUpWebsites();
        $this->trackVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsites()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite('2021-01-01');
        }
    }

    private function trackVisits()
    {
        $dateTime = Date::today()->toString();
        $t = self::getTracker($this->idSite, $dateTime, $defaultInit = true);

        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('0'));
    }
}
