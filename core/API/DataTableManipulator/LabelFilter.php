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

        try {
            $subTable = $this->loadSubtable($dataTable, $row);
        } finally {
            $this->nextLabelPart = null;
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
     * so in that case keep the whole table and let the regular search deal with it.
     *
     * What this does not promise is that the row matching now is the one that would have matched
     * later. Post-processing rewrites labels, so two rows can end up sharing one, and whichever of
     * them matches here wins. The search picks arbitrarily between equal labels either way.
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

        $row = $this->findRowForLabel($this->labelColumn, $this->nextLabelPart, $dataTable);

        if ($row === false) {
            return $dataTable;
        }

        // a summary row is still labelled -1 at this point, and an ordinary row can be labelled
        // that too, so a match here may be the wrong one of the two. keep the whole table and let
        // the regular search pick, once the summary row has been renamed.
        if ($row === $dataTable->getSummaryRow()) {
            return $dataTable;
        }

        $dataTable->setRows([$row]);

        return $dataTable;
    }

    /**
     * Whether a generic filter still to be applied to the subtable decides what to keep by looking
     * at the other rows. ExcludeLowPopulation can derive its threshold from the sum of a column
     * across the whole table, and the row limiting filters keep rows by position, so for those the
     * rows we are about to drop are part of the result rather than just overhead.
     *
     * Both row limiting filters are skipped when their own parameter is missing, and an unlimited
     * limit cannot drop anything, so in those cases there is nothing to protect. A truncate of zero
     * is not one of those cases: it keeps the summary row alone, so it has to be read as a value
     * rather than tested for emptiness.
     */
    private function hasFilterDependingOnOtherRows(array $request): bool
    {
        if (!empty($request['filter_excludelowpop'])) {
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
