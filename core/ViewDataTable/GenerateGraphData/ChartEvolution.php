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
 * Piwik_ViewDataTable_GenerateGraphData for the Evolution graph (eg. Last 30 days visits) using Piwik_Visualization_Chart_Evolution
 *
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
class Piwik_ViewDataTable_GenerateGraphData_ChartEvolution extends Piwik_ViewDataTable_GenerateGraphData
{

    // used for the row picker
    // (the series picker configuration resides in the parent class)
    protected $rowPicker = false;
    protected $visibleRows = array();
    protected $rowPickerConfig = array();

    protected function checkStandardDataTable()
    {
        // DataTable_Array and DataTable allowed for the evolution chart
        Piwik::checkObjectTypeIs($this->dataTable, array('Piwik_DataTable_Array', 'Piwik_DataTable'));
    }

    protected function getViewDataTableId()
    {
        return 'generateDataChartEvolution';
    }

    function __construct()
    {
        $this->view = new Piwik_Visualization_Chart_Evolution();
    }

    /**
     * Adds the same series picker as parent::setSelectableColumns but the selectable series are not
     * columns of a single row but the same column across multiple rows, e.g. the number of visits
     * for each referrer type.
     * @param array $visibleRows the rows that are initially visible
     * @param string $matchBy the way the items in $visibleRows are matched with the data. possible values:
     *                            - label: matches the label of the row
     */
    public function addRowPicker($visibleRows, $matchBy = 'label')
    {
        $this->rowPicker = $matchBy;

        if (!is_array($visibleRows)) {
            $visibleRows = array($visibleRows);
        }
        $this->visibleRows = $visibleRows;
    }

    /**
     * This method is called for every row of every table in the DataTable_Array.
     * It incrementally builds the row picker configuration and determines whether
     * the row is initially visible or not.
     * @param string $rowLabel
     * @return bool
     */
    protected function handleRowForRowPicker(&$rowLabel)
    {
        // determine whether row is visible
        $isVisible = true;
        switch ($this->rowPicker) {
            case 'label':
                $isVisible = in_array($rowLabel, $this->visibleRows);
                break;
        }

        // build config
        if (!isset($this->rowPickerConfig[$rowLabel])) {
            $this->rowPickerConfig[$rowLabel] = array(
                'label'     => $rowLabel,
                'matcher'   => $rowLabel,
                'displayed' => $isVisible
            );
        }

        return $isVisible;
    }

    protected function loadDataTableFromAPI()
    {
        $period = Piwik_Common::getRequestVar('period');
        // period will be overridden when 'range' is requested in the UI
        // but the graph will display for each day of the range.
        // Default 'range' behavior is to return the 'sum' for the range
        if ($period == 'range') {
            $_GET['period'] = 'day';
        }
        // throws exception if no view access
        parent::loadDataTableFromAPI();
        if ($period == 'range') {
            $_GET['period'] = $period;
        }
    }

    protected function initChartObjectData()
    {
        // if the loaded datatable is a simple DataTable, it is most likely a plugin plotting some custom data
        // we don't expect plugin developers to return a well defined Piwik_DataTable_Array
        if ($this->dataTable instanceof Piwik_DataTable) {
            return parent::initChartObjectData();
        }

        $this->dataTable->applyQueuedFilters();
        if (!($this->dataTable instanceof Piwik_DataTable_Array)) {
            throw new Exception("Expecting a DataTable_Array with custom format to draw an evolution chart");
        }

        // the X label is extracted from the 'period' object in the table's metadata
        $xLabels = $uniqueIdsDataTable = array();
        foreach ($this->dataTable->getArray() as $idDataTable => $metadataDataTable) {
            //eg. "Aug 2009"
            $xLabels[] = $metadataDataTable->getMetadata('period')->getLocalizedShortString();
            // we keep track of all unique data table that we need to set a Y value for
            $uniqueIdsDataTable[] = $idDataTable;
        }

        $idSite = Piwik_Common::getRequestVar('idSite', null, 'int');
        $requestedColumnNames = $this->getColumnsToDisplay();
        $units = $this->getUnitsForColumnsToDisplay();

        $yAxisLabelToUnit = array();
        $yAxisLabelToValue = array();
        foreach ($this->dataTable->getArray() as $idDataTable => $dataTable) {
            foreach ($dataTable->getRows() as $row) {
                $rowLabel = $row->getColumn('label');

                // put together configuration for row picker.
                // do this for every data table in the array because rows do not
                // have to present for each date.
                if ($this->rowPicker !== false) {
                    $rowVisible = $this->handleRowForRowPicker($rowLabel);
                    if (!$rowVisible) {
                        continue;
                    }
                }

                // build data for request columns
                foreach ($requestedColumnNames as $requestedColumnName) {
                    $yAxisLabel = $this->getSeriesLabel($rowLabel, $requestedColumnName);
                    if (($columnValue = $row->getColumn($requestedColumnName)) !== false) {
                        $yAxisLabelToValue[$yAxisLabel][$idDataTable] = $columnValue;
                        $yAxisLabelToUnit[$yAxisLabel] = $units[$requestedColumnName];
                    }
                }
            }
        }

        // make sure all column values are set to at least zero (no gap in the graph)
        $yAxisLabelToValueCleaned = array();
        foreach ($uniqueIdsDataTable as $uniqueIdDataTable) {
            foreach ($yAxisLabelToValue as $yAxisLabel => $idDataTableToColumnValue) {
                if (isset($idDataTableToColumnValue[$uniqueIdDataTable])) {
                    $columnValue = $idDataTableToColumnValue[$uniqueIdDataTable];
                } else {
                    $columnValue = 0;
                }
                $yAxisLabelToValueCleaned[$yAxisLabel][] = $columnValue;
            }
        }

        $this->view->setAxisXLabels($xLabels);
        $this->view->setAxisYValues($yAxisLabelToValueCleaned);
        $this->view->setAxisYUnits($yAxisLabelToUnit);

        $countGraphElements = $this->dataTable->getRowsCount();
        $dataTables = $this->dataTable->getArray();
        $firstDatatable = reset($dataTables);
        $period = $firstDatatable->getMetadata('period');

        $stepSize = $this->getXAxisStepSize($period->getLabel(), $countGraphElements);
        $this->view->setXSteps($stepSize);

        if ($this->isLinkEnabled()) {
            $axisXOnClick = array();
            $queryStringAsHash = $this->getQueryStringAsHash();
            foreach ($this->dataTable->getArray() as $idDataTable => $metadataDataTable) {
                $period = $metadataDataTable->getMetadata('period');
                $dateInUrl = $period->getDateStart();
                $parameters = array(
                    'idSite'  => $idSite,
                    'period'  => $period->getLabel(),
                    'date'    => $dateInUrl->toString(),
                    'segment' => Piwik_ViewDataTable::getRawSegmentFromRequest()
                );
                $hash = '';
                if (!empty($queryStringAsHash)) {
                    $hash = '#' . Piwik_Url::getQueryStringFromParameters($queryStringAsHash + $parameters);
                }
                $link = 'index.php?' .
                    Piwik_Url::getQueryStringFromParameters(array(
                        'module' => 'CoreHome',
                        'action' => 'index',
                    ) + $parameters)
                    . $hash;
                $axisXOnClick[] = $link;
            }
            $this->view->setAxisXOnClick($axisXOnClick);
        }

        $this->addSeriesPickerToView();
        if ($this->rowPicker !== false) {
            // configure the row picker
            $this->view->setSelectableRows(array_values($this->rowPickerConfig));
        }
    }

    /**
     * Derive the series label from the row label and the column name.
     * If the row label is set, both the label and the column name are displayed.
     * @param string $rowLabel
     * @param string $columnName
     * @return string
     */
    private function getSeriesLabel($rowLabel, $columnName)
    {
        $metricLabel = $this->getColumnTranslation($columnName);

        if ($rowLabel !== false) {
            // eg. "Yahoo! (Visits)"
            $label = "$rowLabel ($metricLabel)";
        } else {
            // eg. "Visits"
            $label = $metricLabel;
        }

        return $label;
    }

    /**
     * We link the graph dots to the same report as currently being displayed (only the date would change).
     *
     * In some cases the widget is loaded within a report that doesn't exist as such.
     * For example, the dashboards loads the 'Last visits graph' widget which can't be directly linked to.
     * Instead, the graph must link back to the dashboard.
     *
     * In other cases, like Visitors>Overview or the Goals graphs, we can link the graph clicks to the same report.
     *
     * To detect whether or not we can link to a report, we simply check if the current URL from which it was loaded
     * belongs to the menu or not. If it doesn't belong to the menu, we do not append the hash to the URL,
     * which results in loading the dashboard.
     *
     * @return array Query string array to append to the URL hash or false if the dashboard should be displayed
     */
    private function getQueryStringAsHash()
    {
        $queryString = Piwik_Url::getArrayFromCurrentQueryString();
        $piwikParameters = array('idSite', 'date', 'period', 'XDEBUG_SESSION_START', 'KEY');
        foreach ($piwikParameters as $parameter) {
            unset($queryString[$parameter]);
        }
        if (Piwik_IsMenuUrlFound($queryString)) {
            return $queryString;
        }
        return false;
    }

    private function isLinkEnabled()
    {
        static $linkEnabled;
        if (!isset($linkEnabled)) {
            // 1) Custom Date Range always have link disabled, otherwise
            // the graph data set is way too big and fails to display
            // 2) disableLink parameter is set in the Widgetize "embed" code
            $linkEnabled = !Piwik_Common::getRequestVar('disableLink', 0, 'int')
                && Piwik_Common::getRequestVar('period', 'day') != 'range';
        }
        return $linkEnabled;
    }

    private function getXAxisStepSize($periodLabel, $countGraphElements)
    {
        // when the number of elements plotted can be small, make sure the X legend is useful
        if ($countGraphElements <= 7) {
            return 1;
        }

        switch ($periodLabel) {
            case 'day':
                $steps = 5;
                break;
            case 'week':
                $steps = 4;
                break;
            case 'month':
                $steps = 5;
                break;
            case 'year':
                $steps = 5;
                break;
            default:
                $steps = 5;
                break;
        }

        $paddedCount = $countGraphElements + 2; // pad count so last label won't be cut off
        return ceil($paddedCount / $steps);
    }
}
