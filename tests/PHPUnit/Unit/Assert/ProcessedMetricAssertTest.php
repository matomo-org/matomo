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
    public function test_assertProcessedMetricIsValidFor_failsWhenAFormulaIsAnInvalid(string $formula, callable $compute, array $testData, string $exceptionMessageRegex)
    {
        $processedMetric = new class($formula, $compute) extends ProcessedMetric {
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

        try {
            $this->testInstance->assertProcessedMetricIsValidFor($processedMetric, new Row([Row::COLUMNS => $testData]));
            $this->fail('assertProcessedMetricIsValidFor did not fail when formula does not match compute');
        } catch (\Exception $ex) {
            $this->assertRegExp($exceptionMessageRegex, $ex->getMessage());
        }
    }

    public function getFailureTestDataForAssertProcessedMetricIsValidFor()
    {
        return [
            // formula is invalid
            [
                'a!bc#4 >> 23',
                function (Row $row) {
                    return 5;
                },
                [],
                '/^Unexpected character "#" around position 4 for expression/',
            ],

            // metric used in formula does not exist in row
            [
                'metric1 + metric2',
                function (Row $row) {
                    return $row->getColumn('metric1') + $row->getColumn('metric2');
                },
                [
                    'metric1' => 5,
                    'metric3' => 10,
                ],
                '/^Variable "metric2" is not valid around position 11/',
            ],

            // formula and compute are different
            [
                'metric1 + metric2',
                function (Row $row) {
                    return $row->getColumn('metric1') - $row->getColumn('metric2');
                },
                [
                    'metric1' => 10,
                    'metric2' => 3,
                ],
                '/Formula for.*?did not evaluate to the same value as its compute\(\)/',
            ],
        ];
    }

    public function test_checkProcessedMetricsInReport_failsIfAProcessedMetricReferencesAnUnknownColumn()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Variable "nb_hits" is not valid around position 1 for expression');

        $testReport = new class extends Report {
            protected function init()
            {
                parent::init();

                $processedMetric = new class extends ProcessedMetric {
                    public function getName()
                    {
                        return 'test_metric';
                    }

                    public function getTranslatedName()
                    {
                        return 'test metric';
                    }

                    public function compute(Row $row)
                    {
                        return $row->getColumn('nb_hits') / $row->getColumn('nb_actions');
                    }

                    public function getDependentMetrics()
                    {
                        return ['nb_hits', 'nb_actions'];
                    }

                    public function getFormula(): ?string
                    {
                        return 'nb_hits / nb_actions';
                    }
                };

                $this->metrics = [
                    'nb_visits',
                    'nb_actions',
                ];
                $this->processedMetrics = [
                    $processedMetric,
                ];
            }
        };

        $this->testInstance->checkProcessedMetricsInReport($testReport);
    }

    public function test_checkProcessedMetricsInReport_failsIfAProcessedMetricFormulaCannotBeParsed()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Variable "this" is not valid around position 1 for expression');

        $testReport = new class extends Report {
            protected function init()
            {
                parent::init();

                $processedMetric = new class extends ProcessedMetric {
                    public function getName()
                    {
                        return 'test_metric';
                    }

                    public function getTranslatedName()
                    {
                        return 'Test Metric';
                    }

                    public function compute(Row $row)
                    {
                        // empty
                    }

                    public function getDependentMetrics()
                    {
                        return [];
                    }

                    public function getFormula(): ?string
                    {
                        return 'this is not a formula!!!';
                    }
                };

                $this->metrics = [
                    'nb_visits',
                    'nb_actions',
                ];

                $this->processedMetrics = [
                    $processedMetric,
                ];
            }
        };

        $this->testInstance->checkProcessedMetricsInReport($testReport);
    }

    public function test_checkProcessedMetricsInReport_passesIfAllProcessedMetricFormulasAreValid()
    {
        $testReport = new class extends Report {
            protected function init()
            {
                parent::init();

                $processedMetric = new class extends ProcessedMetric {
                    public function getName()
                    {
                        return 'test_metric';
                    }

                    public function getTranslatedName()
                    {
                        return 'Test Metric';
                    }

                    public function compute(Row $row)
                    {
                        return $row->getColumn('nb_visits') / $row->getColumn('nb_actions');
                    }

                    public function getDependentMetrics()
                    {
                        return ['nb_visits', 'nb_actions'];
                    }

                    public function getFormula(): ?string
                    {
                        return 'nb_visits / nb_actions';
                    }
                };

                $this->metrics = [
                    'nb_visits',
                    'nb_actions',
                ];

                $this->processedMetrics = [
                    $processedMetric,
                ];
            }
        };

        $this->testInstance->checkProcessedMetricsInReport($testReport);
    }
}
