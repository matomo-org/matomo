<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Updates;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Plugin\Manager;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\ReportRenderer;
use Piwik\Scheduler\Schedule\Schedule;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater;
use Piwik\Updates\Updates_6_0_0_b8;

require_once PIWIK_INCLUDE_PATH . '/core/Updates/6.0.0-b8.php';

/**
 * @group Core
 * @group Updates600b8Test
 */
class Updates600b8Test extends IntegrationTestCase
{
    /**
     * @var int
     */
    private $idSite;

    /**
     * @var int
     */
    private $idSiteWithEcommerce;

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

        $this->idSite              = Fixture::createWebsite('2015-01-01 00:00:00');
        $this->idSiteWithEcommerce = Fixture::createWebsite('2015-01-01 00:00:00', $ecommerce = 1);

        APIScheduledReports::$cache = [];
    }

    public function testUpdateMovesGoalsOverviewToTheEcommerceOrderReportIfTheSiteHasEcommerce(): void
    {
        $idReport = $this->storeReport($this->idSiteWithEcommerce, ['VisitsSummary_get', 'Goals_get_idGoal--0']);

        $this->runUpdate();

        self::assertSame(['VisitsSummary_get', 'Goals_get_idGoal--ecommerceOrder'], $this->getStoredReports($idReport));
    }

    public function testUpdateMovesGoalsOverviewToTheEcommerceOrderReportIfTheSiteHasEcommerceWhileTheEcommercePluginIsInactive(): void
    {
        $idReport = $this->storeReport($this->idSiteWithEcommerce, ['VisitsSummary_get', 'Goals_get_idGoal--0']);

        Manager::getInstance()->loadPlugins(array_diff(Manager::getInstance()->getActivatedPlugins(), ['Ecommerce']));

        $this->runUpdate();

        self::assertSame(['VisitsSummary_get', 'Goals_get_idGoal--ecommerceOrder'], $this->getStoredReports($idReport));
    }

    public function testUpdateMovesGoalsOverviewToTheAllGoalsReportIfTheSiteHasNoEcommerce(): void
    {
        $idReport = $this->storeReport($this->idSite, ['VisitsSummary_get', 'Goals_get_idGoal--0']);

        $this->runUpdate();

        self::assertSame(['VisitsSummary_get', 'Goals_get'], $this->getStoredReports($idReport));
    }

    public function testUpdateMovesTheConversionDistributionsToTheirAllGoalsReports(): void
    {
        $idReport = $this->storeReport(
            $this->idSiteWithEcommerce,
            ['Goals_getVisitsUntilConversion_idGoal--0', 'Goals_getDaysToConversion_idGoal--0']
        );

        $this->runUpdate();

        self::assertSame(['Goals_getVisitsUntilConversion', 'Goals_getDaysToConversion'], $this->getStoredReports($idReport));
    }

    public function testUpdateKeepsASingleEntryForAReportTheMoveNamesTwice(): void
    {
        $idReport = $this->storeReport($this->idSite, ['Goals_get', 'Goals_get_idGoal--0', 'VisitsSummary_get']);

        $this->runUpdate();

        self::assertSame(['Goals_get', 'VisitsSummary_get'], $this->getStoredReports($idReport));
    }

    public function testUpdateKeepsReportsWithoutRetiredIds(): void
    {
        $reports  = ['VisitsSummary_get', 'Goals_get'];
        $idReport = $this->storeReport($this->idSiteWithEcommerce, $reports);

        $this->runUpdate();

        self::assertSame($reports, $this->getStoredReports($idReport));
    }

    public function testUpdateHasNothingToDoOnceReportsAreMoved(): void
    {
        $this->storeReport($this->idSite, ['Goals_get_idGoal--0']);
        $this->storeReport($this->idSiteWithEcommerce, ['Goals_get_idGoal--0']);

        $this->runUpdate();

        self::assertSame([], $this->makeUpdate()->getMigrations(new Updater()));
    }

    /**
     * @param list<string> $reports
     */
    private function storeReport(int $idSite, array $reports): int
    {
        $idReport = APIScheduledReports::getInstance()->addReport(
            $idSite,
            'Test report',
            Schedule::PERIOD_DAY,
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT,
            ['VisitsSummary_get'],
            [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY]
        );

        Db::query(
            'UPDATE ' . Common::prefixTable('report') . ' SET reports = ? WHERE idreport = ?',
            [json_encode($reports), $idReport]
        );

        return (int) $idReport;
    }

    /**
     * @return list<string>
     */
    private function getStoredReports(int $idReport): array
    {
        $reports = Db::fetchOne(
            'SELECT reports FROM ' . Common::prefixTable('report') . ' WHERE idreport = ?',
            [$idReport]
        );

        return json_decode($reports, true);
    }

    private function runUpdate(): void
    {
        $this->makeUpdate()->doUpdate(new Updater());
    }

    private function makeUpdate(): Updates_6_0_0_b8
    {
        return StaticContainer::get(Updates_6_0_0_b8::class);
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
