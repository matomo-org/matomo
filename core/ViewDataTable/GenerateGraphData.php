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
 * Reads data from the API and prepares data to give to the renderer Piwik_Visualization_Chart.
 * This class is used to generate the data for the graphs.
 * You can set the number of elements to appear in the graph using: setGraphLimit();
 * Example:
 * <pre>
 *    function getWebsites( $fetch = false)
 *    {
 *        $view = Piwik_ViewDataTable::factory();
 *        $view->init( $this->pluginName, 'getWebsites', 'Referers.getWebsites', 'getUrlsFromWebsiteId' );
 *        $view->setColumnsToDisplay( array('label','nb_visits') );
 *        $view->setLimit(10);
 *        $view->setGraphLimit(12);
 *        return $this->renderView($view, $fetch);
 *    }
 * </pre>
 *
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
abstract class Piwik_ViewDataTable_GenerateGraphData extends Piwik_ViewDataTable
{
    /**
     * Number of elements to display in the graph.
     * @var int
     */
    protected $graphLimit = null;
    protected $yAxisUnit = '';

    // used for the series picker
    protected $selectableColumns = array();

    public function setAxisYUnit($unit)
    {
        $this->yAxisUnit = $unit;
    }

    /**
     * Sets the number max of elements to display (number of pie slice, vertical bars, etc.)
     * If the data has more elements than $limit then the last part of the data will be the sum of all the remaining data.
     *
     * @param int $limit
     */
    public function setGraphLimit($limit)
    {
        $this->graphLimit = $limit;
    }

    /**
     * Returns numbers of elemnts to display in the graph
     *
     * @return int
     */
    public function getGraphLimit()
    {
        return $this->graphLimit;
    }

    protected $displayPercentageInTooltip = true;

    /**
     * The percentage in tooltips is computed based on the sum of all values for the plotted column.
     * If the sum of the column in the data set is not the number of elements in the data set,
     * for example when plotting visits that have a given plugin enabled:
     * one visit can have several plugins, hence the sum is much greater than the number of visits.
     * In this case displaying the percentage doesn't make sense.
     */
    public function disallowPercentageInGraphTooltip()
    {
        $this->displayPercentageInTooltip = false;
    }

    /**
     * Sets the columns that can be added/removed by the user
     * This is done on data level (not html level) because the columns might change after reloading via sparklines
     * @param array $columnsNames Array of column names eg. array('nb_visits','nb_hits')
     */
    public function setSelectableColumns($columnsNames)
    {
        // the array contains values if enableShowGoals() has been used
        // add $columnsNames to the beginning of the array
        $this->selectableColumns = array_merge($columnsNames, $this->selectableColumns);
    }

    /**
     * The implementation of this method in Piwik_ViewDataTable passes to the graph whether the
     * goals icon should be displayed or not. Here, we use it to implicitly add the goal metrics
     * to the metrics picker.
     */
    public function enableShowGoals()
    {
        parent::enableShowGoals();

        $goalMetrics = array('nb_conversions', 'revenue');
        $this->selectableColumns = array_merge($this->selectableColumns, $goalMetrics);

        $this->setColumnTranslation('nb_conversions', Piwik_Translate('Goals_ColumnConversions'));
        $this->setColumnTranslation('revenue', Piwik_Translate('General_TotalRevenue'));
    }

    /**
     * Used in initChartObjectData to add the series picker config to the view object
     * @param bool $multiSelect
     */
    protected function addSeriesPickerToView($multiSelect = true)
    {
        if (count($this->selectableColumns)
            && Piwik_Common::getRequestVar('showSeriesPicker', 1) == 1
        ) {
            // build the final configuration for the series picker
            $columnsToDisplay = $this->getColumnsToDisplay();
            $selectableColumns = array();

            foreach ($this->selectableColumns as $column) {
                $selectableColumns[] = array(
                    'column'      => $column,
                    'translation' => $this->getColumnTranslation($column),
                    'displayed'   => in_array($column, $columnsToDisplay)
                );
            }
            $this->view->setSelectableColumns($selectableColumns, $multiSelect);
        }
    }

    protected function getUnitsForColumnsToDisplay()
    {
        // derive units from column names
        $idSite = Piwik_Common::getRequestVar('idSite', null, 'int');
        $units = $this->deriveUnitsFromRequestedColumnNames($this->getColumnsToDisplay(), $idSite);
        if (!empty($this->yAxisUnit)) {
            // force unit to the value set via $this->setAxisYUnit()
            foreach ($units as &$unit) {
                $unit = $this->yAxisUnit;
            }
        }

        return $units;
    }

    protected function deriveUnitsFromRequestedColumnNames($requestedColumnNames, $idSite)
    {
        $units = array();
        foreach ($requestedColumnNames as $columnName) {
            $derivedUnit = Piwik_API_API::getUnit($columnName, $idSite);
            $units[$columnName] = empty($derivedUnit) ? false : $derivedUnit;
        }
        return $units;
    }

    public function main()
    {
        if ($this->mainAlreadyExecuted) {
            return;
        }
        $this->mainAlreadyExecuted = true;

        // Graphs require the full dataset, setting limit to null (same as 'no limit')
        $this->setLimit(null);

        // the queued filters will be manually applied later. This is to ensure that filtering using search
        // will be done on the table before the labels are enhanced (see ReplaceColumnNames)
        $this->disableQueuedFilters();

        // throws exception if no view access
        $this->loadDataTableFromAPI();
        $this->checkStandardDataTable();
        $this->postDataTableLoadedFromAPI();

        $graphLimit = $this->getGraphLimit();
        if (!empty($graphLimit)) {
            $offsetStartSummary = $this->getGraphLimit() - 1;
            $this->dataTable->filter('AddSummaryRow',
                array($offsetStartSummary,
                      Piwik_Translate('General_Others'),

                      // Column to sort by, before truncation
                      $this->dataTable->getSortedByColumnName()
                          ? $this->dataTable->getSortedByColumnName()
                          : Piwik_Archive::INDEX_NB_VISITS
                )
            );
        }
        $this->isDataAvailable = $this->dataTable->getRowsCount() != 0;

        // if addTotalRow was called in GenerateGraphHTML, add a row containing totals of
        // different metrics
        if (Piwik_Common::getRequestVar('add_total_row', 0) == 1) {
            $this->dataTable->queueFilter('AddSummaryRow', array(0, Piwik_Translate('General_Total'), null, false));
        }

        if ($this->isDataAvailable) {
            $this->initChartObjectData();
        }
        $this->view->customizeChartProperties();
    }

    protected function initChartObjectData()
    {
        $this->dataTable->applyQueuedFilters();

        // We apply a filter to the DataTable, decoding the label column (useful for keywords for example)
        $this->dataTable->filter('ColumnCallbackReplace', array('label', 'urldecode'));

        $xLabels = $this->dataTable->getColumn('label');
        $columnNames = parent::getColumnsToDisplay();
        if (($labelColumnFound = array_search('label', $columnNames)) !== false) {
            unset($columnNames[$labelColumnFound]);
        }

        $columnNameToTranslation = $columnNameToValue = array();
        foreach ($columnNames as $columnName) {
            $columnNameToTranslation[$columnName] = $this->getColumnTranslation($columnName);
            $columnNameToValue[$columnName] = $this->dataTable->getColumn($columnName);
        }
        $this->view->setAxisXLabels($xLabels);
        $this->view->setAxisYValues($columnNameToValue);
        $this->view->setAxisYLabels($columnNameToTranslation);
        $this->view->setAxisYUnit($this->yAxisUnit);
        $this->view->setDisplayPercentageInTooltip($this->displayPercentageInTooltip);

        // show_all_ticks is not real query param, it is set by GenerateGraphHTML.
        if (Piwik_Common::getRequestVar('show_all_ticks', 0) == 1) {
            $this->view->showAllTicks();
        }

        $units = $this->getUnitsForColumnsToDisplay();
        $this->view->setAxisYUnits($units);

        $this->addSeriesPickerToView();
    }
}
