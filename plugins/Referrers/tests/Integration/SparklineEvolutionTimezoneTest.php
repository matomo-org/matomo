<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Referrers\tests\Integration;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

/**
 * The archive resolves a relative date on the site's own day, so the sparkline evolution has to
 * compare against the period before that day, not the one before UTC's.
 *
 * @group Referrers
 * @group Plugins
 */
class SparklineEvolutionTimezoneTest extends IntegrationTestCase
{
    /** 04:37 on 2020-12-25 for a site twelve hours ahead of UTC, still 2020-12-24 in UTC. */
    private const NOW_AHEAD = '2020-12-24 16:37:00';

    /** 15:00 on 2020-12-24 for a site twelve hours behind UTC, already 2020-12-25 in UTC. */
    private const NOW_BEHIND = '2020-12-25 03:00:00';

    /** @var array<string, mixed> */
    private array $originalGet;

    /** @var string[] */
    private array $requestedDates = [];

    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();

        $this->originalGet = $_GET;
    }

    public function tearDown(): void
    {
        $_GET = $this->originalGet;
        Date::$now = null;
        Fixture::resetTranslations();

        parent::tearDown();
    }

    /**
     * @dataProvider getRequestsAndTheirPreviousPeriod
     */
    public function testEvolutionIsComparedAgainstThePeriodBeforeTheSitesDay(
        string $timezone,
        string $now,
        string $period,
        string $date,
        string $expectedPreviousDate
    ): void {
        $this->setRequest($timezone, $now, $period, $date);

        Piwik::addAction('API.Request.dispatch', function ($finalParameters, $pluginName, $methodName) {
            if ($pluginName === 'Referrers' && $methodName === 'get') {
                $this->requestedDates[] = $finalParameters['date'];
            }
        });

        // The previous period is requested while the view is configured.
        ViewDataTableFactory::build(Sparklines::ID, 'Referrers.get', 'Referrers.get', true, false);

        self::assertSame([$expectedPreviousDate], $this->requestedDates);
    }

    public function getRequestsAndTheirPreviousPeriod(): array
    {
        return [
            // The site's today is 2020-12-25. UTC's previous day would be 2020-12-23.
            'ahead of UTC' => ['UTC+12', self::NOW_AHEAD, 'day', 'today', '2020-12-24'],
            // The site's today is 2020-12-24. UTC's previous day would be that same day.
            'behind UTC' => ['UTC-12', self::NOW_BEHIND, 'day', 'today', '2020-12-23'],
            // The archive ends the site's last7 on 2020-12-23, so the seven days before it end on
            // the 16th. Resolved on UTC's day, they would end on the 17th.
            'relative range' => ['UTC-12', self::NOW_AHEAD, 'range', 'last7', '2020-12-10,2020-12-16'],
        ];
    }

    /**
     * @dataProvider getTimezonesAndTheirDays
     */
    public function testEvolutionTooltipNamesTheSitesDays(
        string $timezone,
        string $now,
        string $expectedCurrentDay,
        string $expectedPreviousDay
    ): void {
        $this->setRequest($timezone, $now, 'day', 'today');

        $view = ViewDataTableFactory::build(Sparklines::ID, 'Referrers.get', 'Referrers.get', true, false);
        $evolution = ($view->config->compute_evolution)(['Referrers_visitorsFromDirectEntry' => 5]);

        self::assertStringContainsString("in $expectedCurrentDay compared to", $evolution['tooltip']);
        self::assertStringContainsString("in $expectedPreviousDay.", $evolution['tooltip']);
    }

    public function getTimezonesAndTheirDays(): array
    {
        return [
            // UTC's day would be 2020-12-24, compared to 2020-12-23.
            'ahead of UTC' => ['UTC+12', self::NOW_AHEAD, '2020-12-25', '2020-12-24'],
            // UTC's day would be 2020-12-25, compared to 2020-12-24.
            'behind UTC' => ['UTC-12', self::NOW_BEHIND, '2020-12-24', '2020-12-23'],
        ];
    }

    private function setRequest(string $timezone, string $now, string $period, string $date): void
    {
        $idSite = Fixture::createWebsite('2020-12-01 00:00:00', 0, 'Site in ' . $timezone, false, 1, null, null, $timezone);

        Date::$now = (int) strtotime($now);
        $_GET = ['idSite' => (string) $idSite, 'period' => $period, 'date' => $date, 'segment' => ''];
    }
}
