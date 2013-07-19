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
 * Generates JQPlot JSON data/config for evolution graphs.
 */
class Piwik_JqplotDataGenerator_Evolution extends Piwik_JqplotDataGenerator
{
    protected $rowPickerConfig = array();
    
    /**
     * Constructor.
     */
    public function __construct($properties)
    {
        parent::__construct(new Piwik_Visualization_Chart_Evolution(), $properties);
    }

    protected function initChartObjectData($dataTable)
    {
        // if the loaded datatable is a simple DataTable, it is most likely a plugin plotting some custom data
        // we don't expect plugin developers to return a well defined Piwik_DataTable_Array
        if ($dataTable instanceof Piwik_DataTable) {
            return parent::initChartObjectData($dataTable);
        }

        $dataTable->applyQueuedFilters();

        // the X label is extracted from the 'period' object in the table's metadata
        $xLabels = $uniqueIdsDataTable = array();
        foreach ($dataTable->getArray() as $idDataTable => $metadataDataTable) {
            //eg. "Aug 2009"
            $xLabels[] = $metadataDataTable->getMetadata('period')->getLocalizedShortString();
            // we keep track of all unique data table that we need to set a Y value for
            $uniqueIdsDataTable[] = $idDataTable;
        }

        $idSite = Piwik_Common::getRequestVar('idSite', null, 'int');
        $requestedColumnNames = $this->properties['columns_to_display'];
        $units = $this->getUnitsForColumnsToDisplay();

        $yAxisLabelToUnit = array();
        $yAxisLabelToValue = array();
        foreach ($dataTable->getArray() as $idDataTable => $childTable) {
            foreach ($childTable->getRows() as $row) {
                $rowLabel = $row->getColumn('label');

                // put together configuration for row picker.
                // do this for every data table in the array because rows do not
                // have to present for each date.
                if ($this->properties['row_picker_mach_rows_by'] !== false) {
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
        
        $visualization = $this->visualization;
        $visualization->setAxisXLabels($xLabels);
        $visualization->setAxisYValues($yAxisLabelToValueCleaned);
        $visualization->setAxisYUnits($yAxisLabelToUnit);

        $countGraphElements = $dataTable->getRowsCount();
        $dataTables = $dataTable->getArray();
        $firstDatatable = reset($dataTables);
        $period = $firstDatatable->getMetadata('period');

        $stepSize = $this->getXAxisStepSize($period->getLabel(), $countGraphElements);
        $visualization->setXSteps($stepSize);

        if ($this->isLinkEnabled()) {
            $axisXOnClick = array();
            $queryStringAsHash = $this->getQueryStringAsHash();
            foreach ($dataTable->getArray() as $idDataTable => $metadataDataTable) {
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
            $visualization->setAxisXOnClick($axisXOnClick);
        }

        $this->addSeriesPickerToView();
        
        // configure the row picker
        if ($this->properties['row_picker_mach_rows_by'] !== false) {
            $visualization->setSelectableRows(array_values($this->rowPickerConfig));
        }
    }

    /**
     * This method is called for every row of every table in the DataTable_Array.
     * It incrementally builds the row picker configuration and determines whether
     * the row is initially visible or not.
     * @param string $rowLabel
     * @return bool
     */
    private function handleRowForRowPicker(&$rowLabel)
    {
        // determine whether row is visible
        $isVisible = true;
        if ($this->properties['row_picker_mach_rows_by'] == 'label') {
            $isVisible = in_array($rowLabel, $this->properties['row_picker_visible_rows']);
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

    /**
     * Derive the series label from the row label and the column name.
     * If the row label is set, both the label and the column name are displayed.
     * @param string $rowLabel
     * @param string $columnName
     * @return string
     */
    private function getSeriesLabel($rowLabel, $columnName)
    {
        $metricLabel = @$this->properties['translations'][$columnName];

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
