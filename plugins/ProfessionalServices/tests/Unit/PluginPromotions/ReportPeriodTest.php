<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit\PluginPromotions;

use PHPUnit\Framework\TestCase;
use Piwik\Date;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;
use Piwik\Site;

/**
 * @group ProfessionalServices
 * @group PluginPromotions
 */
class ReportPeriodTest extends TestCase
{
    private const IDSITE = 1;

    protected function setUp(): void
    {
        parent::setUp();

        Site::setSiteFromArray(self::IDSITE, ['idsite' => self::IDSITE, 'timezone' => 'UTC']);
    }

    protected function tearDown(): void
    {
        Date::$now = null;
        Site::clearCache();

        parent::tearDown();
    }

    /**
     * Whatever day of the week the dashboard is opened on, the promotion triggers must
     * look at the same completed Monday to Sunday week, and never at a rolling window or
     * at the partially archived current week.
     *
     * @dataProvider getDaysOfTheCurrentWeek
     */
    public function testAlwaysReturnsTheLastCompletedMondayToSundayWeek(string $today): void
    {
        Date::$now = strtotime($today . ' 12:00:00 UTC');

        $reportPeriod = new ReportPeriod();

        $this->assertSame('2026-08-17', $reportPeriod->getStartDate(self::IDSITE), 'start of week for ' . $today);
        $this->assertSame('2026-08-23', $reportPeriod->getEndDate(self::IDSITE), 'end of week for ' . $today);
    }

    /**
     * @return array<string, array{string}>
     */
    public function getDaysOfTheCurrentWeek(): array
    {
        return [
            'Monday' => ['2026-08-24'],
            'Tuesday' => ['2026-08-25'],
            'Wednesday' => ['2026-08-26'],
            'Thursday' => ['2026-08-27'],
            'Friday' => ['2026-08-28'],
            'Saturday' => ['2026-08-29'],
            'Sunday' => ['2026-08-30'],
        ];
    }

    public function testUsesTheWeekPeriodAndNeverARange(): void
    {
        Date::$now = strtotime('2026-08-27 12:00:00 UTC');

        $period = (new ReportPeriod())->forSite(self::IDSITE);

        // A range period would force archiving on a browser request, which a dashboard
        // must never do.
        $this->assertSame('week', $period->getLabel());
    }
}
