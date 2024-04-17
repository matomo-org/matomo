<?php

/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration\Columns\Metrics;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\Plugins\CoreHome\Columns\Metrics\EvolutionMetric;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreHome
 * @group EvolutionMetricTest
 * @group Plugins
 * @group Columns
 */
class EvolutionMetricTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2022-01-01 00:00:00');
    }

    public function test_shouldNotBreakIfSummaryRowGiven()
    {
        $this->assertNoFailureOnComputeForLabel(DataTable::ID_SUMMARY_ROW);
    }

    public function test_shouldNotBreakIfTotalsRowGiven()
    {
        $this->assertNoFailureOnComputeForLabel(DataTable::LABEL_TOTALS_ROW);
    }

    private function assertNoFailureOnComputeForLabel($label): void
    {
        $pastData = new DataTable();
        $cPeriod = new \Piwik\Period\Week(Date::factory('2021-10-10'));
        $pastData->setMetadata('period', $cPeriod);

        $evolution = new EvolutionMetric('nb_visits', $pastData);

        $row = new Row([Row::COLUMNS => ['nb_visits' => 5, 'label' => $label]]);
        $evolution->compute($row);

        $currency = $row->getMetadata('currencySymbol');
        $this->assertNotEmpty($currency);
    }
}
