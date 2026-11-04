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
    /** 11:00 on 2026-11-02 in Los Angeles, the day after its DST end, and 08:00 on 2026-11-03 in Auckland. */
    private const NOW = '2026-11-02 19:00:00';

    private const VISITOR_ID = '1234567890abcdef';

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    /**
     * @dataProvider getVisitsBehindAndAheadOfUtc
     */
    public function testDaysAgoCountsTheSitesCalendarDays(string $timezone, string $visitUtc, int $expectedDaysAgo): void
    {
        Fixture::createSuperUser();
        $idSite = Fixture::createWebsite('2026-10-01 00:00:00', 0, 'Site', false, 1, null, null, $timezone);

        $tracker = Fixture::getTracker($idSite, $visitUtc);
        $tracker->setVisitorId(self::VISITOR_ID);
        Fixture::checkResponse($tracker->doTrackPageView('/page'));

        Date::$now = (int) strtotime(self::NOW . ' UTC');

        $profile = API::getInstance()->getVisitorProfile($idSite, self::VISITOR_ID);

        self::assertSame($expectedDaysAgo, $profile['firstVisit']['daysAgo']);
    }

    public static function getVisitsBehindAndAheadOfUtc(): iterable
    {
        // Los Angeles is UTC-7 until 2026-11-01 02:00 and UTC-8 after, Auckland is UTC+13.
        yield 'Los Angeles, Oct 31 18:00' => ['America/Los_Angeles', '2026-11-01 01:00:00', 2];
        yield 'Los Angeles, Oct 31 09:00' => ['America/Los_Angeles', '2026-10-31 16:00:00', 2];
        yield 'Los Angeles, Nov 1 18:00' => ['America/Los_Angeles', '2026-11-02 02:00:00', 1];
        yield 'Auckland, Nov 1 15:00' => ['Pacific/Auckland', '2026-11-01 02:00:00', 2];
        yield 'Auckland, Nov 2 09:00' => ['Pacific/Auckland', '2026-11-01 20:00:00', 1];
    }
}
