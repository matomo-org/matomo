<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports\tests\Integration;

use PHPMailer\PHPMailer\PHPMailer;
use Piwik\Date;
use Piwik\Period;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\ReportRenderer;
use Piwik\Scheduler\Schedule\Schedule;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

require_once PIWIK_INCLUDE_PATH . '/plugins/ScheduledReports/ScheduledReports.php';

/**
 * Regression test for DEV-20165: a daily-schedule report with a non-daily data period
 * was suppressed by the `ScheduledReports.sendReport` listener's duplicate-send safeguard,
 * which keyed on the (constant for 7+ days) data-period range rather than the schedule
 * cadence. As a result a Daily/Weekly report only emailed once a week, not once a day.
 *
 * @group Plugins
 * @group ScheduledReports
 * @group SchedulingDispatchTest
 */
class SchedulingDispatchTest extends IntegrationTestCase
{
    private $idSite = 1;

    /** @var PHPMailer[] */
    public $mailsSent = [];

    /** @var string[] */
    public $sentDataPeriods = [];

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = true;

        \Piwik\Plugin\Manager::getInstance()->loadPlugins([
            'API',
            'UserCountry',
            'ScheduledReports',
            'VisitsSummary',
            'Referrers',
            'Dashboard',
            'Live',
            'SegmentEditor',
        ]);
        \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();

        APISitesManager::getInstance()->addSite('Test', ['http://piwik.net']);
        FakeAccess::setIdSitesView([$this->idSite]);

        APIScheduledReports::$cache = [];

        $this->mailsSent = [];
        $this->sentDataPeriods = [];
    }

    public function tearDown(): void
    {
        Date::$now = null;
        APIScheduledReports::$cache = [];

        parent::tearDown();
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
            'observers.global' => \Piwik\DI::add([
                ['Test.Mail.send', \Piwik\DI::value(function (PHPMailer $mail) {
                    $this->mailsSent[] = clone $mail;
                    $mail->preSend();
                })],
                ['ScheduledReports.sendReport', \Piwik\DI::value(function ($reportType, $report, $contents, $filename, $prettyDate, $reportSubject, $reportTitle, $additionalFiles, Period $period) {
                    $this->sentDataPeriods[] = $period->getRangeString();
                })],
            ]),
        ];
    }

    /**
     * When a daily-schedule report has a weekly data period, the duplicate-send safeguard
     * in ScheduledReports::sendReport must compare against the schedule's cadence (the day
     * of dispatch) — not the data window's date range. Otherwise the weekly data range
     * stays identical for 6–7 consecutive daily dispatches and only the first one
     * produces an email, so users on a Daily/Weekly configuration receive one email per
     * week instead of one per day.
     *
     * Setup: three dispatches on three consecutive same-week days. All three must produce
     * an email even though the underlying weekly data range is identical on every dispatch.
     */
    public function testDailyScheduleWithWeeklyDataSendsEmailEachDay(): void
    {
        $idReport = $this->createReport(Schedule::PERIOD_DAY, $periodParam = 'week');

        // Wed/Thu/Fri all fall in the same Mon–Sun week (Jun 2–8 2025), so the data-period
        // range Period::build('week', yesterday)->getRangeString() is identical on all
        // three days — the exact condition that previously triggered the false suppression.
        $this->dispatchOnConsecutiveDays($idReport, '2025-06-04 12:00:00 UTC', 3);

        $this->assertCount(3, $this->mailsSent);
    }

    /**
     * Same shape as the Daily/Weekly case but with a monthly data period — the data range
     * stays constant for an entire calendar month, which is an even more dramatic case of
     * the same bug. Covered separately so a future regression that special-cases 'week'
     * but not 'month' (or vice-versa) is caught.
     */
    public function testDailyScheduleWithMonthlyDataSendsEmailEachDay(): void
    {
        $idReport = $this->createReport(Schedule::PERIOD_DAY, $periodParam = 'month');

        $this->dispatchOnConsecutiveDays($idReport, '2025-06-04 12:00:00 UTC', 3);

        $this->assertCount(3, $this->mailsSent);
    }

    /**
     * Baseline sanity check: a Daily/Daily report — where both the schedule and the data
     * period are 'day' — should still send an email on every dispatch. This case worked
     * before the safeguard fix as well, since the data range changed each day, but is
     * worth pinning down to catch any future regression that breaks the common case.
     */
    public function testDailyScheduleWithDailyDataSendsEmailEachDay(): void
    {
        $idReport = $this->createReport(Schedule::PERIOD_DAY, $periodParam = 'day');

        $this->dispatchOnConsecutiveDays($idReport, '2025-06-04 12:00:00 UTC', 3);

        $this->assertCount(3, $this->mailsSent);
    }

    /**
     * The duplicate-send safeguard must still work for its intended purpose: two
     * dispatches of the same daily report on the same calendar day produce only one
     * email. This guards against the safeguard becoming a no-op after the fix.
     */
    public function testSafeguardStillSuppressesSameDayDuplicateDispatchForDailyReport(): void
    {
        $idReport = $this->createReport(Schedule::PERIOD_DAY, $periodParam = 'day');

        Date::$now = strtotime('2025-06-04 12:00:00 UTC');
        APIScheduledReports::getInstance()->sendReport($idReport);
        APIScheduledReports::getInstance()->sendReport($idReport);

        $this->assertCount(1, $this->mailsSent);
    }

    /**
     * A weekly-schedule report should fire at most once per week. Dispatching it twice
     * within the same Mon–Sun window must result in only the first dispatch sending an
     * email — verifies the safeguard still functions for non-daily schedules.
     */
    public function testWeeklyScheduleSuppressesSameWeekDuplicateDispatch(): void
    {
        $idReport = $this->createReport(Schedule::PERIOD_WEEK, $periodParam = 'week');

        // Two dispatches in the same Mon-Sun week.
        Date::$now = strtotime('2025-06-04 12:00:00 UTC');
        APIScheduledReports::getInstance()->sendReport($idReport);
        Date::$now = strtotime('2025-06-05 12:00:00 UTC');
        APIScheduledReports::getInstance()->sendReport($idReport);

        $this->assertCount(1, $this->mailsSent);
    }

    /**
     * Local Monday 05:00 in Asia/Shanghai is Sunday 21:00 UTC. The report sent then must cover the
     * local week that just ended, not the week before it.
     */
    public function testWeeklyReportSentBeforeUtcMondayCoversThePreviousLocalWeek(): void
    {
        $idSite = $this->addShanghaiSite();
        $idReport = $this->createReport(Schedule::PERIOD_WEEK, $periodParam = 'week', $hour = 21, $idSite);

        Date::$now = strtotime('2026-10-04 21:00:00 UTC');
        APIScheduledReports::getInstance()->sendReport($idReport);

        $this->assertCount(1, $this->mailsSent);
        $this->assertSame(['2026-09-28,2026-10-04'], $this->sentDataPeriods);
    }

    /**
     * The first day of the month 05:00 in Asia/Shanghai is the last day of the previous month 21:00 UTC.
     */
    public function testMonthlyReportSentBeforeUtcFirstDayCoversThePreviousLocalMonth(): void
    {
        $idSite = $this->addShanghaiSite();
        $idReport = $this->createReport(Schedule::PERIOD_MONTH, $periodParam = 'month', $hour = 21, $idSite);

        Date::$now = strtotime('2026-09-30 21:00:00 UTC');
        APIScheduledReports::getInstance()->sendReport($idReport);

        $this->assertCount(1, $this->mailsSent);
        $this->assertSame(['2026-09-01,2026-09-30'], $this->sentDataPeriods);
    }

    /**
     * A report still scheduled on the previous UTC Monday sends on local Tuesday once more, the next
     * send on local Monday falls into the same UTC week but must not be suppressed as a duplicate.
     */
    public function testWeeklyReportIsNotSuppressedWhenTwoLocalWeeksShareAUtcWeek(): void
    {
        $idSite = $this->addShanghaiSite();
        $idReport = $this->createReport(Schedule::PERIOD_WEEK, $periodParam = 'week', $hour = 21, $idSite);

        Date::$now = strtotime('2026-10-05 21:00:00 UTC');
        APIScheduledReports::getInstance()->sendReport($idReport);
        Date::$now = strtotime('2026-10-11 21:00:00 UTC');
        APIScheduledReports::getInstance()->sendReport($idReport);

        $this->assertCount(2, $this->mailsSent);
        $this->assertSame(['2026-09-28,2026-10-04', '2026-10-05,2026-10-11'], $this->sentDataPeriods);
    }

    private function addShanghaiSite(): int
    {
        FakeAccess::$superUser = true;
        $idSite = APISitesManager::getInstance()->addSite('Shanghai', ['http://example.org'], timezone: 'Asia/Shanghai');
        FakeAccess::setIdSitesView([$this->idSite, $idSite]);

        return $idSite;
    }

    private function dispatchOnConsecutiveDays(int $idReport, string $startUtc, int $dayCount): void
    {
        $startTs = strtotime($startUtc);
        for ($dayOffset = 0; $dayOffset < $dayCount; $dayOffset++) {
            Date::$now = $startTs + ($dayOffset * 86400);
            APIScheduledReports::getInstance()->sendReport($idReport);
        }
    }

    private function createReport(string $schedulePeriod, string $periodParam, int $hour = 0, ?int $idSite = null): int
    {
        APIScheduledReports::$cache = [];

        return APIScheduledReports::getInstance()->addReport(
            $idSite ?? $this->idSite,
            'test report',
            $schedulePeriod,
            $hour,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT,
            ['VisitsSummary_get'],
            [
                ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY,
                'emailMe'          => false,
                'additionalEmails' => ['recipient@example.com'],
            ],
            false,
            'prev',
            null,
            $periodParam
        );
    }
}
