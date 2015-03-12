<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\API\DataTableManipulator;

use Piwik\API\DataTableManipulator;
use Piwik\Common;
use Piwik\DataTable;
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
    const SEPARATOR_RECURSIVE_LABEL = '>';
    const TERMINAL_OPERATOR = '@';

    private $labels;
    private $addLabelIndex;
    const FLAG_IS_ROW_EVOLUTION = 'label_index';

    /**
     * Filter a data table by label.
     * The filtered table is returned, which might be a new instance.
     *
     * $apiModule, $apiMethod and $request are needed load sub-datatables
     * for the recursive search. If the label is not recursive, these parameters
     * are not needed.
     *
     * @param string $labels the labels to search for
     * @param DataTable $dataTable the data table to be filtered
     * @param bool $addLabelIndex Whether to add label_index metadata describing which
     *                            label a row corresponds to.
     * @return DataTable
     */
    public function filter($labels, $dataTable, $addLabelIndex = false)
    {
        if (!is_array($labels)) {
            $labels = array($labels);
        }

        $this->labels = $labels;
        $this->addLabelIndex = (bool)$addLabelIndex;
        return $this->manipulate($dataTable);
    }

    /**
     * Method for the recursive descend
     *
     * @param array $labelParts
     * @param DataTable $dataTable
     * @return Row|bool
     */
    private function doFilterRecursiveDescend($labelParts, $dataTable)
    {
        // search for the first part of the tree search
        $labelPart = array_shift($labelParts);

        $row = false;
        foreach ($this->getLabelVariations($labelPart) as $labelPart) {
            $row = $dataTable->getRowFromLabel($labelPart);
            if ($row !== false) {
                break;
            }
        }

        if ($row === false) {
            // not found
            return false;
        }

        // end of tree search reached
        if (count($labelParts) == 0) {
            return $row;
        }

        $subTable = $this->loadSubtable($dataTable, $row);
        if ($subTable === null) {
            // no more subtables but label parts left => no match found
            return false;
        }

        return $this->doFilterRecursiveDescend($labelParts, $subTable);
    }

    /**
     * Clean up request for ResponseBuilder to behave correctly
     *
     * @param $request
     */
    protected function manipulateSubtableRequest($request)
    {
        unset($request['label']);

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

        if ($this->apiModule == 'Actions'
            && in_array($this->apiMethod, $pageTitleReports)
        ) {
            if ($isTerminal) {
                array_unshift($variations, ' ' . $sanitizedLabel);
                array_unshift($variations, ' ' . $label);
            } else {
                // special case: the Actions.getPageTitles report prefixes some labels with a blank.
                // the blank might be passed by the user but is removed in Request::getRequestArrayFromString.
                $variations[] = ' ' . $sanitizedLabel;
                $variations[] = ' ' . $label;
            }
        }
        $variations[] = $label;

        $variations = array_unique($variations);

        return $variations;
    }

    /**
     * Filter a DataTable instance. See @filter for more info.
     *
     * @param DataTable\Simple|DataTable\Map $dataTable
     * @return mixed
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
}
