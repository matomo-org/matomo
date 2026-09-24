<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\API\DataTableManipulator;

use Piwik\API\DataTableManipulator;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Filter\SafeDecodeLabel;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\Actions\Columns\Metrics\AveragePageGenerationTime;
use Piwik\Plugins\CoreHome\Columns\Metrics\PercentOfReportTotal;
use Piwik\Plugins\PagePerformance\Columns\Metrics\AveragePerformanceMetric;

/**
 * This class is responsible for handling the label parameter that can be
 * added to every API call. If the parameter is set, only the row with the matching
 * label is returned.
 *
 * The labels passed to this class should be urlencoded.
 * Some reports use recursive labels (e.g. action reports). Use > to join them.
 */
class LabelFilter extends DataTableManipulator
{
    public const SEPARATOR_RECURSIVE_LABEL = '>';
    public const TERMINAL_OPERATOR = '@';
    public const FLAG_IS_ROW_EVOLUTION = 'label_index';

    /**
     * Processed metrics whose beforeCompute() only checks that some row has a value for a column,
     * which the rows kept by getRowsForWholeTableChecks() preserve. PercentOfReportTotal reads the
     * totals instead of the rows.
     */
    private const BEFORE_COMPUTE_SAFE_ON_PRUNED_TABLE = [
        ProcessedMetric::class,
        AveragePerformanceMetric::class,
        AveragePageGenerationTime::class,
        PercentOfReportTotal::class,
    ];

    /**
     * @var array<string, bool> whether a processed metric needs the whole table, by class name
     */
    private static array $metricNeedsWholeTable = [];

    private $labels;
    private $addLabelIndex;
    private $isComparing;
    private $labelSeries;

    private string $labelColumn;

    /**
     * The label the next loaded subtable is searched for, if any.
     */
    private ?string $nextLabelPart = null;

    /**
     * Whether that is the last label part, so the row found is the one the search returns.
     */
    private bool $nextLabelPartIsLast = false;

    public function __construct($apiModule = false, $apiMethod = false, $request = array(), string $labelColumn = 'label')
    {
        parent::__construct($apiModule, $apiMethod, $request);

        $this->labelColumn = $labelColumn;
    }

    /**
     * Filter a data table by label.
     * The filtered table is returned, which might be a new instance.
     *
     * $apiModule, $apiMethod and $request are needed load sub-datatables
     * for the recursive search. If the label is not recursive, these parameters
     * are not needed.
     *
     * @param string|array $labels the label(s) to search for
     * @param DataTable $dataTable the data table to be filtered
     * @param bool $addLabelIndex Whether to add label_index metadata describing which
     *                            label a row corresponds to.
     * @return DataTable\Map|DataTable
     */
    public function filter($labels, $dataTable, $addLabelIndex = false)
    {
        if (!is_array($labels)) {
            $labels = array($labels);
        }

        $this->labels = array_values($labels);
        $this->addLabelIndex = (bool)$addLabelIndex;
        $this->isComparing = $this->isComparing();

        $labelSeries = Common::getRequestVar('labelSeries', '', 'string', $this->request);
        $labelSeries = explode(',', $labelSeries);
        $labelSeries = array_filter($labelSeries, 'strlen');
        $this->labelSeries = $labelSeries;

        $result = $this->manipulate($dataTable);

        return $result;
    }

    /**
     * Method for the recursive descend
     *
     * @param array $labelParts
     * @param DataTable $dataTable
     * @return DataTable\Row|false
     */
    private function doFilterRecursiveDescend($labelParts, $dataTable)
    {
        $labelColumn = $this->labelColumn;

        // search for the first part of the tree search
        $labelPart = array_shift($labelParts);

        $row = $this->findRowForLabel($labelColumn, $labelPart, $dataTable);

        if ($row === false) {
            // not found
            return false;
        }

        // end of tree search reached
        if (count($labelParts) == 0) {
            return $row;
        }

        $this->nextLabelPart = $labelParts[0];
        $this->nextLabelPartIsLast = count($labelParts) === 1;

        try {
            $subTable = $this->loadSubtable($dataTable, $row);
        } finally {
            $this->nextLabelPart = null;
            $this->nextLabelPartIsLast = false;
        }

        if ($subTable === null) {
            // no more subtables but label parts left => no match found
            return false;
        }

        return $this->doFilterRecursiveDescend($labelParts, $subTable);
    }

    /**
     * Drops the rows we don't look for before the subtable is post-processed, which is where the time
     * goes on large subtables. When post-processing could make the search pick another row, the
     * whole table is kept.
     *
     * @param mixed $dataTable
     * @return mixed
     */
    protected function pruneLoadedSubtable($dataTable, array $request, string $apiModule, string $method)
    {
        if (
            $this->nextLabelPart === null
            || !$dataTable instanceof DataTable
            || $this->requestNeedsWholeTable($request)
            || $this->hasQueuedFilterThatMayChangeLabels($dataTable)
            || ($this->nextLabelPartIsLast && $this->hasProcessedMetricNeedingWholeTable($dataTable, $apiModule, $method))
        ) {
            return $dataTable;
        }

        $row = $this->findOnlyRowForLabel($this->nextLabelPart, $dataTable);

        // the summary row is still labelled -1 here and only gets its real label later
        if ($row === null || $row === $dataTable->getSummaryRow()) {
            return $dataTable;
        }

        // a row we only descend through is needed for its subtable id alone
        $dataTable->setRows($this->nextLabelPartIsLast ? $this->getRowsForWholeTableChecks($row, $dataTable) : [$row]);

        return $dataTable;
    }

    /**
     * Finds the row the search will return, or null if more than one row may be it. Post-processing
     * decodes the labels, so "a &amp; b" and "a & b" end up the same, and only the whole table can
     * tell which of them the search picks.
     */
    private function findOnlyRowForLabel(string $labelPart, DataTable $dataTable): ?Row
    {
        $variations = array_flip($this->getLabelVariations($labelPart));
        $match = null;

        foreach ($dataTable->getRows() as $row) {
            $label = (string) ($row->getColumn($this->labelColumn) ?: $row->getMetadata($this->labelColumn));

            if (isset($variations[$label]) || isset($variations[SafeDecodeLabel::decodeLabelSafe($label)])) {
                if ($match !== null) {
                    return null;
                }

                $match = $row;
            }
        }

        return $match;
    }

    /**
     * Some post-processing checks every row first: the page performance and generation time metrics
     * drop columns no row has a value for, and goal columns are added for every goal some row has.
     * So next to the target, keep the first row with a value for each column and each goal, in table
     * order as goal columns follow it. The search only returns the target.
     *
     * @return Row[]
     */
    private function getRowsForWholeTableChecks(Row $target, DataTable $dataTable): array
    {
        $covered = [];
        $coveredKeys = [];
        $rows = [];

        foreach ($dataTable->getRowsWithoutSummaryRow() as $row) {
            if ($this->coverColumns($row->getColumns(), $covered, $coveredKeys) || $row === $target) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Marks the columns of a row that have a value and aren't covered yet. For an array column, like
     * goals, its keys get covered.
     *
     * @return bool whether the row covered anything new
     */
    private function coverColumns(array $columns, array &$covered, array &$coveredKeys): bool
    {
        $coveredNew = false;

        // array_filter() drops the empty values, so a row with nothing new stays cheap
        foreach (array_filter(array_diff_key($columns, $covered)) as $name => $value) {
            if (is_array($value)) {
                $newKeys = array_diff_key($value, $coveredKeys[$name] ?? []);
                if (!empty($newKeys)) {
                    $coveredKeys[$name] = ($coveredKeys[$name] ?? []) + $newKeys;
                    $coveredNew = true;
                }
            } elseif (!is_numeric($value) || $value != 0) {
                // array_filter() keeps a zero string such as '0.0'
                $covered[$name] = true;
                $coveredNew = true;
            }
        }

        return $coveredNew;
    }

    /**
     * Whether a filter the request asks for needs the rows we would drop:
     * - ExcludeLowPopulation can take its threshold from the whole table
     * - limit, offset and truncate keep rows by position
     * - a pattern, or AddColumnsProcessedMetrics deleting the rows without visits, can remove the
     *   rows kept by getRowsForWholeTableChecks()
     * - hideColumns and showColumns can delete the column the rows are identified by
     */
    private function requestNeedsWholeTable(array $request): bool
    {
        if (
            !empty($request['filter_excludelowpop'])
            || !empty($request['filter_add_columns_when_show_all_columns'])
            || !empty($request['filter_offset'])
        ) {
            return true;
        }

        // "0" is a pattern too
        if (isset($request['filter_pattern']) && (string) $request['filter_pattern'] !== '') {
            return true;
        }

        // a truncate of 0 still keeps the summary row alone, and a limit of -1 is no limit
        if (isset($request['filter_truncate']) && (int) $request['filter_truncate'] >= 0) {
            return true;
        }

        if (isset($request['filter_limit']) && (int) $request['filter_limit'] !== -1) {
            return true;
        }

        return $this->deletesLabelColumn($request['hideColumns'] ?? '', $request['showColumns'] ?? '');
    }

    /**
     * Whether a queued filter may change a label. Queued filters run before the search, so a renamed
     * label can make another row match. Referrers, for example, renames the empty keyword to "Keyword
     * not defined", which can also be a real keyword. Callables and unknown filters count as
     * changing labels.
     */
    private function hasQueuedFilterThatMayChangeLabels(DataTable $dataTable): bool
    {
        foreach ($dataTable->getQueuedFilters() as $filter) {
            if (!is_string($filter['className']) || $this->queuedFilterMayChangeLabels($filter['className'], $filter['parameters'])) {
                return true;
            }
        }

        return false;
    }

    private function queuedFilterMayChangeLabels(string $className, array $parameters): bool
    {
        $name = ltrim($className, '\\');
        if (str_starts_with($name, 'Piwik\\DataTable\\Filter\\')) {
            $name = substr($name, strlen('Piwik\\DataTable\\Filter\\'));
        }

        return match ($name) {
            'ColumnDelete' => $this->deletesLabelColumn($parameters[0] ?? [], $parameters[1] ?? []),
            'ReplaceColumnNames' => $this->isInMapping($parameters[0] ?? Metrics::getMappingFromIdToName()),
            'ColumnCallbackAddMetadata', 'MetadataCallbackAddMetadata' => ($parameters[1] ?? null) === $this->labelColumn,
            // the summary row is kept when pruning, so the search still sees it renamed
            'ReplaceSummaryRowLabel', 'PrependSegment' => false,
            default => true,
        };
    }

    /**
     * Whether ColumnDelete with these arguments deletes the column the rows are identified by. The
     * columns to keep always include "label", but no other column.
     *
     * @param mixed $columnsToRemove
     * @param mixed $columnsToKeep
     */
    private function deletesLabelColumn($columnsToRemove, $columnsToKeep): bool
    {
        $columnsToKeep = $this->toColumnList($columnsToKeep);

        return in_array($this->labelColumn, $this->toColumnList($columnsToRemove), true)
            || ($columnsToKeep !== [] && $this->labelColumn !== 'label' && !in_array($this->labelColumn, $columnsToKeep, true));
    }

    /**
     * Reads a column list the way ColumnDelete does: a string is split on commas, and anything but a
     * string or an array is no columns.
     *
     * @param mixed $columns
     */
    private function toColumnList($columns): array
    {
        if (is_string($columns)) {
            return $columns === '' ? [] : explode(',', $columns);
        }

        return is_array($columns) ? $columns : [];
    }

    /**
     * Whether a ReplaceColumnNames mapping renames the label column or renames another column to it.
     *
     * @param mixed $mapping
     */
    private function isInMapping($mapping): bool
    {
        return !is_array($mapping)
            || array_key_exists($this->labelColumn, $mapping) || in_array($this->labelColumn, $mapping, true);
    }

    /**
     * Whether a processed metric's beforeCompute() looks at the rows in a way the rows kept by
     * getRowsForWholeTableChecks() don't preserve, like summing a column over the table. Metrics the
     * generic filters add later aren't seen here, which is fine as long as they don't override
     * beforeCompute().
     */
    private function hasProcessedMetricNeedingWholeTable(DataTable $dataTable, string $apiModule, string $method): bool
    {
        // a manipulator can be built without a module, and then there is no report to look up
        $report = $apiModule === '' ? null : ReportsProvider::factory($apiModule, $method);

        foreach (Report::getProcessedMetricsForTable($dataTable, $report) as $metric) {
            $class = get_class($metric);

            if (!isset(self::$metricNeedsWholeTable[$class])) {
                $declaringClass = (new \ReflectionMethod($metric, 'beforeCompute'))->getDeclaringClass()->getName();
                self::$metricNeedsWholeTable[$class] = !in_array($declaringClass, self::BEFORE_COMPUTE_SAFE_ON_PRUNED_TABLE, true);
            }

            if (self::$metricNeedsWholeTable[$class]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clean up request for ResponseBuilder to behave correctly
     *
     * @param array $request
     * @return array
     */
    protected function manipulateSubtableRequest($request)
    {
        unset($request['label']);
        unset($request['flat']);
        $request['totals'] = 0;
        $request['filter_sort_column'] = ''; // do not sort, we only want to find a matching column

        return $request;
    }

    /**
     * Use variations of the label to make it easier to specify the desired label
     *
     * Note: The HTML Encoded version must be tried first, since in ResponseBuilder the $label is unsanitized
     * via Common::unsanitizeLabelParameter.
     *
     * @param string $originalLabel
     * @return array
     */
    private function getLabelVariations($originalLabel)
    {
        static $pageTitleReports = array('getPageTitles', 'getEntryPageTitles', 'getExitPageTitles');

        $originalLabel = trim($originalLabel);

        $isTerminal = substr($originalLabel, 0, 1) == self::TERMINAL_OPERATOR;
        if ($isTerminal) {
            $originalLabel = substr($originalLabel, 1);
        }

        $variations = array();
        $label = trim(urldecode($originalLabel));

        $sanitizedLabel = Common::sanitizeInputValue($label);
        $variations[] = $sanitizedLabel;

        if (
            $this->apiModule == 'Actions'
            && in_array($this->apiMethod, $pageTitleReports)
        ) {
            if ($isTerminal) {
                array_unshift($variations, ' ' . $sanitizedLabel);
                array_unshift($variations, ' ' . $label);
            } else {
                // special case: the Actions.getPageTitles report prefixes some labels with a blank.
                // the blank might be passed by the user but is removed by the trim above.
                $variations[] = ' ' . $sanitizedLabel;
                $variations[] = ' ' . $label;
            }
        }
        $variations[] = $label;

        $variations = array_unique($variations);

        return $variations;
    }

    /**
     * Filter a DataTable instance. See {@see filter()} for more info.
     *
     * @param DataTable $dataTable
     * @return DataTable
     */
    protected function manipulateDataTable($dataTable)
    {
        $result = $dataTable->getEmptyClone();
        foreach ($this->labels as $labelIndex => $label) {
            $row = null;
            foreach ($this->getLabelVariations($label) as $labelVariation) {
                $labelVariation = explode(self::SEPARATOR_RECURSIVE_LABEL, $labelVariation);

                $row = $this->doFilterRecursiveDescend($labelVariation, $dataTable);
                if ($row) {
                    if (
                        $this->isComparing
                        && isset($this->labelSeries[$labelIndex])
                    ) {
                        $comparisons = $row->getComparisons();
                        if (!empty($comparisons)) {
                            $labelSeriesIndex = $this->labelSeries[$labelIndex];

                            $comparisonRow = $comparisons->getRowFromId($labelSeriesIndex);

                            // labelSeries is supplied by the request, so it does not have to point at an
                            // existing comparison row
                            if ($comparisonRow !== false) {
                                $originalLabel = $row->getColumn($this->labelColumn) ?: $row->getMetadata($this->labelColumn);

                                // both parts are appended after labels are sanitized, so encode them to match.
                                // the label column may be a report specific row identifier, which the label
                                // sanitization does not cover
                                $originalLabel = Common::sanitizeInputValue((string) $originalLabel);
                                $comparisonSuffix = Common::sanitizeInputValue((string) $comparisonRow->getMetadata('compareSeriesPretty'));

                                // add label and make sure it is the first column
                                $columns = array_merge(['label' => $originalLabel . ' ' . $comparisonSuffix], $comparisonRow->getColumns());
                                $comparisonRow->setColumns($columns);

                                $row = $comparisonRow;
                            }
                        }
                    }

                    if ($this->addLabelIndex) {
                        $row->setMetadata(self::FLAG_IS_ROW_EVOLUTION, $labelIndex);
                    }

                    $result->addRow($row);
                    break;
                }
            }
        }
        return $result;
    }

    private function isComparing()
    {
        return Common::getRequestVar('compare', 0, 'int', $this->request) == 1;
    }

    private function findRowForLabel($labelColumn, $labelPart, DataTable $dataTable)
    {
        // we don't use getRowFromLabel() for two reasons: some filters change the label column directly via
        // $row->setColumn('label', '') which would not be noticed in the label index unless we rebuild it,
        // and some reports may specify a different column to use, other than label, to uniquely identify a row.
        $index = [];
        foreach ($dataTable->getRows() as $row) {
            $value = $row->getColumn($labelColumn) ?: $row->getMetadata($labelColumn);
            $index[$value] = $row;
        }

        $variations = $this->getLabelVariations($labelPart);
        foreach ($variations as $variation) {
            if (!empty($index[$variation])) {
                return $index[$variation];
            }
        }

        return false;
    }
}
