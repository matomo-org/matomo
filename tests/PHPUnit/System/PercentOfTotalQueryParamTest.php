<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Date;
use Piwik\Tests\Fixtures\ManyVisitsWithMockLocationProvider;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Tests the percent-of-total metrics (eg, 'nb_visits_percent_of_total') that are added to
 * report rows based on the report totals, and the percent_of_total query parameter that
 * disables them.
 *
 * @group Core
 * @group PercentOfTotalQueryParamTest
 */
class PercentOfTotalQueryParamTest extends SystemTestCase
{
    /**
     * @var ManyVisitsWithMockLocationProvider
     */
    public static $fixture = null;

    public function testPercentOfTotalMetricsAreAddedByDefault()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", $this->defaultParams());
    }

    public function testPercentOfTotalParamDisablesTheMetrics()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", $this->defaultParams() + [
            'percent_of_total' => 0,
        ]);
    }

    public function testDisablingTotalsDisablesTheMetrics()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", $this->defaultParams() + [
            'totals' => 0,
        ]);
    }

    public function testPercentOfTotalParamAcceptsBooleanStrings()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", $this->defaultParams() + [
            'percent_of_total' => 'false',
        ]);
    }

    public function testTruncationSummaryRowGetsTheQuotientOfItsSummedMetrics()
    {
        // sorting by a percent-of-total column computes the processed metrics before the
        // Truncate filter runs; the summary row percentage must still be the quotient of its
        // summed metric values, not the sum of the per row quotients
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", $this->defaultParams() + [
            'filter_truncate' => 1,
            'filter_sort_column' => 'nb_visits_percent_of_total',
            'format_metrics' => 0,
        ]);
    }

    public function testPercentOfTotalMetricsAreNotFormattedWhenMetricFormattingIsOff()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", $this->defaultParams() + [
            'format_metrics' => 0,
        ]);
    }

    public function testSubtableRowsUseTheFirstLevelReportTotal()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getSearchEnginesFromKeywordId", $this->defaultParams() + [
            'idSubtable' => 1,
        ]);
    }

    public function testFlattenedRowsUseTheFirstLevelReportTotal()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", $this->defaultParams() + [
            'flat' => 1,
        ]);
    }

    public function testReportsWithoutDimensionGetNoPercentOfTotalMetrics()
    {
        $this->assertApiResponseEqualsExpected("VisitsSummary.get", $this->defaultParams());
    }

    public function testPercentOfTotalMetricsInCsvOutput()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", $this->defaultParams() + [
            'format' => 'csv',
        ]);
    }

    public function testPercentOfTotalMetricsInProcessedReport()
    {
        $this->assertApiResponseEqualsExpected("API.getProcessedReport", $this->defaultParams() + [
            'apiModule' => 'Referrers',
            'apiAction' => 'getKeywords',
        ]);
    }

    public function testProcessedReportKeepsMetricsDocumentationHidden()
    {
        // hideMetricsDoc=1 must not get the metricsDocumentation block recreated with only
        // the percent-of-total entries in it
        $this->assertApiResponseEqualsExpected("API.getProcessedReport", $this->defaultParams() + [
            'apiModule' => 'Referrers',
            'apiAction' => 'getKeywords',
            'hideMetricsDoc' => 1,
        ]);
    }

    public function testPercentOfTotalMetricsWithMultiplePeriods()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", [
            'idSite' => self::$fixture->idSite,
            'date' => '2010-01-01,2010-01-07',
            'period' => 'day',
        ]);
    }

    private function defaultParams(): array
    {
        return [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
        ];
    }
}

PercentOfTotalQueryParamTest::$fixture = new ManyVisitsWithMockLocationProvider();
