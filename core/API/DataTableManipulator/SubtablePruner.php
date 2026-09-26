<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\API\DataTableManipulator;

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
 * Removes the rows a label search doesn't need from a subtable, before it is post-processed. If
 * post-processing could change which row the search finds, the table is left alone.
 *
 * It must match the same rows as LabelFilter::findRowForLabel().
 *
 * @internal
 */
final class SubtablePruner
{
    /**
     * Metrics whose beforeCompute() gives the same result on a pruned table.
     */
    private const BEFORE_COMPUTE_SAFE_ON_PRUNED_TABLE = [
        ProcessedMetric::class,
        AveragePerformanceMetric::class,
        AveragePageGenerationTime::class,
        PercentOfReportTotal::class,
    ];

    /**
     * @var array<string, bool> for each metric class: does it need the whole table?
     */
    private static array $metricNeedsWholeTable = [];

    private string $labelColumn;

    public function __construct(string $labelColumn = 'label')
    {
        $this->labelColumn = $labelColumn;
    }

    /**
     * @param string[] $labelVariations the labels the search tries for this level
     */
    public function prune(
        DataTable $dataTable,
        array $request,
        string $apiModule,
        string $method,
        array $labelVariations,
        bool $isLastLabelPart
    ): DataTable {
        if (
            $this->requestNeedsWholeTable($request)
            || $this->hasQueuedFilterThatMayChangeLabels($dataTable)
            || ($isLastLabelPart && $this->hasProcessedMetricNeedingWholeTable($dataTable, $apiModule, $method))
        ) {
            return $dataTable;
        }

        $row = $this->findOnlyRowForLabel($labelVariations, $dataTable);

        // the summary row can still be labelled -1 here, so compare the object
        if ($row === null || $row === $dataTable->getSummaryRow()) {
            return $dataTable;
        }

        // if we go deeper we only need this row's subtable id
        $dataTable->setRows($isLastLabelPart ? $this->getRowsForWholeTableChecks($row, $dataTable) : [$row]);

        return $dataTable;
    }

    /**
     * Returns the row the search will find, or null if more than one row could match. For example,
     * "a &amp; b" and "a & b" become the same label once decoded.
     *
     * @param string[] $labelVariations
     */
    private function findOnlyRowForLabel(array $labelVariations, DataTable $dataTable): ?Row
    {
        $variations = array_flip($labelVariations);
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
     * Some metrics look at every row: they drop columns no row has, or add a column for each goal.
     * So besides the target row, keep the first row that has each column and each goal, in table
     * order.
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
     * Marks the columns this row has a value for. For array columns like goals, marks the keys.
     *
     * @return bool true if the row added something new
     */
    private function coverColumns(array $columns, array &$covered, array &$coveredKeys): bool
    {
        $coveredNew = false;

        // array_filter() drops empty values, so this stays fast
        foreach (array_filter(array_diff_key($columns, $covered)) as $name => $value) {
            if (is_array($value)) {
                $newKeys = array_diff_key($value, $coveredKeys[$name] ?? []);
                if (!empty($newKeys)) {
                    $coveredKeys[$name] = ($coveredKeys[$name] ?? []) + $newKeys;
                    $coveredNew = true;
                }
            } elseif (!is_numeric($value) || $value != 0) {
                // array_filter() keeps '0.0', so check it here
                $covered[$name] = true;
                $coveredNew = true;
            }
        }

        return $coveredNew;
    }

    /**
     * Whether a filter in the request needs the rows we would drop:
     * - ExcludeLowPopulation can base its threshold on all rows
     * - limit, offset and truncate pick rows by position
     * - a pattern, or removing rows without visits, can remove the rows we kept for the checks
     * - hideColumns and showColumns can remove the label column
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

        // a truncate of 0 still keeps the summary row, and a limit of -1 means no limit
        if (isset($request['filter_truncate']) && (int) $request['filter_truncate'] >= 0) {
            return true;
        }

        if (isset($request['filter_limit']) && (int) $request['filter_limit'] !== -1) {
            return true;
        }

        return $this->deletesLabelColumn($request['hideColumns'] ?? '', $request['showColumns'] ?? '');
    }

    /**
     * Whether a queued filter may change a label. These run before the search, so a changed label can
     * make another row match. For example, Referrers renames an empty keyword to "Keyword not
     * defined", which can also be a real keyword. Callables and unknown filters count as changing
     * labels.
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
            // we keep the summary row, so the search still sees its new name
            'ReplaceSummaryRowLabel', 'PrependSegment' => false,
            default => true,
        };
    }

    /**
     * Whether ColumnDelete removes the column we find rows by. It always keeps "label", but no other
     * column.
     */
    private function deletesLabelColumn(mixed $columnsToRemove, mixed $columnsToKeep): bool
    {
        $columnsToKeep = $this->toColumnList($columnsToKeep);

        return in_array($this->labelColumn, $this->toColumnList($columnsToRemove), true)
            || ($columnsToKeep !== [] && $this->labelColumn !== 'label' && !in_array($this->labelColumn, $columnsToKeep, true));
    }

    /**
     * Reads a column list like ColumnDelete does. A string is split on commas. Anything that isn't a
     * string or an array means no columns.
     */
    private function toColumnList(mixed $columns): array
    {
        if (is_string($columns)) {
            return $columns === '' ? [] : explode(',', $columns);
        }

        return is_array($columns) ? $columns : [];
    }

    /**
     * Whether a ReplaceColumnNames mapping renames the label column or renames another column to it.
     */
    private function isInMapping(mixed $mapping): bool
    {
        return !is_array($mapping)
            || array_key_exists($this->labelColumn, $mapping) || in_array($this->labelColumn, $mapping, true);
    }

    /**
     * Whether a metric's beforeCompute() needs the rows we would drop, for example to sum a column.
     * Metrics added later by the generic filters aren't checked, which is fine as long as they don't
     * override beforeCompute().
     */
    private function hasProcessedMetricNeedingWholeTable(DataTable $dataTable, string $apiModule, string $method): bool
    {
        // without a module there is no report to look up
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

            if (
                ($metric instanceof AveragePageGenerationTime || $metric instanceof AveragePerformanceMetric)
                && !$this->rowsAgreeOnAverageColumns($dataTable, $metric->getName())
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * The average time metrics read their columns from the top row after sorting, and pruning can
     * change which row is on top. This only matters when rows differ in which of these columns they
     * have.
     */
    private function rowsAgreeOnAverageColumns(DataTable $dataTable, string $averageColumn): bool
    {
        // avg_time_network goes with sum_time_network
        $sumColumn = 'sum_' . substr($averageColumn, strlen('avg_'));
        $expected = null;

        foreach ($dataTable->getRowsWithoutSummaryRow() as $row) {
            $columns = [$row->hasColumn($sumColumn), $row->getColumn($averageColumn) !== false];

            if ($expected === null) {
                $expected = $columns;
            } elseif ($columns !== $expected) {
                return false;
            }
        }

        return true;
    }
}
