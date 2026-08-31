<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\API\Request as ApiRequest;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\DataTable;
use Piwik\NumberFormatter;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\Visualization;
use Piwik\Plugins\CoreVisualizations\Metrics\MetricTotalsTreatment;

/**
 * DataTable visualization that shows DataTable data in an HTML table.
 *
 * @property HtmlTable\Config $config
 */
class HtmlTable extends Visualization
{
    public const ID = 'table';
    public const TEMPLATE_FILE     = "@CoreVisualizations/_dataTableViz_htmlTable.twig";
    public const FOOTER_ICON       = 'icon-table';
    public const FOOTER_ICON_TITLE = 'General_DisplaySimpleTable';

    protected $siteSummary;

    public static function getDefaultConfig()
    {
        return new HtmlTable\Config();
    }

    public static function getDefaultRequestConfig()
    {
        return new HtmlTable\RequestConfig();
    }

    public function beforeLoadDataTable()
    {
        $this->checkRequestIsNotForMultiplePeriods();

        if ($this->isComparing()) {
            $request = $this->getRequestArray();
            if (
                !empty($request['comparePeriods'])
                && count($request['comparePeriods']) == 1
            ) {
                $this->requestConfig->request_parameters_to_modify['invert_compare_change_compute'] = 1;
            }

            // forward the comparisonIdSubtables var if present so it will be used when next/prev links are clicked
            $comparisonIdSubtables = Common::getRequestVar('comparisonIdSubtables', false, 'string');
            if (!empty($comparisonIdSubtables)) {
                $comparisonIdSubtables = Common::unsanitizeInputValue($comparisonIdSubtables);
                $this->config->custom_parameters['comparisonIdSubtables'] = $comparisonIdSubtables;
            }
        }
    }

    public function beforeRender()
    {
        if (
            $this->requestConfig->idSubtable
            && $this->config->show_embedded_subtable
        ) {
            $this->config->show_visualization_only = true;
        }

        if ($this->requestConfig->idSubtable) {
            $this->config->show_totals_row = false;
        }

        foreach (Metrics::getMetricIdsToProcessReportTotal() as $metricId) {
            $this->config->report_ratio_columns[] = Metrics::getReadableColumnName($metricId);
        }
        if (!empty($this->report)) {
            foreach ($this->report->getMetricNamesToProcessReportTotals() as $metricName) {
                $this->config->report_ratio_columns[] = $metricName;
            }
        }

        if ($this->dataTable->getRowsCount()) {
            $this->assignTemplateVar('siteTotalRow', $this->getSiteTotalRow());
        }

        if ($this->isPivoted()) {
            $this->config->columns_to_display = $this->dataTable->getColumns();
        }

        if (
            $this->isComparing()
            && !empty($this->dataTable)
        ) {
            $this->assignTemplateVar('comparisonTotals', $this->dataTable->getMetadata('comparisonTotals'));
        }

        // Note: This needs to be done right before rendering, as otherwise some plugins might change the columns to display again
        if ($this->isFlattened()) {
            $dimensions = $this->dataTable->getMetadata('dimensions');

            $hasMultipleDimensions = is_array($dimensions) && count($dimensions) > 1;
            $this->assignTemplateVar('hasMultipleDimensions', $hasMultipleDimensions);

            if ($hasMultipleDimensions) {
                if ($this->shouldShowDimensions()) {
                    // ensure first metric translation is used as label if other dimensions are in separate columns
                    $this->config->addTranslation('label', $this->config->translations[reset($dimensions)]);
                } else {
                    // concatenate dimensions if table is shown flattened
                    foreach ($dimensions as $dimension) {
                        $labels[] = $this->config->translations[$dimension];
                    }
                    $this->config->addTranslation('label', implode(' - ', $labels));
                }
            }

            if ($this->shouldShowDimensions() && $hasMultipleDimensions) {
                $properties = $this->config;
                array_shift($dimensions); // shift away first dimension, as that will be shown as label

                $this->dataTable->filter(function (DataTable $dataTable) use ($properties, $dimensions) {
                    if (empty($properties->columns_to_display)) {
                        $columns           = $dataTable->getColumns();
                        $hasNbVisits       = in_array('nb_visits', $columns);
                        $hasNbUniqVisitors = in_array('nb_uniq_visitors', $columns);

                        $properties->setDefaultColumnsToDisplay($columns, $hasNbVisits, $hasNbUniqVisitors);
                    }

                    $label = array_search('label', $properties->columns_to_display);
                    if ($label !== false) {
                        unset($properties->columns_to_display[$label]);
                    }

                    foreach (array_reverse($dimensions) as $dimension) {
                        array_unshift($properties->columns_to_display, $dimension);
                    }

                    array_unshift($properties->columns_to_display, 'label');
                });
            }
        }

        $this->assignTemplateVar('segmentTitlePretty', $this->dataTable->getMetadata('segmentPretty'));

        $period = $this->dataTable->getMetadata('period');
        $this->assignTemplateVar('periodTitlePretty', $period ? $period->getLocalizedShortString() : '');

        $this->assignFilteredTotalsRowVars();

        // a subtable a recursive search embedded has no totals of its own, so the report totals stay
        // available to the rows of every hierarchy level the table renders
        $this->assignTemplateVar('reportTotals', $this->dataTable->getMetadata('totals'));

        // Note: This needs to be done last, as it depends on the final columns to display
        $this->config->report_supports_percentage_values = $this->supportsPercentageValues();
    }

    /**
     * Returns whether at least one displayed column has a meaningful percentage value, using the
     * same eligibility rule as the individual cells (see _dataTableViz_htmlTable_ratio.twig).
     */
    private function supportsPercentageValues(): bool
    {
        $totals = $this->dataTable ? $this->dataTable->getMetadata('totals') : null;

        if (empty($totals) || empty($this->config->columns_to_display)) {
            return false;
        }

        $ratioColumns = array_intersect($this->config->report_ratio_columns, array_keys($totals));

        return !empty(array_intersect($this->config->columns_to_display, $ratioColumns));
    }

    /**
     * Makes the report totals available next to a totals row that only totals the rows matching the
     * table search, so both values can be shown, and adds the note explaining what the search did
     * and did not recalculate.
     */
    private function assignFilteredTotalsRowVars(): void
    {
        if (
            !$this->config->show_totals_row
            || !$this->dataTable->getRowsCount()
            || !$this->dataTable->getTotalsRow()
            || true !== $this->dataTable->getMetadata(DataTable::TOTALS_ROW_IS_FILTERED_METADATA_NAME)
        ) {
            return;
        }

        $this->assignTemplateVar('isFilteredTotalsRow', true);
        $this->assignTemplateVar('filteredTotalsRowContext', $this->getFilteredTotalsRowContext($this->report));

        $note = Piwik::translate('General_FilteredTotalsNote', Piwik::translate('General_FilteredTotal'));

        $this->config->show_footer_message = empty($this->config->show_footer_message)
            ? $note
            : $this->config->show_footer_message . '<br />' . $note;
    }

    /**
     * Returns the report total of each displayed metric, together with how that total relates to the
     * total of the rows matching the table search.
     *
     * @param Report|null $report The report of the table, which is not set for every visualization.
     * @return array<string, array{treatment: string, reportTotal: mixed}>
     */
    private function getFilteredTotalsRowContext(?Report $report): array
    {
        $reportTotals = $this->dataTable->getMetadata('totals');
        if (!is_array($reportTotals)) {
            return array();
        }

        $semanticTypes = $report ? $report->getMetricSemanticTypes() : array();
        $processedMetricNames = array_keys(Report::getProcessedMetricsForTable($this->dataTable, $report));
        $aggregationOps = $this->getAggregationOpsByMetricName();

        $context = array();
        foreach ($this->config->columns_to_display as $column) {
            if (
                'label' === $column
                || !array_key_exists($column, $reportTotals)
            ) {
                continue;
            }

            $context[$column] = array(
                'treatment' => MetricTotalsTreatment::getTreatment($column, $semanticTypes, $processedMetricNames, $aggregationOps),
                'reportTotal' => $reportTotals[$column],
            );
        }

        return $context;
    }

    /**
     * Returns the aggregation operations of the table indexed by metric name, as they can still be
     * indexed by metric ID at this point.
     *
     * @return array<string, string|callable>
     */
    private function getAggregationOpsByMetricName(): array
    {
        $aggregationOps = $this->dataTable->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);
        if (!is_array($aggregationOps)) {
            return array();
        }

        $result = array();
        foreach ($aggregationOps as $column => $operation) {
            $result[Metrics::getReadableColumnName($column)] = $operation;
        }

        return $result;
    }

    public function beforeGenericFiltersAreAppliedToLoadedDataTable()
    {
        if ($this->isPivoted()) {
            $this->config->columns_to_display = $this->dataTable->getColumns();

            $this->dataTable->applyQueuedFilters();
        }

        parent::beforeGenericFiltersAreAppliedToLoadedDataTable();

        // Note: This needs to be done right before generic filter are applied, to make sorting such columns possible
        if ($this->isFlattened()) {
            $dimensions = $this->dataTable->getMetadata('dimensions');

            $hasMultipleDimensions = is_array($dimensions) && count($dimensions) > 1;

            if ($hasMultipleDimensions) {
                foreach (Dimension::getAllDimensions() as $dimension) {
                    $dimensionId = str_replace('.', '_', $dimension->getId());
                    $dimensionName = $dimension->getName();

                    if (!empty($dimensionId) && !empty($dimensionName) && in_array($dimensionId, $dimensions)) {
                        $this->config->translations[$dimensionId] = $dimensionName;
                    }
                }
            }

            if ($this->shouldShowDimensions() && $hasMultipleDimensions) {
                # replace original label column with first dimension
                $firstDimension = array_shift($dimensions);
                $this->dataTable->filter('ColumnCallbackAddMetadata', array(
                    'label',
                    'combinedLabel',
                    function ($label) {
                        return $label;
                    },
                ));
                $this->dataTable->filter('ColumnDelete', array('label'));
                $this->dataTable->filter('ReplaceColumnNames', array(array($firstDimension => 'label')));
            }
        }
    }

    public function afterGenericFiltersAreAppliedToLoadedDataTable()
    {
        parent::afterGenericFiltersAreAppliedToLoadedDataTable();

        $this->calculateTotalPercentages(); // this must be done before metrics are formatted
    }

    private function calculateTotalPercentages()
    {
        if (empty($this->report)) {
            return;
        }

        $columnNamesToIndices = Metrics::getMappingFromNameToId();
        $formatter = NumberFormatter::getInstance();

        $totals = $this->dataTable->getMetadata('totalsUnformatted');

        $this->setRowPercentagesRecursively(
            $this->dataTable,
            $this->report->getMetrics(),
            $totals,
            $this->getSiteTotalRow(),
            $columnNamesToIndices,
            $formatter
        );
    }

    /**
     * Sets the percentage metadata of every row of a table, and of the subtables a recursive search
     * embedded into it, so a searched report shows percentages at each of its hierarchy levels.
     *
     * The rows of an embedded subtable relate to the total of the whole report, just like the parent
     * row they belong to, and like the rows of a subtable that is opened by clicking a row.
     *
     * Recurses over getRows() rather than through DataTable::filterSubtables(), which skips the
     * summary row, because the table renders the subtable of a summary row like any other.
     *
     * @param array<string, string> $metrics The metrics of the report, indexed by column name.
     * @param array<string, mixed>|false $totals The unformatted report totals, indexed by column name.
     * @param array<string, int> $columnNamesToIndices The ID of each metric that has one, indexed by column name.
     */
    private function setRowPercentagesRecursively(
        DataTable $table,
        array $metrics,
        $totals,
        ?Row $siteTotalRow,
        array $columnNamesToIndices,
        NumberFormatter $formatter
    ): void {
        foreach ($table->getRows() as $row) {
            foreach ($metrics as $column => $translation) {
                // Try to check the column by it's index (not possible for all metrics, like custom columns)
                $indexColumn = !empty($columnNamesToIndices[$column]) ? $columnNamesToIndices[$column] : null;

                $value = (($indexColumn && $row->getColumn($indexColumn)) ? $row->getColumn($indexColumn) : $row->getColumn($column)) ?: 0;
                if ($column == 'label') {
                    continue;
                }

                $reportTotal = isset($totals[$column]) ? $totals[$column] : 0;

                if (is_numeric($value)) {
                    $percentageColumnName = $column . '_row_percentage';
                    $rowPercentage = $formatter->formatPercent(Piwik::getPercentageSafe($value, $reportTotal, $precision = 1), $precision);
                    $row->setMetadata($percentageColumnName, $rowPercentage);
                }

                if ($siteTotalRow) {
                    $siteTotal = $siteTotalRow->getColumn($column) ?: 0;

                    $siteTotalPercentage = $column . '_site_total_percentage';
                    if ($siteTotal && $siteTotal > $reportTotal) {
                        $rowPercentage = $formatter->formatPercent(Piwik::getPercentageSafe($value, $siteTotal, $precision = 1), $precision);
                        $row->setMetadata($siteTotalPercentage, $rowPercentage);
                    }
                }
            }

            $subtable = $row->getSubtable();
            if ($subtable) {
                $this->setRowPercentagesRecursively($subtable, $metrics, $totals, $siteTotalRow, $columnNamesToIndices, $formatter);
            }
        }
    }

    protected function isPivoted()
    {
        return $this->requestConfig->pivotBy || Common::getRequestVar('pivotBy', '');
    }

    /**
     * Override to compute a custom cell HTML attributes (such as style).
     *
     * @param $column
     * @return array|null Array of name => value pairs, or null if no custom attributes.
     */
    public function getCellHtmlAttributes(Row $row, $column)
    {
        return null;
    }

    public function supportsComparison()
    {
        return true;
    }

    protected function isFlattened()
    {
        return $this->requestConfig->flat || Common::getRequestVar('flat', '');
    }

    protected function shouldShowDimensions()
    {
        return $this->requestConfig->show_dimensions || Common::getRequestVar('show_dimensions', '');
    }

    /**
     * Returns the row holding the totals of the whole site, which not every site summary has.
     */
    private function getSiteTotalRow(): ?Row
    {
        $siteSummary = $this->getSiteSummary();

        return $siteSummary ? ($siteSummary->getFirstRow() ?: null) : null;
    }

    private function getSiteSummary()
    {
        if (empty($this->siteSummary)) {
            // we do not want to get a datatable\map
            $period = Common::getRequestVar('period', 'day', 'string');
            if (Period\Range::parseDateRange($period)) {
                $period = 'range';
            }

            $this->siteSummary = ApiRequest::processRequest('API.get', [
                'module' => 'API',
                'action' => 'get',
                'filter_limit'  => '-1',
                'disable_generic_filters' => 1,
                'filter_offset' => 0,
                'date' => Common::getRequestVar('date', null, 'string'),
                'idSite' => Common::getRequestVar('idSite', null, 'int'),
                'period'        => $period,
                'showColumns'   => implode(',', $this->config->columns_to_display),
                'columns'       => implode(',', $this->config->columns_to_display),
            ], $default = []);
        }

        return $this->siteSummary;
    }
}
