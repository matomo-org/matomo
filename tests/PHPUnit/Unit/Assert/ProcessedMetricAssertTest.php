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
    public function test_assertProcessedMetricIsValidFor_failsWhenAFormulaIsAnInvalidExpression(string $formula, callable $compute, array $testData)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Formula for.*?did not evaluate to the same value as its compute\(\)/');

        $processedMetric = new class() extends ProcessedMetric {
            /**
             * @var string
             */
            private $formula;

            /**
             * @var callable
             */
            private $compute;

            public function __construct(string $formula, callable $compute)
            {
                $this->formula = $formula;
                $this->compute = $compute;
            }

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
                $compute = $this->compute;
                return $compute($row);
            }

            public function getDependentMetrics()
            {
                return [];
            }

            public function getFormula(): ?string
            {
                return $this->formula;
            }
        };

        $this->testInstance->assertProcessedMetricIsValidFor($processedMetric, new Row([Row::COLUMNS => $testData]));
    }

    private function getFailureTestDataForAssertProcessedMetricIsValidFor()
    {
        return [
            // formula is invalid
            [
                // TODO
            ],

            // metric used in formula does not exist in row
            [
                // TODO
            ],

            // formula and compute are different
            [
                // TODO
            ],

            // formula and compute are different, but only for some metric values
            [
                // TODO
            ],
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
