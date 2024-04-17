<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Provides two websites on each border of the allowed timezone range
 */
class TwoSitesWithBorderTimezones extends Fixture
{
    public $dateTime = '2010-02-01 11:22:33';

    public $idSiteUtcMinus = 1;
    public $idSiteUtcPlus = 2;

    // @see \Piwik\Plugins\SitesManager\API::getTimezonesListUTCOffsets()
    private $timezoneUtcMinus = 'UTC-12';
    private $timezoneUtcPlus = 'UTC+14';

    public function setUp(): void
    {
        if (!self::siteCreated($this->idSiteUtcMinus)) {
            self::createWebsite(
                $this->dateTime,
                0,
                false,
                false,
                1,
                null,
                null,
                $this->timezoneUtcMinus
            );
        }

        if (!self::siteCreated($this->idSiteUtcPlus)) {
            self::createWebsite(
                $this->dateTime,
                0,
                false,
                false,
                1,
                null,
                null,
                $this->timezoneUtcPlus
            );
        }

        $this->trackVisits($this->idSiteUtcMinus, $this->timezoneUtcMinus);
        $this->trackVisits($this->idSiteUtcPlus, $this->timezoneUtcPlus);
    }

    public function tearDown(): void
    {
        // empty
    }

    private function trackVisits(int $idSite, string $timezone): void
    {
        $t = self::getTracker($idSite, $this->dateTime);
        $t->setTokenAuth(self::getTokenAuth());
        $t->enableBulkTracking();

        // create 3 visits at 18:00, 00:00, and 06:00 (local timezone)
        for ($visitTimeOffset = -1; $visitTimeOffset <= 1; $visitTimeOffset++) {
            $visitLocalDate = Date::factory($this->dateTime)->getStartOfDay()->addHour($visitTimeOffset * 6);
            $visitDate = $visitLocalDate->setTimezone($timezone);

            $t->setForceNewVisit();
            $t->setForceVisitDateTime($visitDate->getDatetime());
            $t->setLocalTime($visitLocalDate->toString('H:i:s'));
            $t->setUrl('http://example.org/time/offset/' . $visitTimeOffset);

            self::assertTrue($t->doTrackPageView('incredible title ' . $visitTimeOffset));
        }

        self::checkBulkTrackingResponse($t->doBulkTrack());
    }
}
