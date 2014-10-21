<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Exception;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;
use Piwik\Log;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\Segment;
use Piwik\Site;

/**
 * DataTable filter that creates a pivot table from a report.
 *
 * A pivot table is a table that displays one metric value for two dimensions. The rows of
 * the table represent one dimension and the columns another.
 *
 * This filter can pivot any report by any dimension as long as either:
 *
 * - the pivot-by dimension is the dimension of the report's subtable
 * - or, the pivot-by dimension has an associated report, and the report to pivot has a dimension with
 *   a segment
 *
 * Reports are pivoted by iterating over the rows of the report, fetching the pivot-by report
 * for the current row, and setting the columns of row to the rows of the pivot-by report. For example:
 *
 * to pivot Referrers.getKeywords by UserCountry.City, we first loop through the Referrers.getKeywords
 * report's rows. For each row, we take the label (which is the referrer keyword), and get the
 * UserCountry.getCity report using the referrerKeyword=... segment. If the row's label were 'abcdefg',
 * we would use the 'referrerKeyword==abcdefg' segment.
 *
 * The UserCountry.getCity report we find is the report on visits by country, but only for the visits
 * for the specific row. We take this report's row labels and add them as columns for the Referrers.getKeywords
 * table.
 *
 * Implementation details:
 *
 * Fetching intersected table can be done by segment or subtable. If the requested pivot by
 * dimension is the report's subtable dimension, then the subtable is used regardless, since it
 * is much faster than fetching by segment.
 *
 * Also, by default, fetching by segment is disabled in the config (see the
 * '[General] pivot_by_filter_enable_fetch_by_segment' option).
 */
class PivotByDimension extends BaseFilter
{
    /**
     * The pivot-by Dimension. The metadata in this class is used to determine if we can
     * pivot the report and used to fetch intersected tables.
     *
     * @var Dimension
     */
    private $pivotByDimension;

    /**
     * The report that reports on visits by the pivot dimension. The metadata in this class
     * is used to determine if we can pivot the report and used to fetch intersected tables
     * by segment.
     *
     * @var Report
     */
    private $pivotDimensionReport;

    /**
     * The column that should be displayed in the pivot table. This should be a metric, eg,
     * `'nb_visits'`, `'nb_actions'`, etc.
     *
     * @var string
     */
    private $pivotColumn;

    /**
     * The number of columns to limit the pivot table to. Applying a pivot can result in
     * tables with many, many columns. This can cause problems when displayed in web page.
     *
     * A default limit of 7 is imposed if no column limit is specified in construction.
     * If a negative value is supplied, no limiting is performed.
     *
     * Columns are summed and sorted before being limited so the columns w/ the most
     * visits will be displayed and the columns w/ the least will be cut off.
     *
     * @var int
     */
    private $pivotByColumnLimit;

    /**
     * Metadata for the report being pivoted. The metadata in this class is used to
     * determine if we can pivot the report and used to fetch intersected tables.
     *
     * @var Report
     */
    private $thisReport;

    /**
     * Metadata for the segment of the dimension of the report being pivoted. When
     * fetching intersected tables by segment, this is the segment used.
     *
     * @var Segment
     */
    private $thisReportDimensionSegment;

    /**
     * Whether fetching by segment is enabled or not.
     *
     * @var bool
     */
    private $isFetchingBySegmentEnabled;

    /**
     * The subtable dimension of the report being pivoted. Used to determine if and
     * how intersected tables are fetched.
     *
     * @var Dimension|null
     */
    private $subtableDimension;

    /**
     * The index value (if any) for the metric that should be displayed in the pivot
     * table.
     *
     * @var int|null
     */
    private $metricIndexValue;

    /**
     * Constructor.
     *
     * @param DataTable $table The table to pivot.
     * @param string $report The ID of the report being pivoted, eg, `'Referrers.getKeywords'`.
     * @param string $pivotByDimension The ID of the dimension to pivot by, eg, `'Referrers.Keyword'`.
     * @param string|false $pivotColumn The metric that should be displayed in the pivot table, eg, `'nb_visits'`.
     *                                  If `false`, the first non-label column is used.
     * @param false|int $pivotByColumnLimit The number of columns to limit the pivot table to.
     * @param bool $isFetchingBySegmentEnabled Whether to allow fetching by segment.
     * @throws Exception if pivoting the report by a dimension is unsupported.
     */
    public function __construct($table, $report, $pivotByDimension, $pivotColumn, $pivotByColumnLimit = false,
                                $isFetchingBySegmentEnabled = true)
    {
        parent::__construct($table);

        Log::debug("PivotByDimension::%s: creating with [report = %s, pivotByDimension = %s, pivotColumn = %s, "
            . "pivotByColumnLimit = %s, isFetchingBySegmentEnabled = %s]", __FUNCTION__, $report, $pivotByDimension,
            $pivotColumn, $pivotByColumnLimit, $isFetchingBySegmentEnabled);

        $this->pivotColumn = $pivotColumn;
        $this->pivotByColumnLimit = $pivotByColumnLimit ?: self::getDefaultColumnLimit();
        $this->isFetchingBySegmentEnabled = $isFetchingBySegmentEnabled;

        $namesToId = Metrics::getMappingFromIdToName();
        $this->metricIndexValue = isset($namesToId[$this->pivotColumn]) ? $namesToId[$this->pivotColumn] : null;

        $this->setPivotByDimension($pivotByDimension);
        $this->setThisReportMetadata($report);

        $this->checkSupportedPivot();
    }

    /**
     * Pivots to table.
     *
     * @param DataTable $table The table to manipulate.
     */
    public function filter($table)
    {
        // set of all column names in the pivoted table mapped with the sum of all column
        // values. used later in truncating and ordering the pivoted table's columns.
        $columnSet = array();

        // if no pivot column was set, use the first one found in the row
        if (empty($this->pivotColumn)) {
            $this->pivotColumn = $this->getNameOfFirstNonLabelColumnInTable($table);
        }

        Log::debug("PivotByDimension::%s: pivoting table with pivot column = %s", __FUNCTION__, $this->pivotColumn);

        foreach ($table->getRows() as $row) {
            $row->setColumns(array('label' => $row->getColumn('label')));

            $associatedTable = $this->getIntersectedTable($table, $row);
            if (!empty($associatedTable)) {
                foreach ($associatedTable->getRows() as $columnRow) {
                    $pivotTableColumn = $columnRow->getColumn('label');

                    $columnValue = $this->getColumnValue($columnRow, $this->pivotColumn);

                    if (isset($columnSet[$pivotTableColumn])) {
                        $columnSet[$pivotTableColumn] += $columnValue;
                    } else {
                        $columnSet[$pivotTableColumn] = $columnValue;
                    }

                    $row->setColumn($pivotTableColumn, $columnValue);
                }

                Common::destroy($associatedTable);
                unset($associatedTable);
            }
        }

        Log::debug("PivotByDimension::%s: pivoted columns set: %s", __FUNCTION__, $columnSet);

        $others = Piwik::translate('General_Others');
        $defaultRow = $this->getPivotTableDefaultRowFromColumnSummary($columnSet, $others);

        Log::debug("PivotByDimension::%s: un-prepended default row: %s", __FUNCTION__, $defaultRow);

        // post process pivoted datatable
        foreach ($table->getRows() as $row) {
            // remove subtables from rows
            $row->removeSubtable();
            $row->deleteMetadata('idsubdatatable_in_db');

            // use default row to ensure column ordering and add missing columns/aggregate cut-off columns
            $orderedColumns = $defaultRow;
            foreach ($row->getColumns() as $name => $value) {
                if (isset($orderedColumns[$name])) {
                    $orderedColumns[$name] = $value;
                } else {
                    $orderedColumns[$others] += $value;
                }
            }
            $row->setColumns($orderedColumns);
        }

        $table->clearQueuedFilters(); // TODO: shouldn't clear queued filters, but we can't wait for them to be run
                                      //       since generic filters are run before them. remove after refactoring
                                      //       processed metrics.

        // prepend numerals to columns in a queued filter (this way, disable_queued_filters can be used
        // to get machine readable data from the API if needed)
        $prependedColumnNames = $this->getOrderedColumnsWithPrependedNumerals($defaultRow, $others);

        Log::debug("PivotByDimension::%s: prepended column name mapping: %s", __FUNCTION__, $prependedColumnNames);

        $table->queueFilter(function (DataTable $table) use ($prependedColumnNames) {
            foreach ($table->getRows() as $row) {
                $row->setColumns(array_combine($prependedColumnNames, $row->getColumns()));
            }
        });
    }

    /**
     * An intersected table is a table that describes visits by a certain dimension for the visits
     * represented by a row in another table. This method fetches intersected tables either via
     * subtable or by using a segment. Read the class docs for more info.
     */
    private function getIntersectedTable(DataTable $table, Row $row)
    {
        if ($this->isPivotDimensionSubtable()) {
            return $this->loadSubtable($table, $row);
        }

        if ($this->isFetchingBySegmentEnabled) {
            $segmentValue = $row->getColumn('label');
            return $this->fetchIntersectedWithThisBySegment($table, $segmentValue);
        }

        // should never occur, unless checkSupportedPivot() fails to catch an unsupported pivot
        throw new Exception("Unexpected error, cannot fetch intersected table.");
    }

    private function isPivotDimensionSubtable()
    {
        return self::areDimensionsEqualAndNotNull($this->subtableDimension, $this->pivotByDimension);
    }

    private function loadSubtable(DataTable $table, Row $row)
    {
        $idSubtable = $row->getIdSubDataTable();
        if ($idSubtable === null) {
            return null;
        }

        if ($row->isSubtableLoaded()) {
            $subtable = $row->getSubtable();
        } else {
            $subtable = $this->thisReport->fetchSubtable($idSubtable, $this->getRequestParamOverride($table));
        }

        if ($subtable === null) { // sanity check
            throw new Exception("Unexpected error: could not load subtable '$idSubtable'.");
        }

        return $subtable;
    }

    private function fetchIntersectedWithThisBySegment(DataTable $table, $segmentValue)
    {
        $segmentStr = $this->thisReportDimensionSegment->getSegment() . "==" . urlencode($segmentValue);

        // TODO: segment + report API method query params should be stored in DataTable metadata so we don't have to access it here
        $originalSegment = Common::getRequestVar('segment', false);
        if (!empty($originalSegment)) {
            $segmentStr = $originalSegment . ';' . $segmentStr;
        }

        Log::debug("PivotByDimension: Fetching intersected with segment '%s'", $segmentStr);

        $params = array('segment' => $segmentStr) + $this->getRequestParamOverride($table);
        return $this->pivotDimensionReport->fetch($params);
    }

    private function setPivotByDimension($pivotByDimension)
    {
        $this->pivotByDimension = Dimension::factory($pivotByDimension);
        if (empty($this->pivotByDimension)) {
            throw new Exception("Invalid dimension '$pivotByDimension'.");
        }

        $this->pivotDimensionReport = Report::getForDimension($this->pivotByDimension);
    }

    private function setThisReportMetadata($report)
    {
        list($module, $method) = explode('.', $report);

        $this->thisReport = Report::factory($module, $method);
        if (empty($this->thisReport)) {
            throw new Exception("Unable to find report '$report'.");
        }

        $this->subtableDimension = $this->thisReport->getSubtableDimension();

        $thisReportDimension = $this->thisReport->getDimension();
        if ($thisReportDimension !== null) {
            $segments = $thisReportDimension->getSegments();
            $this->thisReportDimensionSegment = reset($segments);
        }
    }

    private function checkSupportedPivot()
    {
        $reportId = $this->thisReport->getModule() . '.' . $this->thisReport->getName();

        if (!$this->isFetchingBySegmentEnabled) {
            // if fetching by segment is disabled, then there must be a subtable for the current report and
            // subtable's dimension must be the pivot dimension

            if (empty($this->subtableDimension)) {
                throw new Exception("Unsupported pivot: report '$reportId' has no subtable dimension.");
            }

            if (!$this->isPivotDimensionSubtable()) {
                throw new Exception("Unsupported pivot: the subtable dimension for '$reportId' does not match the "
                                  . "requested pivotBy dimension. [subtable dimension = {$this->subtableDimension->getId()}, "
                                  . "pivot by dimension = {$this->pivotByDimension->getId()}]");
            }
        } else {
            $canFetchBySubtable = !empty($this->subtableDimension)
                && $this->subtableDimension->getId() === $this->pivotByDimension->getId();
            if ($canFetchBySubtable) {
                return;
            }

            // if fetching by segment is enabled, and we cannot fetch by subtable, then there has to be a report
            // for the pivot dimension (so we can fetch the report), and there has to be a segment for this report's
            // dimension (so we can use it when fetching)

            if (empty($this->pivotDimensionReport)) {
                throw new Exception("Unsupported pivot: No report for pivot dimension '{$this->pivotByDimension->getId()}'"
                                  . " (report required for fetching intersected tables by segment).");
            }

            if (empty($this->thisReportDimensionSegment)) {
                throw new Exception("Unsupported pivot: No segment for dimension of report '$reportId'."
                                  . " (segment required for fetching intersected tables by segment).");
            }
        }
    }

    /**
     * @param $columnRow
     * @param $pivotColumn
     * @return false|mixed
     */
    private function getColumnValue(Row $columnRow, $pivotColumn)
    {
        $value = $columnRow->getColumn($pivotColumn);
        if (empty($value)
            && !empty($this->metricIndexValue)
        ) {
            $value = $columnRow->getColumn($this->metricIndexValue);
        }
        return $value;
    }

    private function getNameOfFirstNonLabelColumnInTable(DataTable $table)
    {
        foreach ($table->getRows() as $row) {
            foreach ($row->getColumns() as $columnName => $ignore) {
                if ($columnName != 'label') {
                    return $columnName;
                }
            }
        }
    }

    private function getRequestParamOverride(DataTable $table)
    {
        $params = array(
            'pivotBy' => '',
            'column' => '',
            'flat' => 0,
            'totals' => 0,
            'disable_queued_filters' => 1,
            'disable_generic_filters' => 1,
            'showColumns' => '',
            'hideColumns' => ''
        );

        /** @var Site $site */
        $site = $table->getMetadata('site');
        if (!empty($site)) {
            $params['idSite'] = $site->getId();
        }

        /** @var Period $period */
        $period = $table->getMetadata('period');
        if (!empty($period)) {
            $params['period'] = $period->getLabel();

            if ($params['period'] == 'range') {
                $params['date'] = $period->getRangeString();
            } else {
                $params['date'] = $period->getDateStart()->toString();
            }
        }

        return $params;
    }

    private function getPivotTableDefaultRowFromColumnSummary($columnSet, $othersRowLabel)
    {
        // sort columns by sum (to ensure deterministic ordering)
        arsort($columnSet);

        // limit columns if necessary (adding aggregate Others column at end)
        if ($this->pivotByColumnLimit > 0
            && count($columnSet) > $this->pivotByColumnLimit
        ) {
            $columnSet = array_slice($columnSet, 0, $this->pivotByColumnLimit - 1, $preserveKeys = true);
            $columnSet[$othersRowLabel] = 0;
        }

        // remove column sums from array so it can be used as a default row
        $columnSet = array_map(function () { return false; }, $columnSet);

        // make sure label column is first
        $columnSet = array('label' => false) + $columnSet;

        return $columnSet;
    }

    private function getOrderedColumnsWithPrependedNumerals($defaultRow, $othersRowLabel)
    {
        $flags = ENT_COMPAT;
        if (defined('ENT_HTML401')) {
            $flags |= ENT_HTML401; // part of default flags for 5.4, but not 5.3
        }

        // must use decoded character otherwise sort later will fail
        // (sort column will be set to decoded but columns will have &nbsp;)
        $nbsp = html_entity_decode('&nbsp;', $flags, 'utf-8');

        $result = array();

        $currentIndex = 1;
        foreach ($defaultRow as $columnName => $ignore) {
            if ($columnName === $othersRowLabel
                || $columnName === 'label'
            ) {
                $result[] = $columnName;
            } else {
                $modifiedColumnName = $currentIndex . '.' . $nbsp . $columnName;
                $result[] = $modifiedColumnName;

                ++$currentIndex;
            }
        }

        return $result;
    }

    /**
     * Returns true if pivoting by subtable is supported for a report. Will return true if the report
     * has a subtable dimension and if the subtable dimension is different than the report's dimension.
     *
     * @param Report $report
     * @return bool
     */
    public static function isPivotingReportBySubtableSupported(Report $report)
    {
        return self::areDimensionsNotEqualAndNotNull($report->getSubtableDimension(), $report->getDimension());
    }

    /**
     * Returns true if fetching intersected tables by segment is enabled in the INI config, false if otherwise.
     *
     * @return bool
     */
    public static function isSegmentFetchingEnabledInConfig()
    {
        return Config::getInstance()->General['pivot_by_filter_enable_fetch_by_segment'];
    }

    /**
     * Returns the default maximum number of columns to allow in a pivot table from the INI config.
     * Uses the **pivot_by_filter_default_column_limit** INI config option.
     *
     * @return int
     */
    public static function getDefaultColumnLimit()
    {
        return Config::getInstance()->General['pivot_by_filter_default_column_limit'];
    }

    /**
     * @param Dimension|null $lhs
     * @param Dimension|null $rhs
     * @return bool
     */
    private static function areDimensionsEqualAndNotNull($lhs, $rhs)
    {
        return !empty($lhs) && !empty($rhs) && $lhs->getId() == $rhs->getId();
    }

    /**
     * @param Dimension|null $lhs
     * @param Dimension|null $rhs
     * @return bool
     */
    private static function areDimensionsNotEqualAndNotNull($lhs, $rhs)
    {
        return !empty($lhs) && !empty($rhs) && $lhs->getId() != $rhs->getId();
    }
}