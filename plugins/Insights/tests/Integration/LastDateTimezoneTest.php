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
 * against the period before that day, not the one before UTC's.
 *
 * @group Insights
 * @group Plugins
 */
class LastDateTimezoneTest extends IntegrationTestCase
{
    /** 04:37 on 2020-12-25 for the site, still 2020-12-24 in UTC. */
    private const NOW = '2020-12-24 16:37:00';

    private int $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2020-12-01 00:00:00', 0, 'Site ahead of UTC', false, 1, null, null, 'UTC+12');

        Date::$now = (int) strtotime(self::NOW);
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function testGetInsightsComparesAgainstTheSitesPreviousDay(): void
    {
        $insights = API::getInstance()->getInsights($this->idSite, 'day', 'today', 'Actions_getPageUrls');

        // The site's today is 2020-12-25. UTC's previous day would be 2020-12-23.
        self::assertSame('2020-12-24', $insights->getMetadata('lastDate'));
        // The row tooltips name this day as the one the current values are from.
        self::assertSame('2020-12-25', $insights->getMetadata('date'));
    }

    public function testGetMoversAndShakersComparesAgainstTheSitesPreviousDay(): void
    {
        $insights = API::getInstance()->getMoversAndShakers($this->idSite, 'day', 'today', 'Actions_getPageUrls');

        self::assertSame('2020-12-24', $insights->getMetadata('lastDate'));
        self::assertSame('2020-12-25', $insights->getMetadata('date'));
    }

    public function testGetInsightsNamesTheRangeTheArchiveBuildsForTheSitesDays(): void
    {
        $idSite = Fixture::createWebsite('2020-12-01 00:00:00', 0, 'Site behind UTC', false, 1, null, null, 'UTC-12');

        $insights = API::getInstance()->getInsights($idSite, 'range', 'last7', 'Actions_getPageUrls');

        // The site's last seven days end on 2020-12-23, and the seven before them end the day before.
        self::assertSame('2020-12-17,2020-12-23', $insights->getMetadata('date'));
        self::assertSame('2020-12-10,2020-12-16', $insights->getMetadata('lastDate'));
    }
}
