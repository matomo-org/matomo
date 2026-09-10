<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Live\tests\Integration;

use Piwik\Date;
use Piwik\Plugins\Live\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The visitor profile names how many of the site's calendar days ago a visit was, so a visit
 * early in the day two days back is two days ago however many hours have passed since.
 *
 * @group Live
 * @group Plugins
 */
class VisitorProfileDaysAgoTest extends IntegrationTestCase
{
    /** UTC+12 with no DST transition near NOW. */
    private const SITE_TIMEZONE = 'Pacific/Auckland';

    /** 08:00 on 2026-09-09 in Auckland, still 2026-09-08 in UTC. */
    private const NOW = '2026-09-08 20:00:00';

    private const VISITOR_ID = '1234567890abcdef';

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

        // 10:00 on 2026-09-07 in Auckland, 46 hours before NOW, and 07:00 on Auckland's today.
        foreach (['2026-09-06 22:00:00', '2026-09-08 19:00:00'] as $when) {
            $tracker = Fixture::getTracker($this->idSite, $when);
            $tracker->setVisitorId(self::VISITOR_ID);
            Fixture::checkResponse($tracker->doTrackPageView('/page'));
        }

        Date::$now = (int) strtotime(self::NOW . ' UTC');
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function testDaysAgoCountsTheSitesCalendarDays(): void
    {
        $profile = API::getInstance()->getVisitorProfile($this->idSite, self::VISITOR_ID);

        self::assertSame(2, $profile['firstVisit']['daysAgo']);
        self::assertSame(0, $profile['lastVisit']['daysAgo']);
    }
}
