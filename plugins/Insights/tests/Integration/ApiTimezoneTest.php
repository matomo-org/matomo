<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Insights\tests\Integration;

use Piwik\Date;
use Piwik\Plugins\Insights\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The archive resolves a relative date on the site's own day, so insights have to compare
 * against the day before that one, not the day before UTC's.
 *
 * @group Insights
 * @group Plugins
 */
class ApiTimezoneTest extends IntegrationTestCase
{
    /** UTC+12 with no DST transition near NOW. */
    private const SITE_TIMEZONE = 'Pacific/Auckland';

    /** 01:00 on 2026-09-09 in Auckland, still 2026-09-08 in UTC. */
    private const NOW = '2026-09-08 13:00:00';

    /** Auckland's yesterday. UTC's would be 2026-09-07. */
    private const AUCKLAND_YESTERDAY = '2026-09-08';

    /** The day before Auckland's yesterday. UTC's would be 2026-09-06. */
    private const AUCKLAND_DAY_BEFORE_YESTERDAY = '2026-09-07';

    private int $idSite;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->idSite = Fixture::createWebsite(
            '2026-09-01 00:00:00',
            0,
            'Site ahead of UTC',
            false,
            1,
            null,
            null,
            self::SITE_TIMEZONE
        );

        // On Auckland's yesterday, so the current report holds a row.
        $tracker = Fixture::getTracker($this->idSite, '2026-09-07 13:00:00');
        Fixture::checkResponse($tracker->doTrackPageView('/page'));

        Date::$now = (int) strtotime(self::NOW . ' UTC');
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function testInsightsAreComparedAgainstTheSitesPreviousDay(): void
    {
        $insights = API::getInstance()->getInsights($this->idSite, 'day', 'yesterday', 'Actions_getPageUrls');

        self::assertSame(self::AUCKLAND_DAY_BEFORE_YESTERDAY, $insights->getMetadata('lastDate'));
    }

    public function testMoversAndShakersAreComparedAgainstTheSitesPreviousDay(): void
    {
        $movers = API::getInstance()->getMoversAndShakers($this->idSite, 'day', 'yesterday', 'Actions_getPageUrls');

        self::assertSame(self::AUCKLAND_DAY_BEFORE_YESTERDAY, $movers->getMetadata('lastDate'));
    }

    /**
     * The row tooltips name `date` and `lastDate` side by side, so both have to be on the site's days.
     */
    public function testTheComparedDateIsReportedOnTheSitesDay(): void
    {
        $insights = API::getInstance()->getInsights($this->idSite, 'day', 'yesterday', 'Actions_getPageUrls');
        $movers = API::getInstance()->getMoversAndShakers($this->idSite, 'day', 'yesterday', 'Actions_getPageUrls');

        self::assertSame(self::AUCKLAND_YESTERDAY, $insights->getMetadata('date'));
        self::assertSame(self::AUCKLAND_YESTERDAY, $movers->getMetadata('date'));
    }

    public function testARelativeRangeIsReportedOnTheSitesDays(): void
    {
        $insights = API::getInstance()->getInsights($this->idSite, 'range', '2026-09-05,yesterday', 'Actions_getPageUrls');

        self::assertSame('2026-09-05,' . self::AUCKLAND_YESTERDAY, $insights->getMetadata('date'));
    }
}
