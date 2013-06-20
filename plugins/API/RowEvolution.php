<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_API
 */

/**
 * This class generates a Row evolution dataset, from input request
 *
 * @package Piwik_API
 */
class Piwik_API_RowEvolution
{

    public function getRowEvolution($idSite, $period, $date, $apiModule, $apiAction, $label = false, $segment = false, $column = false, $language = false, $idGoal = false, $legendAppendMetric = true, $labelUseAbsoluteUrl = true)
    {
        // validation of requested $period & $date
        if ($period == 'range') {
            // load days in the range
            $period = 'day';
        }

        if (!Piwik_Period::isMultiplePeriod($date, $period)) {
            throw new Exception("Row evolutions can not be processed with this combination of \'date\' and \'period\' parameters.");
        }

        $label = Piwik_API_ResponseBuilder::unsanitizeLabelParameter($label);
        if ($label) {
            $labels = explode(',', $label);
            $labels = array_unique($labels);
        } else {
            $labels = array();
        }

        $dataTable = $this->loadRowEvolutionDataFromAPI($idSite, $period, $date, $apiModule, $apiAction, $labels, $segment, $idGoal);

        if (empty($labels)) {
            // if no labels specified, use all possible labels as list
            foreach ($dataTable->getArray() as $table) {
                $labels = array_merge($labels, $table->getColumn('label'));
            }
            $labels = array_values(array_unique($labels));

            // if the filter_limit query param is set, treat it as a request to limit
            // the number of labels used
            $limit = Piwik_Common::getRequestVar('filter_limit', false);
            if ($limit != false
                && $limit >= 0
            ) {
                $labels = array_slice($labels, 0, $limit);
            }

            // set label index metadata
            $labelsToIndex = array_flip($labels);
            foreach ($dataTable->getArray() as $table) {
                foreach ($table->getRows() as $row) {
                    $label = $row->getColumn('label');
                    if (isset($labelsToIndex[$label])) {
                        $row->setMetadata('label_index', $labelsToIndex[$label]);
                    }
                }
            }
        }

        if (count($labels) != 1) {
            $data = $this->getMultiRowEvolution(
                $dataTable,
                $idSite,
                $period,
                $date,
                $apiModule,
                $apiAction,
                $labels,
                $segment,
                $column,
                $language,
                $idGoal,
                $legendAppendMetric,
                $labelUseAbsoluteUrl
            );
        } else {
            $data = $this->getSingleRowEvolution(
                $dataTable,
                $idSite,
                $period,
                $date,
                $apiModule,
                $apiAction,
                $labels[0],
                $segment,
                $language,
                $idGoal,
                $labelUseAbsoluteUrl
            );
        }
        return $data;
    }

    /**
     * Get row evolution for a single label
     * @return array containing  report data, metadata, label, logo
     */
    private function getSingleRowEvolution($dataTable, $idSite, $period, $date, $apiModule, $apiAction, $label, $segment, $language = false, $idGoal = false, $labelUseAbsoluteUrl = true)
    {
        $metadata = $this->getRowEvolutionMetaData($idSite, $period, $date, $apiModule, $apiAction, $language, $idGoal);
        $metricNames = array_keys($metadata['metrics']);

        $logo = $actualLabel = false;
        $urlFound = false;
        foreach ($dataTable->getArray() as $date => $subTable) {
            /** @var $subTable Piwik_DataTable */
            $subTable->applyQueuedFilters();
            if ($subTable->getRowsCount() > 0) {
                /** @var $row Piwik_DataTable_Row */
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
                    if (!in_array($column, $metricNames)) {
                        $row->deleteColumn($column);
                    }
                }

                $row->deleteMetadata();
            }
        }

        $this->enhanceRowEvolutionMetaData($metadata, $dataTable);

        // if we have a recursive label and no url, use the path
        if (!$urlFound) {
            $actualLabel = str_replace(Piwik_API_DataTableManipulator_LabelFilter::SEPARATOR_RECURSIVE_LABEL, ' - ', $label);
        }

        $return = array(
            'label'      => Piwik_DataTable_Filter_SafeDecodeLabel::safeDecodeLabel($actualLabel),
            'reportData' => $dataTable,
            'metadata'   => $metadata
        );
        if (!empty($logo)) {
            $return['logo'] = $logo;
        }
        return $return;
    }

    private function getRowUrlForEvolutionLabel($row, $apiModule, $apiAction, $labelUseAbsoluteUrl)
    {
        $url = $row->getMetadata('url');
        if ($url
            && ($apiModule == 'Actions'
                || ($apiModule == 'Referers'
                    && $apiAction == 'getWebsites'))
            && $labelUseAbsoluteUrl
        ) {
            $actualLabel = preg_replace(';^http(s)?://(www.)?;i', '', $url);
            return $actualLabel;
        }
        return false;
    }

    /**
     * @param $idSite
     * @param $period
     * @param $date
     * @param $apiModule
     * @param $apiAction
     * @param $label
     * @param $segment
     * @param $idGoal
     * @throws Exception
     * @return Piwik_DataTable_Array|Piwik_DataTable
     */
    private function loadRowEvolutionDataFromAPI($idSite, $period, $date, $apiModule, $apiAction, $label = false, $segment = false, $idGoal = false)
    {
        if (!is_array($label)) {
            $label = array($label);
        }
        $label = array_map('rawurlencode', $label);

        $parameters = array(
            'method'                   => $apiModule . '.' . $apiAction,
            'label'                    => $label,
            'idSite'                   => $idSite,
            'period'                   => $period,
            'date'                     => $date,
            'format'                   => 'original',
            'serialize'                => '0',
            'segment'                  => $segment,
            'idGoal'                   => $idGoal,

            // data for row evolution should NOT be limited
            'filter_limit'             => -1,

            // if more than one label is used, we add metadata to ensure we know which
            // row corresponds with which label (since the labels can change, and rows
            // can be sorted in a different order)
            'labelFilterAddLabelIndex' => count($label) > 1 ? 1 : 0,
        );

        // add "processed metrics" like actions per visit or bounce rate
        // note: some reports should not be filtered with AddColumnProcessedMetrics
        // specifically, reports without the Piwik_Metrics::INDEX_NB_VISITS metric such as Goals.getVisitsUntilConversion & Goal.getDaysToConversion
        // this is because the AddColumnProcessedMetrics filter removes all datable rows lacking this metric
        if
        (
            $apiModule != 'Actions'
            &&
            ($apiModule != 'Goals' || ($apiAction != 'getVisitsUntilConversion' && $apiAction != 'getDaysToConversion'))
            && !empty($label)
        ) {
            $parameters['filter_add_columns_when_show_all_columns'] = '1';
        }

        $url = Piwik_Url::getQueryStringFromParameters($parameters);

        $request = new Piwik_API_Request($url);

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
     * @param $idGoal
     * @throws Exception
     * @return array
     */
    private function getRowEvolutionMetaData($idSite, $period, $date, $apiModule, $apiAction, $language, $idGoal = false)
    {
        $apiParameters = array();
        if (!empty($idGoal) && $idGoal > 0) {
            $apiParameters = array('idGoal' => $idGoal);
        }
        $reportMetadata = $this->getMetadata($idSite, $apiModule, $apiAction, $apiParameters, $language, $period, $date, $hideMetricsDoc = false, $showSubtableReports = true);

        if (empty($reportMetadata)) {
            throw new Exception("Requested report $apiModule.$apiAction for Website id=$idSite "
                . "not found in the list of available reports. \n");
        }

        $reportMetadata = reset($reportMetadata);

        $metrics = $reportMetadata['metrics'];
        if (isset($reportMetadata['processedMetrics']) && is_array($reportMetadata['processedMetrics'])) {
            $metrics = $metrics + $reportMetadata['processedMetrics'];
        }

        $dimension = $reportMetadata['dimension'];

        return compact('metrics', 'dimension');
    }

    /**
     * Given the Row evolution dataTable, and the associated metadata,
     * enriches the metadata with min/max values, and % change between the first period and the last one
     * @param array $metadata
     * @param Piwik_DataTable_Array $dataTable
     */
    private function enhanceRowEvolutionMetaData(&$metadata, $dataTable)
    {
        // prepare result array for metrics
        $metricsResult = array();
        foreach ($metadata['metrics'] as $metric => $name) {
            $metricsResult[$metric] = array('name' => $name);

            if (!empty($metadata['logos'][$metric])) {
                $metricsResult[$metric]['logo'] = $metadata['logos'][$metric];
            }
        }
        unset($metadata['logos']);

        $subDataTables = $dataTable->getArray();
        $firstDataTable = reset($subDataTables);
        $firstDataTableRow = $firstDataTable->getFirstRow();
        $lastDataTable = end($subDataTables);
        $lastDataTableRow = $lastDataTable->getFirstRow();

        // Process min/max values
        $firstNonZeroFound = array();
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

            $change = Piwik_DataTable_Filter_CalculateEvolutionFilter::calculate($last, $first, $quotientPrecision = 0);
            $change = Piwik_DataTable_Filter_CalculateEvolutionFilter::prependPlusSignToNumber($change);
            $metricsResult[$metric]['change'] = $change;
        }

        $metadata['metrics'] = $metricsResult;
    }

    /** Get row evolution for a multiple labels */
    private function getMultiRowEvolution($dataTable, $idSite, $period, $date, $apiModule, $apiAction, $labels, $segment, $column, $language = false, $idGoal = false, $legendAppendMetric = true, $labelUseAbsoluteUrl = true)
    {
        $actualLabels = $logos = array();

        $metadata = $this->getRowEvolutionMetaData($idSite, $period, $date, $apiModule, $apiAction, $language, $idGoal);

        if (!isset($metadata['metrics'][$column])) {
            // invalid column => use the first one that's available
            $metrics = array_keys($metadata['metrics']);
            $column = reset($metrics);
        }

        // get the processed label and logo (if any) for every requested label
        $actualLabels = $logos = array();
        foreach ($labels as $labelIdx => $label) {
            foreach ($dataTable->getArray() as $table) {
                $labelRow = $this->getRowEvolutionRowFromLabelIdx($table, $labelIdx);

                if ($labelRow) {
                    $actualLabels[$labelIdx] = $this->getRowUrlForEvolutionLabel(
                        $labelRow, $apiModule, $apiAction, $labelUseAbsoluteUrl);

                    $logos[$labelIdx] = $labelRow->getMetadata('logo');

                    if (!empty($actualLabels[$labelIdx])) {
                        break;
                    }
                }
            }

            if (empty($actualLabels[$labelIdx])) {
                $actualLabels[$labelIdx] = $this->cleanOriginalLabel($label);
            }
        }

        // convert rows to be array($column.'_'.$labelIdx => $value) as opposed to
        // array('label' => $label, 'column' => $value).
        $dataTableMulti = $dataTable->getEmptyClone();
        foreach ($dataTable->getArray() as $tableLabel => $table) {
            $newRow = new Piwik_DataTable_Row();

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
        $metadata['metrics'] = array();
        foreach ($actualLabels as $labelIndex => $label) {
            if ($legendAppendMetric) {
                $label .= ' (' . $metadata['columns'][$column] . ')';
            }
            $metricName = $column . '_' . $labelIndex;
            $metadata['metrics'][$metricName] = Piwik_DataTable_Filter_SafeDecodeLabel::safeDecodeLabel($label);

            if (!empty($logos[$labelIndex])) {
                $metadata['logos'][$metricName] = $logos[$labelIndex];
            }
        }

        $this->enhanceRowEvolutionMetaData($metadata, $dataTableMulti);

        return array(
            'column'     => $column,
            'reportData' => $dataTableMulti,
            'metadata'   => $metadata
        );
    }

    /**
     * Returns the row in a datatable by its label_index metadata.
     *
     * @param Piwik_DataTable $table
     * @param int $labelIdx
     * @return Piwik_DataTable_Row|false
     */
    private function getRowEvolutionRowFromLabelIdx($table, $labelIdx)
    {
        $labelIdx = (int)$labelIdx;
        foreach ($table->getRows() as $row)
        {
            if ($row->getMetadata('label_index') === $labelIdx)
            {
                return $row;
            }
        }
        return false;
    }


    /**
     * Returns a prettier, more comprehensible version of a row evolution label
     * for display.
     */
    private function cleanOriginalLabel($label)
    {
        return str_replace(Piwik_API_DataTableManipulator_LabelFilter::SEPARATOR_RECURSIVE_LABEL, ' - ', $label);
    }
}
