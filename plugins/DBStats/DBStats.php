<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats;

use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;

class DBStats extends \Piwik\Plugin
{
    const TIME_OF_LAST_TASK_RUN_OPTION = 'dbstats_time_of_last_cache_task_run';

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'ViewDataTable.configure'         => 'configureViewDataTable',
            'ViewDataTable.getDefaultType'    => 'getDefaultTypeViewDataTable',
            "TestingEnvironment.addHooks"     => 'setupTestEnvironment'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/DBStats/stylesheets/dbStatsTable.less";
    }

    /** Returns the date when the cacheDataByArchiveNameReports was last run. */
    public static function getDateOfLastCachingRun()
    {
        return Option::get(self::TIME_OF_LAST_TASK_RUN_OPTION);
    }

    public function getDefaultTypeViewDataTable(&$defaultViewTypes)
    {
        $defaultViewTypes['DBStats.getDatabaseUsageSummary']     = Pie::ID;
        $defaultViewTypes['DBStats.getTrackerDataSummary']       = HtmlTable::ID;
        $defaultViewTypes['DBStats.getMetricDataSummary']        = HtmlTable::ID;
        $defaultViewTypes['DBStats.getMetricDataSummaryByYear']  = HtmlTable::ID;
        $defaultViewTypes['DBStats.getReportDataSummary']        = HtmlTable::ID;
        $defaultViewTypes['DBStats.getReportDataSummaryByYear']  = HtmlTable::ID;
        $defaultViewTypes['DBStats.getIndividualReportsSummary'] = HtmlTable::ID;
        $defaultViewTypes['DBStats.getIndividualMetricsSummary'] = HtmlTable::ID;
        $defaultViewTypes['DBStats.getAdminDataSummary']         = HtmlTable::ID;
    }

    public function configureViewDataTable(ViewDataTable $view)
    {
        switch ($view->requestConfig->apiMethodToRequestDataTable) {
            case 'DBStats.getDatabaseUsageSummary':
                $this->configureViewForGetDatabaseUsageSummary($view);
                break;
            case 'DBStats.getTrackerDataSummary':
                $this->configureViewForGetTrackerDataSummary($view);
                break;
            case 'DBStats.getMetricDataSummary':
                $this->configureViewForGetMetricDataSummary($view);
                break;
            case 'DBStats.getMetricDataSummaryByYear':
                $this->configureViewForGetMetricDataSummaryByYear($view);
                break;
            case 'DBStats.getReportDataSummary':
                $this->configureViewForGetReportDataSummary($view);
                break;
            case 'DBStats.getReportDataSummaryByYear':
                $this->configureViewForGetReportDataSummaryByYear($view);
                break;
            case 'DBStats.getIndividualReportsSummary':
                $this->configureViewForGetIndividualReportsSummary($view);
                break;
            case 'DBStats.getIndividualMetricsSummary':
                $this->configureViewForGetIndividualMetricsSummary($view);
                break;
            case 'DBStats.getAdminDataSummary':
                $this->configureViewForGetAdminDataSummary($view);
                break;
        }
    }

    private function configureViewForGetDatabaseUsageSummary(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view, $addTotalSizeColumn = true, $addPercentColumn = true);

        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->show_all_ticks = true;
        }

        // translate the labels themselves
        $valueToTranslationStr = array(
            'tracker_data' => 'DBStats_TrackerTables',
            'report_data'  => 'DBStats_ReportTables',
            'metric_data'  => 'DBStats_MetricTables',
            'other_data'   => 'DBStats_OtherTables'
        );

        $translateSummaryLabel = function ($value) use ($valueToTranslationStr) {
            return isset($valueToTranslationStr[$value])
                ? Piwik::translate($valueToTranslationStr[$value])
                : $value;
        };

        $view->config->filters[] = array('ColumnCallbackReplace',
                                         array(
                                             'label',
                                             $translateSummaryLabel
                                         ),
                                         $isPriority = true
        );
    }

    private function configureViewForGetTrackerDataSummary(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view);

        $view->requestConfig->filter_sort_order = 'asc';
        $view->config->show_offset_information  = false;
        $view->config->show_pagination_control  = false;
    }

    private function configureViewForGetMetricDataSummary(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view);

        $view->config->title = Piwik::translate('DBStats_MetricTables');
        $view->config->addRelatedReports(array(
            'DBStats.getMetricDataSummaryByYear' => Piwik::translate('DBStats_MetricDataByYear')
        ));
    }

    private function configureViewForGetMetricDataSummaryByYear(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view);

        $view->config->title = Piwik::translate('DBStats_MetricDataByYear');
        $view->config->addTranslation('label', Piwik::translate('CoreHome_PeriodYear'));
        $view->config->addRelatedReports(array(
            'DBStats.getMetricDataSummary' => Piwik::translate('DBStats_MetricTables')
        ));
    }

    private function configureViewForGetReportDataSummary(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view);

        $view->config->title = Piwik::translate('DBStats_ReportTables');
        $view->config->addRelatedReports(array(
            'DBStats.getReportDataSummaryByYear' => Piwik::translate('DBStats_ReportDataByYear')
        ));
    }

    private function configureViewForGetReportDataSummaryByYear(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view);

        $view->config->title = Piwik::translate('DBStats_ReportDataByYear');
        $view->config->addTranslation('label', Piwik::translate('CoreHome_PeriodYear'));
        $view->config->addRelatedReports(array(
            'DBStats.getReportDataSummary' => Piwik::translate('DBStats_ReportTables')
        ));
    }

    private function configureViewForGetIndividualReportsSummary(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view, $addTotalSizeColumn = false, $addPercentColumn = false,
                                     $sizeColumns = array('estimated_size'));

        $view->requestConfig->filter_sort_order = 'asc';
        $view->config->addTranslation('label', Piwik::translate('General_Report'));

        // this report table has some extra columns that shouldn't be shown
        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->columns_to_display = array('label', 'row_count', 'estimated_size');
        }

        $this->setIndividualSummaryFooterMessage($view);
    }

    private function configureViewForGetIndividualMetricsSummary(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view, $addTotalSizeColumn = false, $addPercentColumn = false,
            $sizeColumns = array('estimated_size'));

        $view->requestConfig->filter_sort_order = 'asc';
        $view->config->addTranslation('label', Piwik::translate('General_Metric'));

        $this->setIndividualSummaryFooterMessage($view);
    }

    private function configureViewForGetAdminDataSummary(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view);

        $view->requestConfig->filter_sort_order = 'asc';
        $view->config->show_offset_information  = false;
        $view->config->show_pagination_control  = false;
    }

    private function addBaseDisplayProperties(ViewDataTable $view)
    {
        $view->requestConfig->filter_sort_column   = 'label';
        $view->requestConfig->filter_sort_order    = 'desc';
        $view->requestConfig->filter_limit         = 25;

        $view->config->show_exclude_low_population = false;
        $view->config->show_table_all_columns      = false;
        $view->config->show_tag_cloud = false;
        $view->config->show_search    = false;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->keep_summary_row      = true;
            $view->config->disable_row_evolution = true;
            $view->config->highlight_summary_row = true;
        }

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->show_series_picker = false;
        }

        $view->config->addTranslations(array(
            'label'          => Piwik::translate('DBStats_Table'),
            'year'           => Piwik::translate('CoreHome_PeriodYear'),
            'data_size'      => Piwik::translate('DBStats_DataSize'),
            'index_size'     => Piwik::translate('DBStats_IndexSize'),
            'total_size'     => Piwik::translate('DBStats_TotalSize'),
            'row_count'      => Piwik::translate('DBStats_RowCount'),
            'percent_total'  => '%&nbsp;' . Piwik::translate('DBStats_DBSize'),
            'estimated_size' => Piwik::translate('DBStats_EstimatedSize')
        ));
    }

    private function addPresentationFilters(ViewDataTable $view, $addTotalSizeColumn = true, $addPercentColumn = false,
                                            $sizeColumns = array('data_size', 'index_size'))
    {
        // add total_size column
        if ($addTotalSizeColumn) {
            $getTotalTableSize = function ($dataSize, $indexSize) {
                return $dataSize + $indexSize;
            };

            $view->config->filters[] = array('ColumnCallbackAddColumn',
                                             array(
                                                 array('data_size', 'index_size'),
                                                 'total_size',
                                                 $getTotalTableSize
                                             ),
                                             $isPriority = true
            );

            $sizeColumns[] = 'total_size';
        }

        $runPrettySizeFilterBeforeGeneric = false;

        if ($view->isViewDataTableId(HtmlTable::ID)) {

            // add summary row only if displaying a table
            $view->config->filters[] = array('AddSummaryRow', Piwik::translate('General_Total'));

            // add percentage column if desired
            if ($addPercentColumn
                && $addTotalSizeColumn
            ) {
                $view->config->filters[] = array('ColumnCallbackAddColumnPercentage',
                                                 array(
                                                     'percent_total',
                                                     'total_size',
                                                     'total_size',
                                                     $quotientPrecision = 0,
                                                     $shouldSkipRows = false,
                                                     $getDivisorFromSummaryRow = true
                                                 ),
                                                 $isPriority = false
                );

                $view->requestConfig->filter_sort_column = 'percent_total';
            }

        } else if ($view->isViewDataTableId(Graph::ID)) {
            if ($addTotalSizeColumn) {
                $view->config->columns_to_display = array('label', 'total_size');

                // when displaying a graph, we force sizes to be shown as the same unit so axis labels
                // will be readable. NOTE: The unit should depend on the smallest value of the data table,
                // however there's no way to know this information, short of creating a custom filter. For
                // now, just assume KB.
                $fixedMemoryUnit = 'K';
                $view->config->y_axis_unit = ' K';
                $view->requestConfig->filter_sort_column = 'total_size';
                $view->requestConfig->filter_sort_order  = 'desc';

                $runPrettySizeFilterBeforeGeneric = true;
            } else {
                $view->config->columns_to_display = array('label', 'row_count');
                $view->config->y_axis_unit        = ' ' . Piwik::translate('General_Rows');

                $view->requestConfig->filter_sort_column = 'row_count';
                $view->requestConfig->filter_sort_order  = 'desc';
            }
            $view->config->selectable_rows = array();
        }

        $getPrettySize = array('\Piwik\MetricsFormatter', 'getPrettySizeFromBytes');
        $params        = !isset($fixedMemoryUnit) ? array() : array($fixedMemoryUnit);

        $view->config->filters[] = array('ColumnCallbackReplace', array($sizeColumns, $getPrettySize, $params), $runPrettySizeFilterBeforeGeneric);

        // jqPlot will display &nbsp; as, well, '&nbsp;', so don't replace the spaces when rendering as a graph
        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $replaceSpaces = function ($value) {
                return str_replace(' ', '&nbsp;', $value);
            };

            $view->config->filters[] = array('ColumnCallbackReplace', array($sizeColumns, $replaceSpaces));
        }

        $getPrettyNumber = array('\Piwik\MetricsFormatter', 'getPrettyNumber');
        $view->config->filters[] = array('ColumnCallbackReplace', array('row_count', $getPrettyNumber));
    }

    /**
     * Sets the footer message for the Individual...Summary reports.
     */
    private function setIndividualSummaryFooterMessage(ViewDataTable $view)
    {
        $lastGenerated = self::getDateOfLastCachingRun();
        if ($lastGenerated !== false) {
            $view->config->show_footer_message = Piwik::translate('Mobile_LastUpdated', $lastGenerated);
        }
    }

    public function setupTestEnvironment($environment)
    {
        Piwik::addAction("MySQLMetadataProvider.createDao", function (&$dao) {
            require_once dirname(__FILE__) . "/tests/Mocks/MockDataAccess.php";
            $dao = new Mocks\MockDataAccess();
        });
    }
}
