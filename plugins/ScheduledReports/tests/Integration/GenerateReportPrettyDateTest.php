<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ScheduledReports\tests\Integration;

use Piwik\Date;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Piwik;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\ReportRenderer;
use Piwik\ReportRenderer\Html;
use Piwik\Scheduler\Schedule\Schedule;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ScheduledReports
 * @group Plugins
 */
class GenerateReportPrettyDateTest extends IntegrationTestCase
{
    /** 01:00 on 2026-09-09 in Auckland, so the site's yesterday is a day after UTC's. */
    private const NOW = '2026-09-08 13:00:00';

    private ?int $originalNow = null;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();
        Fixture::createSuperUser(true);

        // Two sites, so MultiSites.getAll resolves the relative date on UTC's days.
        foreach (['Timezone probe', 'Timezone probe two'] as $name) {
            $idSite = Fixture::createWebsite('2026-09-01 00:00:00', 0, $name, false, 1, null, null, 'Pacific/Auckland');
            Fixture::checkResponse(Fixture::getTracker($idSite, '2026-09-07 13:00:00')->doTrackPageView('/'));
        }

        $this->originalNow = Date::$now;
        Date::$now = (int) strtotime(self::NOW . ' UTC');
    }

    public function tearDown(): void
    {
        Date::$now = $this->originalNow;

        parent::tearDown();
    }

    /**
     * @dataProvider getReportOrders
     * @param string[] $reports
     */
    public function testAReportMixingMultiSitesWithSiteReportsIsLabelledOnTheSitesDays(array $reports): void
    {
        $idReport = APIScheduledReports::getInstance()->addReport(
            1,
            'Mixed report',
            Schedule::PERIOD_DAY,
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT,
            $reports,
            [
                ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY,
                ScheduledReports::ENFORCE_ORDER_PARAMETER => true,
            ]
        );

        $rendered = APIScheduledReports::getInstance()->generateReport(
            $idReport,
            'yesterday',
            false,
            APIScheduledReports::OUTPUT_RETURN
        );

        self::assertLabelledOn('2026-09-08', '2026-09-07', $rendered);
    }

    public function getReportOrders(): array
    {
        return [
            'MultiSites last' => [['VisitsSummary_get', 'MultiSites_getAll']],
            'MultiSites first' => [['MultiSites_getAll', 'VisitsSummary_get']],
        ];
    }

    public function testAMultiSitesOnlyReportKeepsItsOwnLabel(): void
    {
        $idReport = APIScheduledReports::getInstance()->addReport(
            1,
            'All websites',
            Schedule::PERIOD_DAY,
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT,
            ['MultiSites_getAll'],
            [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY]
        );

        $rendered = APIScheduledReports::getInstance()->generateReport(
            $idReport,
            'yesterday',
            false,
            APIScheduledReports::OUTPUT_RETURN
        );

        self::assertLabelledOn('2026-09-07', '2026-09-08', $rendered);
    }

    public function testASiteReportsDateLineAndFileNameNameTheSitesDay(): void
    {
        $download = ['filename' => null, 'content' => null];
        Piwik::addAction(
            APIScheduledReports::GET_RENDERER_INSTANCE_EVENT,
            function (&$reportRenderer) use (&$download) {
                $reportRenderer = new class ($download) extends Html {
                    /** @var array{filename: string|null, content: string|null} */
                    private array $download;

                    /** @param array{filename: string|null, content: string|null} $download */
                    public function __construct(array &$download)
                    {
                        $this->download = &$download;
                    }

                    public function sendToBrowserDownload($filename)
                    {
                        $this->download = ['filename' => $filename, 'content' => $this->getRenderedReport()];
                    }
                };
            }
        );

        $idReport = APIScheduledReports::getInstance()->addReport(
            1,
            'Site report',
            Schedule::PERIOD_DAY,
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT,
            ['VisitsSummary_get'],
            [ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY]
        );

        APIScheduledReports::getInstance()->generateReport(
            $idReport,
            'yesterday',
            false,
            APIScheduledReports::OUTPUT_DOWNLOAD
        );

        $siteDay = PeriodFactory::build('day', '2026-09-08')->getLocalizedLongString();
        self::assertSame("Timezone probe - $siteDay - Site report", $download['filename']);
        self::assertLabelledOn('2026-09-08', '2026-09-07', (string) $download['content']);
    }

    private static function assertLabelledOn(string $expectedDay, string $otherDay, string $rendered): void
    {
        self::assertStringContainsString(PeriodFactory::build('day', $expectedDay)->getLocalizedLongString(), $rendered);
        self::assertStringNotContainsString(PeriodFactory::build('day', $otherDay)->getLocalizedLongString(), $rendered);
    }
}
