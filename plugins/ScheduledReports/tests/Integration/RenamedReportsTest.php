<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ScheduledReports\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\ReportRenderer;
use Piwik\Scheduler\Schedule\Schedule;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Covers the compatibility path for the goal overview report unique ids that carried `idGoal=0`
 * and are no longer advertised.
 *
 * @group Plugins
 * @group ScheduledReportsTest
 * @group RenamedReportsTest
 */
class RenamedReportsTest extends IntegrationTestCase
{
    private const RETIRED_GOALS_GET = 'Goals_get_idGoal--0';

    private const DATE = '2015-01-02';

    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = true;

        Manager::getInstance()->loadPlugins([
            'API',
            'ScheduledReports',
            'MobileMessaging',
            'VisitsSummary',
            'Goals',
            'Ecommerce',
            'SegmentEditor',
        ]);
        Manager::getInstance()->installLoadedPlugins();

        $this->idSite = Fixture::createWebsite('2015-01-01 00:00:00');

        GoalsApi::getInstance()->addGoal($this->idSite, 'Thank you page', 'url', 'thank-you', 'contains');

        APIScheduledReports::$cache = [];
    }

    public function testAStoredRetiredIdIsReturnedAsTheAllGoalsReport(): void
    {
        $idReport = $this->addReport(['VisitsSummary_get', 'Goals_get']);
        $this->overwriteStoredReports($idReport, ['VisitsSummary_get', self::RETIRED_GOALS_GET]);

        self::assertSame(['VisitsSummary_get', 'Goals_get'], $this->getStoredReportsFromApi($idReport));
    }

    public function testAStoredRowHoldingBothIdsIsReturnedWithASingleEntry(): void
    {
        $idReport = $this->addReport(['Goals_get']);
        $this->overwriteStoredReports($idReport, ['Goals_get', self::RETIRED_GOALS_GET]);

        self::assertSame(['Goals_get'], $this->getStoredReportsFromApi($idReport));
    }

    public function testACorruptStoredSelectionIsLeftAloneAsBefore(): void
    {
        $idReport = $this->addReport(['Goals_get']);
        Db::query(
            'UPDATE ' . Common::prefixTable('report') . ' SET reports = ? WHERE idreport = ?',
            ['"not a list"', $idReport]
        );
        APIScheduledReports::$cache = [];

        $reports = APIScheduledReports::getInstance()->getReports($this->idSite, false, $idReport);

        self::assertSame('not a list', reset($reports)['reports']);
    }

    public function testSavingAReportWithARetiredIdPersistsTheAllGoalsReport(): void
    {
        $idReport = $this->addReport(['VisitsSummary_get', self::RETIRED_GOALS_GET]);

        self::assertSame(
            ['VisitsSummary_get', 'Goals_get'],
            $this->getStoredReportsFromDb($idReport)
        );
    }

    public function testSavingAReportWithBothIdsPersistsASingleEntry(): void
    {
        $idReport = $this->addReport(['Goals_get', self::RETIRED_GOALS_GET]);

        self::assertSame(['Goals_get'], $this->getStoredReportsFromDb($idReport));
    }

    public function testSavingAReportWithAnUnknownIdIsStillRejected(): void
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage("Report Goals_get_idGoal--999 is unknown or not available for report type 'email'.");

        $this->addReport(['Goals_get_idGoal--999']);
    }

    public function testTheCustomOrderBranchRendersTheGoalsSectionOnce(): void
    {
        $idReport = $this->addReport(['Goals_get'], $enforceOrder = true);
        $this->overwriteStoredReports($idReport, ['Goals_get', self::RETIRED_GOALS_GET]);

        $generated = APIScheduledReports::getInstance()->generateReport(
            $idReport,
            self::DATE,
            false,
            APIScheduledReports::OUTPUT_RETURN
        );

        self::assertSame(1, substr_count($generated, 'id="Goals_get"'));
        self::assertStringNotContainsString('id="' . self::RETIRED_GOALS_GET . '"', $generated);
    }

    /**
     * @param list<string> $reports
     */
    private function addReport(array $reports, bool $enforceOrder = false): int
    {
        return APIScheduledReports::getInstance()->addReport(
            $this->idSite,
            'Test report',
            Schedule::PERIOD_DAY,
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT,
            $reports,
            [
                ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY,
                ScheduledReports::ENFORCE_ORDER_PARAMETER => $enforceOrder,
            ]
        );
    }

    /**
     * @param list<string> $reports
     */
    private function overwriteStoredReports(int $idReport, array $reports): void
    {
        Db::query(
            'UPDATE ' . Common::prefixTable('report') . ' SET reports = ? WHERE idreport = ?',
            [json_encode($reports), $idReport]
        );

        APIScheduledReports::$cache = [];
    }

    /**
     * @return list<string>
     */
    private function getStoredReportsFromApi(int $idReport): array
    {
        $reports = APIScheduledReports::getInstance()->getReports($this->idSite, false, $idReport);

        return reset($reports)['reports'];
    }

    /**
     * @return list<string>
     */
    private function getStoredReportsFromDb(int $idReport): array
    {
        $reports = Db::fetchOne(
            'SELECT reports FROM ' . Common::prefixTable('report') . ' WHERE idreport = ?',
            [$idReport]
        );

        return json_decode($reports, true);
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
