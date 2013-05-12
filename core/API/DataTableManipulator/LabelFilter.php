<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * This class is responsible for handling the label parameter that can be
 * added to every API call. If the parameter is set, only the row with the matching
 * label is returned.
 *
 * The labels passed to this class should be urlencoded.
 * Some reports use recursive labels (e.g. action reports). Use > to join them.
 *
 * @package Piwik
 * @subpackage Piwik_API
 */
class Piwik_API_DataTableManipulator_LabelFilter extends Piwik_API_DataTableManipulator
{
    const SEPARATOR_RECURSIVE_LABEL = '>';

    private $labels;
    private $addLabelIndex;

    /**
     * Filter a data table by label.
     * The filtered table is returned, which might be a new instance.
     *
     * $apiModule, $apiMethod and $request are needed load sub-datatables
     * for the recursive search. If the label is not recursive, these parameters
     * are not needed.
     *
     * @param string $labels      the labels to search for
     * @param Piwik_DataTable $dataTable  the data table to be filtered
     * @param bool $addLabelIndex Whether to add label_index metadata describing which
     *                            label a row corresponds to.
     * @return Piwik_DataTable
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
     * @param Piwik_DataTable $dataTable
     * @return Piwik_DataTable_Row|false
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
     * Clean up request for Piwik_API_ResponseBuilder to behave correctly
     *
     * @param $request
     */
    protected function manipulateSubtableRequest(&$request)
    {
        unset($request['label']);
    }

    /**
     * Use variations of the label to make it easier to specify the desired label
     *
     * Note: The HTML Encoded version must be tried first, since in Piwik_API_ResponseBuilder the $label is unsanitized
     * via Piwik_Common::unsanitizeLabelParameter.
     *
     * @param string $label
     * @return array
     */
    private function getLabelVariations($label)
    {
        static $pageTitleReports = array('getPageTitles', 'getEntryPageTitles', 'getExitPageTitles');
        
        $variations = array();
        $label = urldecode($label);
        $label = trim($label);

        $sanitizedLabel = Piwik_Common::sanitizeInputValue( $label );
        $variations[] = $sanitizedLabel;

        if ($this->apiModule == 'Actions'
            && in_array($this->apiMethod, $pageTitleReports)
        ) {
            // special case: the Actions.getPageTitles report prefixes some labels with a blank.
            // the blank might be passed by the user but is removed in Piwik_API_Request::getRequestArrayFromString.
            $variations[] = ' ' . $sanitizedLabel;
            $variations[] = ' ' . $label;
        }
        $variations[] = $label;

        return $variations;
    }

    /**
     * Filter a Piwik_DataTable instance. See @filter for more info.
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
                        $row->setMetadata('label_index', $labelIndex);
                    }
                    
                    $result->addRow($row);
                    break;
                }
            }
        }
        return $result;
    }
}
