<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API;

use Exception;
use Piwik\API\DataTableManipulator\LabelFilter;
use Piwik\API\DataTablePostProcessor;
use Piwik\API\Request;
use Piwik\Cache;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\DataTable\Filter\CalculateEvolutionFilter;
use Piwik\DataTable\Filter\SafeDecodeLabel;
use Piwik\DataTable\Row;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\API\Filter\DataComparisonFilter;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\ConversionRate;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\Conversions;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\Revenue;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\RevenuePerVisit;
use Piwik\Site;
use Piwik\Url;

/**
 * This class generates a Row evolution dataset, from input request
 *
 */
class RowEvolution
{
    private static $actionsUrlReports = [
        'getPageUrls',
        'getPageUrlsFollowingSiteSearch',
        'getEntryPageUrls',
        'getExitPageUrls',
        'getPageUrl'
    ];

    public function getRowEvolution($idSite, $period, $date, $apiModule, $apiAction, $label = false, $segment = false, $column = false, $language = false, $apiParameters = [], $legendAppendMetric = true, $labelUseAbsoluteUrl = true, $labelSeries = '', $showGoalMetricsForGoal = false)
    {
        // validation of requested $period & $date
        if ($period == 'range') {
            // load days in the range
            $period = 'day';
        }

        if (!Period::isMultiplePeriod($date, $period)) {
            throw new Exception("Row evolutions can not be processed with this combination of \'date\' and \'period\' parameters.");
        }

        $label = DataTablePostProcessor::unsanitizeLabelParameter($label);
        $labels = Piwik::getArrayFromApiParameter($label, $onlyUnique = empty($labelSeries));

        $metadata = $this->getRowEvolutionMetaData($idSite, $period, $date, $apiModule, $apiAction, $language, $apiParameters);

        // if goal metrics should be shown, we replace the metrics
        if ($showGoalMetricsForGoal !== false) {
            $metadata['metrics'] = [
                'nb_visits' => $metadata['metrics']['nb_visits'],
            ];

            // Use ecommerce specific metrics / column names when only showing ecommerce metrics
            if ($showGoalMetricsForGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {
                $metadata['metrics']['goal_ecommerceOrder_nb_conversions'] = Piwik::translate('General_EcommerceOrders');
                $metadata['metrics']['goal_ecommerceOrder_revenue'] = Piwik::translate('General_TotalRevenue');
                $metadata['metrics']['goal_ecommerceOrder_conversion_rate'] = Piwik::translate('Goals_ConversionRate', Piwik::translate('General_EcommerceOrders'));
                $metadata['metrics']['goal_ecommerceOrder_avg_order_revenue'] = Piwik::translate('General_AverageOrderValue');
                $metadata['metrics']['goal_ecommerceOrder_items'] = Piwik::translate('General_PurchasedProducts');
                $metadata['metrics']['goal_ecommerceOrder_revenue_per_visit'] = Piwik::translate('General_ColumnValuePerVisit');
            } else {
                $goalsToProcess = $this->getGoalsToProcess($showGoalMetricsForGoal, $idSite);

                foreach ($goalsToProcess as $idGoal) {
                    if ($idGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {

                        $metadata['metrics']['goal_ecommerceOrder_conversion_rate'] = Piwik::translate('Goals_ConversionRate', Piwik::translate('General_EcommerceOrders'));

                        if ((int) $showGoalMetricsForGoal === AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW) {
                            // only conversion rate is used for goals overview
                            continue;
                        }

                        $metadata['metrics']['goal_ecommerceOrder_nb_conversions'] = Piwik::translate('Goals_Conversions', Piwik::translate('General_EcommerceOrders'));
                        $metadata['metrics']['goal_ecommerceOrder_revenue'] = Piwik::translate('General_EcommerceOrders') . ' ' . Piwik::translate('General_ColumnRevenue');
                        $metadata['metrics']['goal_ecommerceOrder_revenue_per_visit'] = Piwik::translate('General_EcommerceOrders') . ' ' . Piwik::translate('General_ColumnValuePerVisit');
                        continue;
                    }

                    $conversionRateMetric  = new ConversionRate($idSite, $idGoal);
                    $metadata['metrics'][$conversionRateMetric->getName()]  = $conversionRateMetric->getTranslatedName();

                    if ((int) $showGoalMetricsForGoal === AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW) {
                        // only conversion rate is used for goals overview
                        continue;
                    }

                    $conversionsMetric     = new Conversions($idSite, $idGoal);
                    $revenueMetric         = new Revenue($idSite, $idGoal);
                    $revenuePerVisitMetric = new RevenuePerVisit($idSite, $idGoal);

                    $metadata['metrics'][$conversionsMetric->getName()]     = $conversionsMetric->getTranslatedName();
                    $metadata['metrics'][$revenueMetric->getName()]         = $revenueMetric->getTranslatedName();
                    $metadata['metrics'][$revenuePerVisitMetric->getName()] = $revenuePerVisitMetric->getTranslatedName();
                }

                $metadata['metrics']['revenue_per_visit'] = Piwik::translate('General_ColumnValuePerVisit');
            }
        }

        $dataTable = $this->loadRowEvolutionDataFromAPI($metadata, $idSite, $period, $date, $apiModule, $apiAction, $labels, $segment, $apiParameters, $showGoalMetricsForGoal);

        if (empty($dataTable->getDataTables())) {
            return [];
        }

        if (empty($labels)) {
            $labels = $this->getLabelsFromDataTable($dataTable, $labels);
            $dataTable = $this->enrichRowAddMetadataLabelIndex($labels, $dataTable);
        }
        if (count($labels) != 1) {
            $data = $this->getMultiRowEvolution(
                $dataTable,
                $metadata,
                $apiModule,
                $apiAction,
                $labels,
                $column,
                $legendAppendMetric,
                $labelUseAbsoluteUrl,
                $labelSeries
            );
        } else {
            $data = $this->getSingleRowEvolution(
                $idSite,
                $dataTable,
                $metadata,
                $apiModule,
                $apiAction,
                $labels[0],
                $labelUseAbsoluteUrl
            );
        }
        return $data;
    }

    protected function getGoalsToProcess($goalId, $idSite): array
    {
        switch ($goalId) {
            case AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE:
            case AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW:
                return $this->getAllGoalIds($idSite);
            case Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER:
            default:
                return [$goalId];
        }
    }

    protected function getAllGoalIds($idSite): array
    {
        $cache = Cache::getTransientCache();
        $key   = 'RowEvolution_allGoalIds_' . $idSite;

        if ($cache->contains($key)) {
            return $cache->fetch($key);
        }

        $goalIds = [];

        if (Site::isEcommerceEnabledFor($idSite)) {
            $goalIds[] = Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER;
        }

        $siteGoals = Request::processRequest('Goals.getGoals', ['idSite' => $idSite, 'filter_limit' => '-1'], $default = []);

        foreach ($siteGoals as $goal) {
            $goalIds[] = $goal['idgoal'];
        }

        $cache->save($key, $goalIds);

        return $goalIds;
    }

    /**
     * @param array $labels
     * @param DataTable\Map $dataTable
     * @return mixed
     */
    protected function enrichRowAddMetadataLabelIndex($labels, $dataTable)
    {
        // set label index metadata
        $labelsToIndex = array_flip($labels);
        foreach ($dataTable->getDataTables() as $table) {
            foreach ($table->getRows() as $row) {
                $label = $row->getColumn('label');
                if (isset($labelsToIndex[$label])) {
                    $row->setMetadata(LabelFilter::FLAG_IS_ROW_EVOLUTION, $labelsToIndex[$label]);
                }
            }
        }
        return $dataTable;
    }

    /**
     * @param DataTable\Map $dataTable
     * @param array $labels
     * @return array
     */
    protected function getLabelsFromDataTable($dataTable, $labels)
    {
        // if no labels specified, use all possible labels as list
        foreach ($dataTable->getDataTables() as $table) {
            $labels = array_merge($labels, $table->getColumn('label'));
        }
        $labels = array_values(array_unique($labels));

        // if the filter_limit query param is set, treat it as a request to limit
        // the number of labels used
        $limit = Common::getRequestVar('filter_limit', false);
        if ($limit != false
            && $limit >= 0
        ) {
            $labels = array_slice($labels, 0, $limit);
        }
        return $labels;
    }

    /**
     * Get row evolution for a single label
     * @param DataTable\Map $dataTable
     * @param array $metadata
     * @param string $apiModule
     * @param string $apiAction
     * @param string $label
     * @param bool $labelUseAbsoluteUrl
     * @return array containing  report data, metadata, label, logo
     */
    private function getSingleRowEvolution($idSite, $dataTable, $metadata, $apiModule, $apiAction, $label, $labelUseAbsoluteUrl = true)
    {
        $metricNames = array_keys($metadata['metrics']);

        $logo = $actualLabel = false;
        $urlFound = false;
        foreach ($dataTable->getDataTables() as $subTable) {
            /** @var $subTable DataTable */
            $subTable->applyQueuedFilters();
            if ($subTable->getRowsCount() > 0) {
                /** @var $row Row */
                $row = $subTable->getFirstRow();

                if (!$actualLabel) {
                    $logo = $row->getMetadata('logo');

                    $actualLabel = $this->getRowUrlForEvolutionLabel($row, $apiModule, $apiAction, $labelUseAbsoluteUrl);
                    $urlFound = $actualLabel !== false;
                    if (empty($actualLabel)) {
                        $actualLabel = $row->getColumn('label');
                    }
                }

                // remove all columns that are not in the available metrics.
                // this removes the label as well (which is desired for two reasons: (1) it was passed
                // in the request, (2) it would cause the evolution graph to show the label in the legend).
                foreach ($row->getColumns() as $column => $value) {
                    if (!in_array($column, $metricNames) && $column != 'label_html') {
                        $row->deleteColumn($column);
                    }
                }
                $row->deleteMetadata();
            }
        }

        $this->enhanceRowEvolutionMetaData($metadata, $dataTable);

        // if we have a recursive label and no url, use the path
        if (!$urlFound) {
            $label = Common::getRequestVar('labelPretty', $label, 'string');
            $label = Common::unsanitizeInputValue($label);
            $actualLabel = $this->formatQueryLabelForDisplay($idSite, $apiModule, $apiAction, $label);
        }

        $return = [
            'label'      => SafeDecodeLabel::decodeLabelSafe($actualLabel),
            'reportData' => $dataTable,
            'metadata'   => $metadata,
        ];
        if (!empty($logo)) {
            $return['logo'] = $logo;
        }
        return $return;
    }

    private function formatQueryLabelForDisplay($idSite, $apiModule, $apiAction, $label)
    {
        // rows with subtables do not contain URL metadata. this hack makes sure the label titles in row
        // evolution popovers look like URLs.
        if ($apiModule == 'Actions'
            && in_array($apiAction, self::$actionsUrlReports)
        ) {
            $mainUrl = Site::getMainUrlFor($idSite);
            $mainUrlHost = @parse_url($mainUrl, PHP_URL_HOST);

            $replaceRegex = "/\\s*" . preg_quote(LabelFilter::SEPARATOR_RECURSIVE_LABEL) . "\\s*/";
            $cleanLabel = preg_replace($replaceRegex, '/', $label);

            $result = $mainUrlHost . '/' . $cleanLabel . '/';
        } else {
            $result = str_replace(LabelFilter::SEPARATOR_RECURSIVE_LABEL, ' - ', $label);
        }

        // remove @ terminal operator occurrences
        return str_replace(LabelFilter::TERMINAL_OPERATOR, '', $result);
    }

    /**
     * @param Row $row
     * @param string $apiModule
     * @param string $apiAction
     * @param bool $labelUseAbsoluteUrl
     * @return bool|string
     */
    private function getRowUrlForEvolutionLabel($row, $apiModule, $apiAction, $labelUseAbsoluteUrl)
    {
        $url = $row->getMetadata('url');
        if ($url
            && ($apiModule == 'Actions'
                || ($apiModule == 'Referrers'
                    && $apiAction == 'getWebsites'))
            && $labelUseAbsoluteUrl
        ) {
            $actualLabel = preg_replace(';^http(s)?://(www.)?;i', '', $url);
            return $actualLabel;
        }
        return false;
    }

    /**
     * @param array $metadata see getRowEvolutionMetaData()
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string $apiModule
     * @param string $apiAction
     * @param string|bool|array $label
     * @param string|bool $segment
     * @param array $apiParameters
     * @throws Exception
     * @return DataTable\Map|DataTable
     */
    private function loadRowEvolutionDataFromAPI($metadata, $idSite, $period, $date, $apiModule, $apiAction, $label, $segment, $apiParameters, $showGoalMetricsForGoal)
    {
        if (!is_array($label)) {
            $label = [$label];
        }
        $label = array_map('rawurlencode', $label);

        $parameters = [
            'method'                   => $apiModule . '.' . $apiAction,
            'label'                    => $label,
            'idSite'                   => $idSite,
            'period'                   => $period,
            'date'                     => $date,
            'format'                   => 'original',
            'serialize'                => '0',
            'segment'                  => $segment,
            // data for row evolution should NOT be limited
            'filter_limit'             => -1,

            // if more than one label is used, we add metadata to ensure we know which
            // row corresponds with which label (since the labels can change, and rows
            // can be sorted in a different order)
            'labelFilterAddLabelIndex' => count($label) > 1 ? 1 : 0,
        ];

        if ($showGoalMetricsForGoal !== false) {
            $parameters['filter_show_goal_columns_process_goals'] = implode(',', $this->getGoalsToProcess($showGoalMetricsForGoal, $idSite));
            $parameters['filter_update_columns_when_show_all_goals'] = 1;
            $parameters['idGoal'] = $showGoalMetricsForGoal;
        }

        if (!empty($apiParameters) && is_array($apiParameters)) {
            foreach ($apiParameters as $param => $value) {
                $parameters[$param] = $value;
            }
        }

        // add "processed metrics" like actions per visit or bounce rate
        // note: some reports should not be filtered with AddColumnProcessedMetrics
        // specifically, reports without the Metrics::INDEX_NB_VISITS metric such as Goals.getVisitsUntilConversion & Goal.getDaysToConversion
        // this is because the AddColumnProcessedMetrics filter removes all datable rows lacking this metric
        if (isset($metadata['metrics']['nb_visits'])) {
            $parameters['filter_add_columns_when_show_all_columns'] = '0';
        }

        $url = Url::getQueryStringFromParameters($parameters);

        $request = new Request($url);

        try {
            $dataTable = $request->process();
        } catch (Exception $e) {
            throw new Exception("API returned an error: " . $e->getMessage() . "\n");
        }

        return $dataTable;
    }

    /**
     * For a given API report, returns a simpler version
     * of the metadata (will return only the metrics and the dimension name)
     * @param $idSite
     * @param $period
     * @param $date
     * @param $apiModule
     * @param $apiAction
     * @param $language
     * @param $apiParameters
     * @throws Exception
     * @return array
     */
    private function getRowEvolutionMetaData($idSite, $period, $date, $apiModule, $apiAction, $language, $apiParameters)
    {
        $reportMetadata = API::getInstance()->getMetadata($idSite, $apiModule, $apiAction, $apiParameters, $language,
            $period, $date, $hideMetricsDoc = false, $showSubtableReports = true);

        if (empty($reportMetadata)) {
            throw new Exception("Requested report $apiModule.$apiAction for Website id=$idSite "
                . "not found in the list of available reports. \n");
        }

        $reportMetadata = reset($reportMetadata);

        $metrics = (isset($reportMetadata['metrics']) && is_array($reportMetadata['metrics']) ? $reportMetadata['metrics'] : []);
        if (isset($reportMetadata['processedMetrics']) && is_array($reportMetadata['processedMetrics'])) {
            $metrics = $metrics + $reportMetadata['processedMetrics'];
        }

        if (empty($reportMetadata['dimension'])) {
            throw new Exception(sprintf('Reports like %s.%s which do not have a dimension are not supported by row evolution', $apiModule, $apiAction));
        }

        $dimension = $reportMetadata['dimension'];

        return compact('metrics', 'dimension');
    }

    /**
     * Given the Row evolution dataTable, and the associated metadata,
     * enriches the metadata with min/max values, and % change between the first period and the last one
     * @param array $metadata
     * @param DataTable\Map $dataTable
     */
    private function enhanceRowEvolutionMetaData(&$metadata, $dataTable)
    {
        // prepare result array for metrics
        $metricsResult = [];
        foreach ($metadata['metrics'] as $metric => $name) {
            $metricsResult[$metric] = ['name' => $name];

            if (!empty($metadata['logos'][$metric])) {
                $metricsResult[$metric]['logo'] = $metadata['logos'][$metric];
            }
        }
        unset($metadata['logos']);

        $subDataTables = $dataTable->getDataTables();
        if (empty($subDataTables)) {
            throw new \Exception("Unexpected state: row evolution API call returned empty DataTable\\Map.");
        }

        $firstDataTable = reset($subDataTables);
        $this->checkDataTableInstance($firstDataTable);
        $firstDataTableRow = $firstDataTable->getFirstRow();

        $lastDataTable = end($subDataTables);
        $this->checkDataTableInstance($lastDataTable);
        $lastDataTableRow = $lastDataTable->getFirstRow();

        // Process min/max values
        $firstNonZeroFound = [];
        foreach ($subDataTables as $subDataTable) {
            // $subDataTable is the report for one period, it has only one row
            $firstRow = $subDataTable->getFirstRow();
            foreach ($metadata['metrics'] as $metric => $label) {
                $value = $firstRow ? floatval($firstRow->getColumn($metric)) : 0;
                if ($value > 0) {
                    $firstNonZeroFound[$metric] = true;
                } else if (!isset($firstNonZeroFound[$metric])) {
                    continue;
                }
                if (!isset($metricsResult[$metric]['min'])
                    || $metricsResult[$metric]['min'] > $value
                ) {
                    $metricsResult[$metric]['min'] = $value;
                }
                if (!isset($metricsResult[$metric]['max'])
                    || $metricsResult[$metric]['max'] < $value
                ) {
                    $metricsResult[$metric]['max'] = $value;
                }
            }
        }

        // Process % change between first/last values
        foreach ($metadata['metrics'] as $metric => $label) {
            $first = $firstDataTableRow ? floatval($firstDataTableRow->getColumn($metric)) : 0;
            $last = $lastDataTableRow ? floatval($lastDataTableRow->getColumn($metric)) : 0;

            // do not calculate evolution if the first value is 0 (to avoid divide-by-zero)
            if ($first == 0) {
                continue;
            }

            $change = CalculateEvolutionFilter::calculate($last, $first, $quotientPrecision = 0, true, true);

            $metricsResult[$metric]['change'] = $change;
        }

        $metadata['metrics'] = $metricsResult;
    }

    /** Get row evolution for a multiple labels */
    private function getMultiRowEvolution(DataTable\Map $dataTable, $metadata, $apiModule, $apiAction, $labels, $column,
                                          $legendAppendMetric = true,
                                          $labelUseAbsoluteUrl = true,
                                          $labelSeries = '')
    {
        $labelSeries = explode(',', $labelSeries);
        $labelSeries = array_filter($labelSeries, 'strlen');
        $labelSeries = array_map('intval', $labelSeries);

        if (!isset($metadata['metrics'][$column])) {
            // invalid column => use the first one that's available
            $metrics = array_keys($metadata['metrics']);
            $column = reset($metrics);
        }

        $labelPretty = Common::getRequestVar('labelPretty', '', 'string');
        $labelPretty = Piwik::getArrayFromApiParameter($labelPretty);

        // get the processed label and logo (if any) for every requested label
        $actualLabels = $logos = [];
        foreach ($labels as $labelIdx => $label) {
            foreach ($dataTable->getDataTables() as $table) {
                $labelRow = $this->getRowEvolutionRowFromLabelIdx($table, $labelIdx);

                if ($labelRow) {
                    $actualLabels[$labelIdx] = $this->getRowUrlForEvolutionLabel(
                        $labelRow, $apiModule, $apiAction, $labelUseAbsoluteUrl);

                    $prettyLabel = $labelRow->getColumn('label_html');
                    if ($prettyLabel !== false) {
                        $actualLabels[$labelIdx] = $prettyLabel;
                    } else if (!empty($labelPretty[$labelIdx])) {
                        $actualLabels[$labelIdx] = $labelPretty[$labelIdx];
                    }

                    $logos[$labelIdx] = $labelRow->getMetadata('logo');

                    if (!empty($actualLabels[$labelIdx])) {
                        break;
                    } else if (!empty($labelPretty[$labelIdx])) {
                        $actualLabels[$labelIdx] = $labelPretty[$labelIdx];
                    }
                }
            }

            if (empty($actualLabels[$labelIdx])) {
                $cleanLabel = $this->cleanOriginalLabel($label);
                $actualLabels[$labelIdx] = $cleanLabel;
            }

            if (isset($labelSeries[$labelIdx])) {
                $labelSeriesIndex = $labelSeries[$labelIdx];
                $actualLabels[$labelIdx] .= ' ' . DataComparisonFilter::getPrettyComparisonLabelFromSeriesIndex($labelSeriesIndex);
            }
        }

        // convert rows to be [$column.'_'.$labelIdx => $value] as opposed to
        // ['label' => $label, 'column' => $value].
        $dataTableMulti = $dataTable->getEmptyClone();
        foreach ($dataTable->getDataTables() as $tableLabel => $table) {
            $newRow = new Row();

            foreach ($labels as $labelIdx => $label) {
                $row = $this->getRowEvolutionRowFromLabelIdx($table, $labelIdx);

                $value = 0;
                if ($row) {
                    $value = $row->getColumn($column);
                    $value = floatVal(str_replace(',', '.', $value));
                }

                if ($value == '') {
                    $value = 0;
                }

                $newLabel = $column . '_' . (int)$labelIdx;
                $newRow->addColumn($newLabel, $value);
            }

            $newTable = $table->getEmptyClone();
            if (!empty($labels)) { // only add a row if the row has data (no labels === no data)
                $newTable->addRow($newRow);
            }

            $dataTableMulti->addTable($newTable, $tableLabel);
        }

        // the available metrics for the report are returned as metadata / columns
        $metadata['columns'] = $metadata['metrics'];

        // metadata / metrics should document the rows that are compared
        // this way, UI code can be reused
        $metadata['metrics'] = [];
        foreach ($actualLabels as $labelIndex => $label) {
            if ($legendAppendMetric) {
                $label .= ' (' . $metadata['columns'][$column] . ')';
            }
            $metricName = $column . '_' . $labelIndex;
            $metadata['metrics'][$metricName] = $label;

            if (!empty($logos[$labelIndex])) {
                $metadata['logos'][$metricName] = $logos[$labelIndex];
            }
        }

        $this->enhanceRowEvolutionMetaData($metadata, $dataTableMulti);

        return [
            'column'     => $column,
            'reportData' => $dataTableMulti,
            'metadata'   => $metadata,
        ];
    }

    /**
     * Returns the row in a datatable by its LabelFilter::FLAG_IS_ROW_EVOLUTION metadata.
     *
     * @param DataTable $table
     * @param int $labelIdx
     * @return Row|false
     */
    private function getRowEvolutionRowFromLabelIdx($table, $labelIdx)
    {
        $labelIdx = (int)$labelIdx;
        foreach ($table->getRows() as $row) {
            if ($row->getMetadata(LabelFilter::FLAG_IS_ROW_EVOLUTION) === $labelIdx) {
                return $row;
            }
        }
        return false;
    }

    /**
     * Returns a prettier, more comprehensible version of a row evolution label for display.
     */
    private function cleanOriginalLabel($label)
    {
        $label = str_replace(LabelFilter::SEPARATOR_RECURSIVE_LABEL, ' - ', $label);
        return SafeDecodeLabel::decodeLabelSafe($label);
    }

    private function checkDataTableInstance($lastDataTable)
    {
        if (!($lastDataTable instanceof DataTable)) {
            throw new \Exception("Unexpected state: row evolution returned DataTable\\Map w/ incorrect child table type: " . get_class($lastDataTable));
        }
    }
}
