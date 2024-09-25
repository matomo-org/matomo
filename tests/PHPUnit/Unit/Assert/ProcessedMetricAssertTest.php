<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Assert;

use PHPUnit\Framework\TestCase;
use Piwik\DataTable\Row;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;
use Piwik\Tests\Framework\Assert\ProcessedMetricAssert;

class ProcessedMetricAssertTest extends TestCase
{
    /**
     * @var ProcessedMetricAssert
     */
    private $testInstance;

    public function setUp(): void
    {
        parent::setUp();
        $this->testInstance = new ProcessedMetricAssert();
    }

    public function test_assertProcessedMetricIsValidFor_doesNothingIfTheProcessedMetricFormulaIsEmpty()
    {
        $this->expectNotToPerformAssertions();

        $processedMetric = new class() extends ProcessedMetric {
            public function getName()
            {
                return 'test_metric';
            }

            public function getTranslatedName()
            {
                return 'TestPlugin_TestMetric';
            }

            public function compute(Row $row)
            {
                // empty
            }

            public function getDependentMetrics()
            {
                return [];
            }
        };

        $testRow = new Row([
            Row::COLUMNS => [
                'nb_visits' => 1,
                'nb_hits' => 2,
            ],
        ]);
        $this->testInstance->assertProcessedMetricIsValidFor($processedMetric, $testRow);
    }

    public function test_assertProcessedMetricIsValidFor_passesWhenAFormulaMatchesTheComputeMethod()
    {
        $processedMetric = new class() extends ProcessedMetric {
            public function getName()
            {
                return 'test_metric';
            }

            public function getTranslatedName()
            {
                return 'TestPlugin_TestMetric';
            }

            public function compute(Row $row)
            {
                return Piwik::getQuotientSafe(
                    self::getMetric($row, 'nb_visits'),
                    self::getMetric($row, 'nb_hits'),
                    2
                );
            }

            public function getDependentMetrics()
            {
                return ['nb_visits', 'nb_hits'];
            }

            public function getFormula(): ?string
            {
                return 'nb_visits / nb_hits';
            }
        };

        $testRow = new Row([
            Row::COLUMNS => [
                'nb_visits' => 1,
                'nb_hits' => 2,
            ],
        ]);
        $this->testInstance->assertProcessedMetricIsValidFor($processedMetric, $testRow);
    }

    /**
     * @dataProvider getFailureTestDataForAssertProcessedMetricIsValidFor
     */
    public function test_assertProcessedMetricIsValidFor_failsWhenAFormulaIsAnInvalidExpression(string $formula, Row $testData)
    {
        // TODO
    }

    private function getFailureTestDataForAssertProcessedMetricIsValidFor()
    {
        return [
            // TODO
        ];
    }

    public function test_checkProcessedMetricsInReport_failsIfAProcessedMetricReferencesAnUnknownColumn()
    {
        // TODO
    }

    public function test_checkProcessedMetricsInReport_failsIfAProcessedMetricFormulaCannotBeParsed()
    {
        // TODO
    }

    public function test_checkProcessedMetricsInReport_passesIfAllProcessedMetricFormulasAreValid()
    {
        // TODO
    }
}
