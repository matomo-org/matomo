<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_DBStats
 */

/**
 *
 * @package Piwik_DBStats
 */
class Piwik_DBStats_Controller extends Piwik_Controller_Admin
{
    /**
     * Returns the index for this plugin. Shows every other report defined by this plugin,
     * except the '...ByYear' reports. These can be loaded as related reports.
     *
     * Also, the 'getIndividual...Summary' reports are loaded by AJAX, as they can take
     * a significant amount of time to load on setups w/ lots of websites.
     */
    public function index()
    {
        Piwik::checkUserIsSuperUser();
        $view = Piwik_View::factory('index');
        $this->setBasicVariablesView($view);
        $view->menu = Piwik_GetAdminMenu();

        $view->databaseUsageSummary = $this->getDatabaseUsageSummary(true);
        $view->trackerDataSummary = $this->getTrackerDataSummary(true);
        $view->metricDataSummary = $this->getMetricDataSummary(true);
        $view->reportDataSummary = $this->getReportDataSummary(true);
        $view->adminDataSummary = $this->getAdminDataSummary(true);

        list($siteCount, $userCount, $totalSpaceUsed) = Piwik_DBStats_API::getInstance()->getGeneralInformation();
        $view->siteCount = Piwik::getPrettyNumber($siteCount);
        $view->userCount = Piwik::getPrettyNumber($userCount);
        $view->totalSpaceUsed = Piwik::getPrettySizeFromBytes($totalSpaceUsed);

        echo $view->render();
    }

    /**
     * Shows a datatable that displays how much space the tracker tables, numeric
     * archive tables, report tables and other tables take up in the MySQL database.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     */
    public function getDatabaseUsageSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();

        $view = $this->getDataTableView(__FUNCTION__, $viewType = 'graphPie', $orderDir = 'desc',
            $addPercentColumn = true);
        $view->disableOffsetInformationAndPaginationControls();

        if ($view instanceof Piwik_ViewDataTable_GenerateGraphHTML) {
            $view->showAllTicks();
        }

        // translate the labels themselves
        $translateSummaryLabel = array($this, 'translateSummarylabel');
        $view->queueFilter('ColumnCallbackReplace', array(array('label'), $translateSummaryLabel),
            $runBeforeGenericFilters = true);

        return $this->renderView($view, $fetch);
    }

    /**
     * Shows a datatable that displays the amount of space each individual log table
     * takes up in the MySQL database.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     */
    public function getTrackerDataSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();

        $view = $this->getDataTableView(__FUNCTION__);
        $view->disableOffsetInformationAndPaginationControls();
        return $this->renderView($view, $fetch);
    }

    /**
     * Shows a datatable that displays the amount of space each numeric archive table
     * takes up in the MySQL database.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     */
    public function getMetricDataSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        $view = $this->getDataTableView(__FUNCTION__, $viewType = 'table', $orderDir = 'desc');
        $view->addRelatedReports(Piwik_Translate('DBStats_MetricTables'), array(
                                                                               'DBStats.getMetricDataSummaryByYear' => Piwik_Translate('DBStats_MetricDataByYear')
                                                                          ));
        return $this->renderView($view, $fetch);
    }

    /**
     * Shows a datatable that displays the amount of space each numeric archive table
     * takes up in the MySQL database, for each year of numeric data.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     */
    public function getMetricDataSummaryByYear($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        $view = $this->getDataTableView(__FUNCTION__, $viewType = 'table', $orderDir = 'desc',
            $addPercentColumn = false, $labelKey = 'CoreHome_PeriodYear');
        $view->addRelatedReports(Piwik_Translate('DBStats_MetricDataByYear'), array(
                                                                                   'DBStats.getMetricDataSummary' => Piwik_Translate('DBStats_MetricTables')
                                                                              ));
        return $this->renderView($view, $fetch);
    }

    /**
     * Shows a datatable that displays the amount of space each blob archive table
     * takes up in the MySQL database.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     */
    public function getReportDataSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        $view = $this->getDataTableView(__FUNCTION__, $viewType = 'table', $orderDir = 'desc');
        $view->addRelatedReports(Piwik_Translate('DBStats_ReportTables'), array(
                                                                               'DBStats.getReportDataSummaryByYear' => Piwik_Translate('DBStats_ReportDataByYear')
                                                                          ));
        return $this->renderView($view, $fetch);
    }

    /**
     * Shows a datatable that displays the amount of space each blob archive table
     * takes up in the MySQL database, for each year of blob data.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     */
    public function getReportDataSummaryByYear($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        $view = $this->getDataTableView(__FUNCTION__, $viewType = 'table', $orderDir = 'desc',
            $addPercentColumn = false, $labelKey = 'CoreHome_PeriodYear');
        $view->addRelatedReports(Piwik_Translate('DBStats_ReportDataByYear'), array(
                                                                                   'DBStats.getReportDataSummary' => Piwik_Translate('DBStats_ReportTables')
                                                                              ));
        return $this->renderView($view, $fetch);
    }

    /**
     * Shows a datatable that displays how many occurances there are of each individual
     * report type stored in the MySQL database.
     *
     * Goal reports and reports of the format: .*_[0-9]+ are grouped together.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     */
    public function getIndividualReportsSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        $view = $this->getDataTableView(__FUNCTION__, $viewType = 'table', $orderDir = 'asc',
            $addPercentColumn = false, $labelKey = 'General_Report',
            $sizeColumns = array('estimated_size'));

        // this report table has some extra columns that shouldn't be shown
        if ($view instanceof Piwik_ViewDataTable_HtmlTable) {
            $view->setColumnsToDisplay(array('label', 'row_count', 'estimated_size'));
        }

        $this->setIndividualSummaryFooterMessage($view);

        return $this->renderView($view, $fetch);
    }

    /**
     * Shows a datatable that displays how many occurances there are of each individual
     * metric type stored in the MySQL database.
     *
     * Goal metrics, metrics of the format .*_[0-9]+ and 'done...' metrics are grouped together.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     */
    public function getIndividualMetricsSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        $view = $this->getDataTableView(__FUNCTION__, $viewType = 'table', $orderDir = 'asc',
            $addPercentColumn = false, $labelKey = 'General_Metric',
            $sizeColumns = array('estimated_size'));

        $this->setIndividualSummaryFooterMessage($view);

        return $this->renderView($view, $fetch);
    }

    /**
     * Shows a datatable that displays the amount of space each 'admin' table takes
     * up in the MySQL database.
     *
     * An 'admin' table is a table that is not central to analytics functionality.
     * So any table that isn't an archive table or a log table is an 'admin' table.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     */
    public function getAdminDataSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        $view = $this->getDataTableView(__FUNCTION__, $viewType = 'table');
        $view->disableOffsetInformationAndPaginationControls();
        return $this->renderView($view, $fetch);
    }

    /**
     * Utility function that creates and prepares a ViewDataTable for this plugin.
     */
    private function getDataTableView($function, $viewType = 'table', $orderDir = 'asc', $addPercentColumn = false,
                                      $labelKey = 'DBStats_Table', $sizeColumns = array('data_size', 'index_size'),
                                      $limit = 25)
    {
        $columnTranslations = array(
            'label'          => Piwik_Translate($labelKey),
            'year'           => Piwik_Translate('CoreHome_PeriodYear'),
            'data_size'      => Piwik_Translate('DBStats_DataSize'),
            'index_size'     => Piwik_Translate('DBStats_IndexSize'),
            'total_size'     => Piwik_Translate('DBStats_TotalSize'),
            'row_count'      => Piwik_Translate('DBStats_RowCount'),
            'percent_total'  => '%&nbsp;' . Piwik_Translate('DBStats_DBSize'),
            'estimated_size' => Piwik_Translate('DBStats_EstimatedSize')
        );

        $view = Piwik_ViewDataTable::factory($viewType);
        $view->init($this->pluginName, $function, "DBStats.$function");
        $view->setSortedColumn('label', $orderDir);
        $view->setLimit($limit);
        $view->setHighlightSummaryRow(true);
        $view->disableSearchBox();
        $view->disableExcludeLowPopulation();
        $view->disableTagCloud();
        $view->disableShowAllColumns();
        $view->alwaysShowSummaryRow();

        // translate columns
        foreach ($columnTranslations as $columnName => $translation) {
            $view->setColumnTranslation($columnName, $translation);
        }

        // add total_size column (if necessary columns are present)
        if (in_array('data_size', $sizeColumns) && in_array('index_size', $sizeColumns)) {
            $getTotalTableSize = array($this, 'getTotalTableSize');
            $view->queueFilter('ColumnCallbackAddColumn',
                array(array('data_size', 'index_size'), 'total_size', $getTotalTableSize),
                $runBeforeGenericFilters = true);

            $sizeColumns[] = 'total_size';
        }

        $runPrettySizeFilterBeforeGeneric = false;
        $fixedMemoryUnit = false;
        if ($view instanceof Piwik_ViewDataTable_HtmlTable) // if displaying a table
        {
            $view->disableRowEvolution();

            // add summary row only if displaying a table
            $view->queueFilter('AddSummaryRow', array(0, Piwik_Translate('General_Total'), 'label', false),
                $runBeforeGenericFilters = true);

            // add other filters
            if ($addPercentColumn && in_array('total_size', $sizeColumns)) {
                $view->queueFilter('ColumnCallbackAddColumnPercentage',
                    array('percent_total', 'total_size', 'total_size', $quotientPrecision = 0, $shouldSkipRows = false,
                          $getDivisorFromSummaryRow = true),
                    $runBeforeGenericFilters = true);
                $view->setSortedColumn('percent_total', $orderDir);
            }
        } else if ($view instanceof Piwik_ViewDataTable_GenerateGraphData) // if displaying a graph
        {
            if (in_array('total_size', $sizeColumns)) {
                $view->setColumnsToDisplay(array('label', 'total_size'));

                // when displaying a graph, we force sizes to be shown as the same unit so axis labels
                // will be readable. NOTE: The unit should depend on the smallest value of the data table,
                // however there's no way to know this information, short of creating a custom filter. For
                // now, just assume KB.
                $fixedMemoryUnit = 'K';
                $view->setAxisYUnit(' K');

                $view->setSortedColumn('total_size', 'desc');

                $runPrettySizeFilterBeforeGeneric = true;
            } else {
                $view->setColumnsToDisplay(array('label', 'row_count'));
                $view->setAxisYUnit(' ' . Piwik_Translate('General_Rows'));

                $view->setSortedColumn('row_count', 'desc');
            }
        }

        $getPrettySize = array('Piwik', 'getPrettySizeFromBytes');
        $params = $fixedMemoryUnit === false ? array() : array($fixedMemoryUnit);
        $view->queueFilter(
            'ColumnCallbackReplace', array($sizeColumns, $getPrettySize, $params), $runPrettySizeFilterBeforeGeneric);

        // jqPlot will display &nbsp; as, well, '&nbsp;', so don't replace the spaces when rendering as a graph
        if (!($view instanceof Piwik_ViewDataTable_GenerateGraphData)) {
            $replaceSpaces = array($this, 'replaceColumnSpaces');
            $view->queueFilter('ColumnCallbackReplace', array($sizeColumns, $replaceSpaces));
        }

        $getPrettyNumber = array('Piwik', 'getPrettyNumber');
        $view->queueFilter('ColumnCallbackReplace', array(array('row_count'), $getPrettyNumber));

        return $view;
    }

    /**
     * Replaces spaces w/ &nbsp; for correct HTML output.
     */
    public function replaceColumnSpaces($value)
    {
        return str_replace(' ', '&nbsp;', $value);
    }

    /**
     * Row callback function that calculates a tables total size.
     */
    public function getTotalTableSize($dataSize, $indexSize)
    {
        return $dataSize + $indexSize;
    }

    /**
     * Column callback used to translate the column values in the database usage summary table.
     */
    public function translateSummarylabel($value)
    {
        static $valueToTranslationStr = array(
            'tracker_data' => 'DBStats_TrackerTables',
            'report_data'  => 'DBStats_ReportTables',
            'metric_data'  => 'DBStats_MetricTables',
            'other_data'   => 'DBStats_OtherTables'
        );

        return isset($valueToTranslationStr[$value])
            ? Piwik_Translate($valueToTranslationStr[$value])
            : $value;
    }

    /**
     * Sets the footer message for the Individual...Summary reports.
     */
    private function setIndividualSummaryFooterMessage($view)
    {
        $lastGenerated = Piwik_DBStats::getDateOfLastCachingRun();
        if ($lastGenerated !== false) {
            $view->setFooterMessage(Piwik_Translate('Mobile_LastUpdated', $lastGenerated));
        }
    }
}
