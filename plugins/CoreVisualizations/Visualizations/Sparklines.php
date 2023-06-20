<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Metrics\Formatter as MetricFormatter;
use Piwik\Period\Factory;
use Piwik\Plugin\Report;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\API\Filter\DataComparisonFilter;
use Piwik\SettingsPiwik;
use Piwik\View;

/**
 * Reads the requested DataTable from the API and prepares data for the Sparklines view. It can display any amount
 * of sparklines. Within a reporting page sparklines are shown in 2 columns, in a dashboard or when exported as a widget
 * the sparklines are shown in one column.
 *
 * The sparklines view currently only supports requesting columns from the same API (the API method of the defining
 * report) via {Sparklines\Config::addSparklineMetric($columns = array('nb_visits', 'nb_unique_visitors'))}.
 *
 * Example:
 * $view->config->addSparklineMetric('nb_visits'); // if an array of metrics given, they will be displayed comma separated
 * $view->config->addTranslation('nb_visits', 'Visits');
 * Results in: [sparkline image] X visits
 * Data is fetched from the configured $view->requestConfig->apiMethodToRequestDataTable.
 *
 * In case you want to add any custom sparklines from any other API method you can call
 * {@link Sparklines\Config::addSparkline()}.
 *
 * Example:
 * $sparklineUrlParams = array('columns' => array('nb_visits));
 * $evolution = array('currentValue' => 5, 'pastValue' => 10, 'tooltip' => 'Foo bar');
 * $view->config->addSparkline($sparklineUrlParams, $value = 5, $description = 'Visits', $evolution);
 *
 * @property Sparklines\Config $config
 */
class Sparklines extends ViewDataTable
{
    const ID = 'sparklines';

    public static function getDefaultConfig()
    {
        return new Sparklines\Config();
    }

    public function supportsComparison()
    {
        return true;
    }

    /**
     * @see ViewDataTable::main()
     * @return mixed
     */
    public function render()
    {
        $view = new View('@CoreVisualizations/_dataTableViz_sparklines.twig');

        $columnsList = [];
        if ($this->config->hasSparklineMetrics()) {
            foreach ($this->config->getSparklineMetrics() as $cols) {
                $columns = $cols['columns'];
                if (!is_array($columns)) {
                    $columns = [$columns];
                }

                $columnsList = array_merge($columns, $columnsList);
            }
        }

        $view->allMetricsDocumentation = array_merge(Metrics::getDefaultMetricsDocumentation(), $this->config->metrics_documentation);

        $this->requestConfig->request_parameters_to_modify['columns'] = $columnsList;
        $this->requestConfig->request_parameters_to_modify['format_metrics'] = '1';

        /**
         * This special request parameter is used to include trend indication columns for all evolution columns
         * this is done to be able to determine safely in the view if an evolution is positive or negative, as this
         * can't be done with formatted evolution values due to language specific signs being used.
         *
         * @see DataComparisonFilter::compareChangePercents
         */
        $this->requestConfig->request_parameters_to_modify['include_trends'] = '1';

        $request = $this->getRequestArray();
        if (
            $this->isComparing()
            && !empty($request['comparePeriods'])
            && count($request['comparePeriods']) == 1
        ) {
            $this->requestConfig->request_parameters_to_modify['invert_compare_change_compute'] = 1;
        }

        if (!empty($this->requestConfig->apiMethodToRequestDataTable)) {
            $this->fetchConfiguredSparklines();
        }

        $view->sparklines = $this->config->getSortedSparklines();
        $view->isWidget = Common::getRequestVar('widget', 0, 'int');
        $view->titleAttributes = $this->config->title_attributes;
        $view->footerMessage = $this->config->show_footer_message;
        $view->areSparklinesLinkable = $this->config->areSparklinesLinkable();
        $view->isComparing = $this->isComparing();

        $view->title = '';
        if ($this->config->show_title) {
            $view->title = $this->config->title;
        }

        return $view->render();
    }

    /**
     * Load the datatable from the API using the pre-configured request object
     *
     * @param array $forcedParams
     *
     * @return DataTable
     */
    protected function loadDataTableFromAPI(array $forcedParams = [])
    {
        if (!is_null($this->dataTable)) {
            // data table is already there
            // this happens when setDataTable has been used
            return $this->dataTable;
        }

        if ($this->isComparing()) {
            $forcedParams['compare'] = '1';
        }

        $this->dataTable = $this->request->loadDataTableFromAPI($forcedParams);

        return $this->dataTable;
    }

    private function fetchConfiguredSparklines()
    {
        $data = $this->loadDataTableFromAPI(['format_metrics' => '0']);

        $this->applyFilters($data);

        if (!$this->config->hasSparklineMetrics()) {
            foreach ($data->getColumns() as $column) {
                $this->config->addSparklineMetric($column);
            }
        }

        $report = ReportsProvider::factory($this->requestConfig->getApiModuleToRequest(), $this->requestConfig->getApiMethodToRequest());
        $processedMetrics = Report::getProcessedMetricsForTable($data, $report);
        $metricFormatter = new MetricFormatter();
        $idSite = $this->getRequestArray()['idSite'] ?? false;

        $firstRow = $data->getFirstRow();
        if ($firstRow) {
            $comparisons = $firstRow->getComparisons();
        } else {
            $comparisons = null;
        }

        $originalDate = Common::getRequestVar('date');
        $originalPeriod = Common::getRequestVar('period');

        $isComparing = $this->isComparing() && !empty($comparisons);
        if ($isComparing) {
            $comparisonRows = [];
            foreach ($comparisons->getRows() as $comparisonRow) {
                $segment = $comparisonRow->getMetadata('compareSegment');
                if ($segment === false) {
                    $segment = Request::getRawSegmentFromRequest() ?: '';
                }

                $date = $comparisonRow->getMetadata('compareDate');
                $period = $comparisonRow->getMetadata('comparePeriod');

                $comparisonRows[$segment][$period][$date] = $comparisonRow;
            }
        }

        foreach ($this->config->getSparklineMetrics() as $sparklineMetricIndex => $sparklineMetric) {
            $column = $sparklineMetric['columns'];
            $order  = $sparklineMetric['order'];
            $graphParams = $sparklineMetric['graphParams'];

            if (!isset($order)) {
                $order = 1000;
            }

            if ($column === 'label') {
                continue;
            }

            if (empty($column)) {
                $this->config->addPlaceholder($order);
                continue;
            }

            if (!is_array($column)) {
                $column = [$column];
            }

            $columnMetrics = [];
            foreach ($column as $col) {
                foreach ($processedMetrics as $metricObj) {
                    if ($metricObj->getName() === $col) {
                        $columnMetrics[$col] = $metricObj;
                        break;
                    }
                }
            }

            $sparklineUrlParams = array_merge($this->config->custom_parameters, [
                'columns' => $column,
                'module'  => $this->requestConfig->getApiModuleToRequest(),
                'action'  => $this->requestConfig->getApiMethodToRequest()
            ]);

            $periodObj = Factory::build($originalPeriod, $originalDate);
            $comparePeriods = $data->getMetadata('comparePeriods');
            $compareDates = $data->getMetadata('compareDates');

            // the first entry includes the original period and we need to remove it
            $compareDatesWithoutOriginalDate = $compareDates ? array_slice($compareDates, 1) : [];
            $comparePeriodsWithoutOriginalPeriod = $comparePeriods ? array_slice($comparePeriods, 1) : [];

            $periodSelector = new EvolutionPeriodSelector($this->config);
            $comparisonPeriods = $periodSelector->getComparisonPeriodObjects($comparePeriodsWithoutOriginalPeriod, $compareDatesWithoutOriginalDate);
            $sparklineUrlParams = $periodSelector->setDatePeriods($sparklineUrlParams, $periodObj, $comparisonPeriods, $isComparing);

            if ($isComparing) {
                $sparklineUrlParams['compareSegments'] = [];

                $compareSegments = $data->getMetadata('compareSegments');
                foreach ($compareSegments as $segmentIndex => $segment) {
                    $metrics = [];
                    $seriesIndices = [];

                    foreach ($comparePeriods as $periodIndex => $period) {
                        $date = $compareDates[$periodIndex];

                        $compareRow = $comparisonRows[$segment][$period][$date];
                        $segmentPretty = $compareRow->getMetadata('compareSegmentPretty');
                        $periodPretty = $compareRow->getMetadata('comparePeriodPretty');

                        $columnToUse = $this->removeUniqueVisitorsIfNotEnabledForPeriod($column, $period);

                        [$compareValues, $compareDescriptions, $evolutions] = $this->getValuesAndDescriptions($compareRow, $columnToUse, '_change', '_trend');

                        foreach ($compareValues as $i => $value) {
                            if (!isset($column[$i])) {
                                continue;
                            }
                            if (isset($columnMetrics[$column[$i]]) && $columnMetrics[$column[$i]]) {
                                $value = $columnMetrics[$columnToUse[$i]]->format($value, $metricFormatter);
                            } elseif (strpos($columnToUse[$i], 'revenue') !== false && $idSite > 0) {
                                $value = $metricFormatter->getPrettyMoney($value, $idSite);
                            }

                            $metricInfo = [
                                'value' => $value,
                                'description' => $compareDescriptions[$i],
                                'group' => $periodPretty,
                            ];

                            if (isset($evolutions[$i])) {
                                $metricInfo['evolution'] = $evolutions[$i];
                            }

                            $metrics[] = $metricInfo;
                        }

                        $seriesIndices[] = DataComparisonFilter::getComparisonSeriesIndex($data, $periodIndex, $segmentIndex);
                    }

                    // only set the title (which is the segment) if comparing more than one segment
                    $title = count($compareSegments) > 1 ? $segmentPretty : null;

                    $params = array_merge($sparklineUrlParams, [
                        'segment' => $segment,
                    ]);

                    $this->config->addSparkline($params, $metrics, $desc = null, null, ($order * 100) + $segmentIndex, $title, $sparklineMetricIndex, $seriesIndices, $graphParams);
                }
            } else {
                [$values, $descriptions] = $this->getValuesAndDescriptions($firstRow, $column);

                $metrics = [];
                foreach ($values as $i => $value) {
                    if (!isset($column[$i])) {
                        continue;
                    }
                    if (isset($columnMetrics[$column[$i]]) && $columnMetrics[$column[$i]]) {
                        $value = $columnMetrics[$column[$i]]->format($value, $metricFormatter);
                    } elseif (strpos($column[$i], 'revenue') !== false && $idSite > 0) {
                        $value = $metricFormatter->getPrettyMoney($value, $idSite);
                    }

                    $newMetric = [
                        'value' => $value,
                        'description' => $descriptions[$i],
                    ];

                    $metrics[] = $newMetric;
                }

                $evolution = null;

                $computeEvolution = $this->config->compute_evolution;
                if ($computeEvolution) {
                    $evolution = $computeEvolution(array_combine((is_array($column) ? $column : [$column]), $values), $processedMetrics);
                    $newMetric['evolution'] = $evolution;
                }

                $this->config->addSparkline($sparklineUrlParams, $metrics, $desc = null, $evolution, $order, $title = null, $group = $sparklineMetricIndex, $seriesIndices = null, $graphParams);
            }
        }
    }

    private function applyFilters(DataTable\DataTableInterface $table)
    {
        foreach ($this->config->getPriorityFilters() as $filter) {
            $table->filter($filter[0], $filter[1]);
        }

        // queue other filters so they can be applied later if queued filters are disabled
        foreach ($this->config->getPresentationFilters() as $filter) {
            $table->queueFilter($filter[0], $filter[1]);
        }

        $table->applyQueuedFilters();
    }

    private function getValuesAndDescriptions($firstRow, $columns, $evolutionColumnNameSuffix = null, $trendColumnNameSuffix = null)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $translations = $this->config->translations;

        $values = [];
        $descriptions = [];
        $evolutions = [];

        foreach ($columns as $col) {
            $value = 0;
            if ($firstRow) {
                $value = $firstRow->getColumn($col);
            }

            if ($value === false) {
                $value = 0;
            }

            if ($evolutionColumnNameSuffix !== null) {
                $evolution = $firstRow->getColumn($col . $evolutionColumnNameSuffix);
                $trend = $firstRow->getColumn($col . $trendColumnNameSuffix);
                if ($evolution !== false) {
                    $evolutions[] = ['percent' => ltrim($evolution, '+'), 'trend' => $trend, 'tooltip' => ''];
                }
            }

            $values[] = $value;
            $descriptions[] = isset($translations[$col]) ? $translations[$col] : $col;
        }

        return [$values, $descriptions, $evolutions];
    }

    private function removeUniqueVisitorsIfNotEnabledForPeriod($columns, $period)
    {
        if (SettingsPiwik::isUniqueVisitorsEnabled($period)) {
            return $columns;
        }

        if (!is_array($columns)) {
            $columns = [$columns];
        }

        return array_diff($columns, ['nb_users', 'nb_uniq_visitors']);
    }
}
