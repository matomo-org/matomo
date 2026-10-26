<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\SegmentEditor\tests\Integration;

use Piwik\Date;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The archive resolves a relative date on the site's own day, so the visit evolution has to
 * compare against the day before that one, not the day before UTC's.
 *
 * @group SegmentEditor
 * @group Plugins
 */
class SegmentDataTimezoneTest extends IntegrationTestCase
{
    /** UTC+12 with no DST transition near NOW. */
    private const SITE_TIMEZONE = 'Pacific/Auckland';

    /** 01:00 on 2026-09-09 in Auckland, still 2026-09-08 in UTC. */
    private const NOW = '2026-09-08 13:00:00';

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

        // Two visits on each of Auckland's 2026-09-07 and 2026-09-08 and none on 2026-09-06,
        // so the evolution is stable only against Auckland's previous day.
        foreach (['2026-09-06 13:00:00', '2026-09-06 14:00:00', '2026-09-07 13:00:00', '2026-09-07 14:00:00'] as $when) {
            $tracker = Fixture::getTracker($this->idSite, $when);
            Fixture::checkResponse($tracker->doTrackPageView('/page'));
        }

        Date::$now = (int) strtotime(self::NOW . ' UTC');
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function testEvolutionIsComparedAgainstTheSitesPreviousDay(): void
    {
        $data = API::getInstance()->getSegmentData($this->idSite, 'day', 'yesterday', '');

        self::assertSame(2, $data['nb_visits']);
        self::assertSame('stable', $data['evolution_visits_direction']);
    }
}
