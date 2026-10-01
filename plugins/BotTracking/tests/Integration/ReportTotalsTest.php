<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Integration;

use Piwik\Plugin\Report;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\BotTracking\Metrics;
use Piwik\Plugins\BotTracking\Reports\AbstractAIChatbotsRealTimeReport;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The HTML table visualization only shows the percentage of the report total on hover for the
 * columns a report registers through Report::getMetricNamesToProcessReportTotals().
 *
 * @group BotTracking
 * @group BotTrackingReportTotals
 * @group Plugins
 */
class ReportTotalsTest extends IntegrationTestCase
{
    /**
     * Columns whose values do not add up over the rows of a report, so a percentage of the summed
     * up report total would be meaningless for them.
     */
    private const NON_ADDITIVE_COLUMNS = [
        Metrics::COLUMN_AVG_SERVER_TIME,
        Metrics::COLUMN_AVG_RESPONSE_SIZE,
        Metrics::COLUMN_DISCREPANCY_SCORE,
    ];

    public function testEveryAdditiveColumnOfAReportIsRegisteredToProcessReportTotals(): void
    {
        $reports = $this->getReportsWithProcessableTotals();

        $this->assertNotEmpty($reports);

        foreach ($reports as $report) {
            $reportName = $report->getModule() . '.' . $report->getAction();
            $additiveColumns = array_diff($report->getAllMetrics(), self::NON_ADDITIVE_COLUMNS);

            $this->assertEqualsCanonicalizing(
                array_values($additiveColumns),
                array_values($report->getMetricNamesToProcessReportTotals()),
                'Report ' . $reportName . ' registers the wrong columns to process report totals for.'
                    . ' Every column that adds up over the rows of the report has to be registered, so its'
                    . ' percentage of the report total is shown on hover, and no other column may be.'
            );
        }
    }

    public function testRealTimeReportsDoNotProcessReportTotals(): void
    {
        $reports = $this->getRealTimeReports();

        $this->assertNotEmpty($reports);

        foreach ($reports as $report) {
            $this->assertSame(
                [],
                $report->getMetricNamesToProcessReportTotals(),
                'Report ' . $report->getModule() . '.' . $report->getAction() . ' must not process report totals.'
            );
        }
    }

    /**
     * Reports without a dimension have a single row, so no report total is calculated for them.
     *
     * Real-time reports are left out as well: their rows are a live lookback window capped by the
     * DAO without a summary row for the remainder, and they carry neither the period nor the segment
     * metadata the ratio tooltip describes them with, so a percentage of their total would relate a
     * row to a population the report does not show.
     *
     * @return Report[]
     */
    private function getReportsWithProcessableTotals(): array
    {
        $reports = [];

        foreach ($this->getBotTrackingReports() as $report) {
            if ($report->getDimension() && !$report instanceof AbstractAIChatbotsRealTimeReport) {
                $reports[] = $report;
            }
        }

        return $reports;
    }

    /**
     * @return Report[]
     */
    private function getRealTimeReports(): array
    {
        $reports = [];

        foreach ($this->getBotTrackingReports() as $report) {
            if ($report instanceof AbstractAIChatbotsRealTimeReport) {
                $reports[] = $report;
            }
        }

        return $reports;
    }

    /**
     * @return Report[]
     */
    private function getBotTrackingReports(): array
    {
        $reports = [];

        foreach ((new ReportsProvider())->getAllReports() as $report) {
            if ($report->getModule() === 'BotTracking') {
                $reports[] = $report;
            }
        }

        return $reports;
    }
}
