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

    private $labels;
    private $addLabelIndex;
    private $isComparing;
    private $labelSeries;

    private string $labelColumn;

    /**
     * The label the next subtable to be loaded will be searched for, if any.
     */
    private ?string $nextLabelPart = null;

    /**
     * Whether the row searched for in that subtable is the one the search returns.
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
     * We are looking for a single row, so drop the others before the subtable is post-processed.
     * If the label doesn't match here it may still match once the subtable has been post-processed,
     * so in that case keep the whole table and let the regular search deal with it. When the row is
     * the one the search returns, a few other rows are kept too, see getRowsForWholeTableChecks().
     *
     * @param mixed $dataTable
     * @param array $request
     * @return mixed
     */
    protected function pruneLoadedSubtable($dataTable, array $request)
    {
        if ($this->nextLabelPart === null || !$dataTable instanceof DataTable) {
            return $dataTable;
        }

        if ($this->hasFilterDependingOnOtherRows($request)) {
            return $dataTable;
        }

        $row = $this->findOnlyRowForLabel($this->nextLabelPart, $dataTable);

        // a summary row is still labelled -1 at this point, and only gets its real label during
        // post-processing, so a match on it is not the row the search will find
        if ($row === null || $row === $dataTable->getSummaryRow()) {
            return $dataTable;
        }

        $rows = [$row];

        // a row we only descend through is used for its subtable id alone, so its columns don't matter
        if ($this->nextLabelPartIsLast) {
            $rows = $this->getRowsForWholeTableChecks($row, $dataTable);
        }

        $dataTable->setRows($rows);

        return $dataTable;
    }

    /**
     * Finds the row the search will pick once the subtable has been post-processed, as long as only
     * one row can be it. Post-processing decodes the labels, so rows stored as "a &amp; b" and
     * "a & b" both end up as "a &amp; b", and the search then picks whichever of them the sort puts
     * last. Only the whole table can tell which one that is, so if more than one row matches, as
     * its label reads now or once decoded, no row is returned.
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
     * Some post-processing looks at every row before it computes anything. The PagePerformance and
     * page generation time metrics drop their columns when no row has a value for them, and goal
     * columns are added for every goal any row has. Left with the target row alone, those checks
     * would only see that row, and the row we return could lose columns it has on the whole table.
     *
     * So next to the target row, keep the first row with a value for each column, and each goal.
     * Goal columns are added in the order the rows first show each goal, so the rows keep their
     * order in the table. The search only returns the target row, so the extra rows never reach
     * the output.
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
     * Records the columns of a row that have a value and are not covered yet. For a column holding
     * an array, like goals, it is the keys that get covered, whatever their values are.
     *
     * @return bool whether the row covered anything new
     */
    private function coverColumns(array $columns, array &$covered, array &$coveredKeys): bool
    {
        $coveredNew = false;

        // array_filter() drops the empty values, which keeps this cheap on rows with nothing new
        foreach (array_filter(array_diff_key($columns, $covered)) as $name => $value) {
            if (is_array($value)) {
                $newKeys = array_diff_key($value, $coveredKeys[$name] ?? []);
                if (!empty($newKeys)) {
                    $coveredKeys[$name] = ($coveredKeys[$name] ?? []) + $newKeys;
                    $coveredNew = true;
                }
            } elseif (!is_numeric($value) || $value != 0) {
                // a numeric string such as '0.0' has no value either, but array_filter() keeps it
                $covered[$name] = true;
                $coveredNew = true;
            }
        }

        return $coveredNew;
    }

    /**
     * Whether a generic filter still to be applied to the subtable decides what to keep by looking
     * at the other rows. ExcludeLowPopulation can derive its threshold from the sum of a column
     * across the whole table, and the row limiting filters keep rows by position, so for those the
     * rows we are about to drop are part of the result rather than just overhead. A pattern can
     * remove the rows kept for the whole table checks, see getRowsForWholeTableChecks().
     *
     * Both row limiting filters are skipped when their own parameter is missing, and an unlimited
     * limit cannot drop anything, so in those cases there is nothing to protect. A truncate of zero
     * is not one of those cases: it keeps the summary row alone, so it has to be read as a value
     * rather than tested for emptiness.
     */
    private function hasFilterDependingOnOtherRows(array $request): bool
    {
        if (!empty($request['filter_excludelowpop']) || !empty($request['filter_pattern'])) {
            return true;
        }

        if (isset($request['filter_truncate']) && (int) $request['filter_truncate'] >= 0) {
            return true;
        }

        if (isset($request['filter_limit']) && (int) $request['filter_limit'] !== -1) {
            return true;
        }

        return !empty($request['filter_offset']);
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
