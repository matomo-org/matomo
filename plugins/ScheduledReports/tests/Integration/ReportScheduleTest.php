<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports\tests\Integration;

use Piwik\Plugins\ScheduledReports\ReportSchedule;
use Piwik\Scheduler\Schedule\Schedule;
use Piwik\Site;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Plugins
 * @group ScheduledReports
 * @group ReportScheduleTest
 */
class ReportScheduleTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Site::setSites([
            1 => ['timezone' => 'UTC'],
            2 => ['timezone' => 'Asia/Shanghai'],
            3 => ['timezone' => 'UTC-6.5'],
            4 => ['timezone' => 'Asia/Kolkata'],
            5 => ['timezone' => 'America/Chicago'],
        ]);
    }

    public function tearDown(): void
    {
        Site::clearCache();

        parent::tearDown();
    }

    /**
     * @dataProvider getRescheduledTimeTestCases
     */
    public function testGetRescheduledTimeKeepsTheSiteLocalDay(string $period, int $idSite, int $utcHour, string $now, string $expected): void
    {
        $schedule = $this->getMockBuilder(ReportSchedule::class)
            ->setConstructorArgs([$period, $idSite])
            ->onlyMethods(['getTime'])
            ->getMock();
        $schedule->method('getTime')->willReturn(strtotime($now));
        $schedule->setHour($utcHour);

        $this->assertSame($expected, gmdate('Y-m-d H:i:s', $schedule->getRescheduledTime()));
    }

    public function getRescheduledTimeTestCases(): iterable
    {
        // 2026-10-07 is a Wednesday
        yield 'weekly, UTC site' => [Schedule::PERIOD_WEEK, 1, 0, '2026-10-07 12:00:00 UTC', '2026-10-12 00:00:00'];
        yield 'weekly, local Monday 05:00 is UTC Sunday' => [Schedule::PERIOD_WEEK, 2, 21, '2026-10-07 12:00:00 UTC', '2026-10-11 21:00:00'];
        yield 'weekly, positive offset without day change' => [Schedule::PERIOD_WEEK, 2, 8, '2026-10-07 12:00:00 UTC', '2026-10-12 08:00:00'];
        yield 'weekly, local Monday 19:30 is UTC Tuesday' => [Schedule::PERIOD_WEEK, 3, 2, '2026-10-07 12:00:00 UTC', '2026-10-13 02:00:00'];
        yield 'weekly, half hour offset crossing midnight' => [Schedule::PERIOD_WEEK, 4, 23, '2026-10-07 12:00:00 UTC', '2026-10-11 23:00:00'];
        yield 'weekly, negative offset in daylight saving time' => [Schedule::PERIOD_WEEK, 5, 1, '2026-10-07 12:00:00 UTC', '2026-10-13 01:00:00'];
        yield 'weekly, negative offset in standard time' => [Schedule::PERIOD_WEEK, 5, 1, '2026-12-02 12:00:00 UTC', '2026-12-08 01:00:00'];
        yield 'weekly, rescheduled right after sending' => [Schedule::PERIOD_WEEK, 2, 21, '2026-10-11 21:00:00 UTC', '2026-10-18 21:00:00'];

        yield 'monthly, UTC site' => [Schedule::PERIOD_MONTH, 1, 0, '2026-10-07 12:00:00 UTC', '2026-11-01 00:00:00'];
        yield 'monthly, local first day 05:00 is the last UTC day' => [Schedule::PERIOD_MONTH, 2, 21, '2026-10-07 12:00:00 UTC', '2026-10-31 21:00:00'];
        yield 'monthly, positive offset without day change' => [Schedule::PERIOD_MONTH, 2, 8, '2026-10-07 12:00:00 UTC', '2026-11-01 08:00:00'];
        yield 'monthly, local first day 19:30 is the second UTC day' => [Schedule::PERIOD_MONTH, 3, 2, '2026-10-07 12:00:00 UTC', '2026-11-02 02:00:00'];
        yield 'monthly, half hour offset crossing midnight' => [Schedule::PERIOD_MONTH, 4, 23, '2026-10-07 12:00:00 UTC', '2026-10-31 23:00:00'];
        yield 'monthly, rescheduled right after sending' => [Schedule::PERIOD_MONTH, 2, 21, '2026-10-31 21:00:00 UTC', '2026-11-30 21:00:00'];
        yield 'monthly, late run in the next UTC month does not skip a month' => [Schedule::PERIOD_MONTH, 2, 21, '2026-11-01 00:30:00 UTC', '2026-11-30 21:00:00'];
    }
}
