<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package DBStats
 */
namespace Piwik\Plugins\DBStats;

use Piwik\Common;

use Piwik\Date;
use Piwik\Menu\MenuAdmin;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;
use Piwik\ScheduledTask;
use Piwik\ScheduledTime\Weekly;

/**
 *
 * @package DBStats
 */
class DBStats extends \Piwik\Plugin
{
    const TIME_OF_LAST_TASK_RUN_OPTION = 'dbstats_time_of_last_cache_task_run';

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles'            => 'getStylesheetFiles',
            'Menu.Admin.addItems'                        => 'addMenu',
            'TaskScheduler.getScheduledTasks'            => 'getScheduledTasks',
            'Visualization.getReportDisplayProperties'   => 'getReportDisplayProperties',
            'Visualization.getDefaultViewTypeForReports' => 'getDefaultViewTypeForReports'
        );
    }

    function addMenu()
    {
        MenuAdmin::getInstance()->add('CoreAdminHome_MenuDiagnostic', 'DBStats_DatabaseUsage',
            array('module' => 'DBStats', 'action' => 'index'),
            Piwik::isUserIsSuperUser(),
            $order = 6);
    }

    /**
     * Gets all scheduled tasks executed by this plugin.
     */
    public function getScheduledTasks(&$tasks)
    {
        $cacheDataByArchiveNameReportsTask = new ScheduledTask(
            $this,
            'cacheDataByArchiveNameReports',
            null,
            new Weekly(),
            ScheduledTask::LOWEST_PRIORITY
        );
        $tasks[] = $cacheDataByArchiveNameReportsTask;
    }

    /**
     * Caches the intermediate DataTables used in the getIndividualReportsSummary and
     * getIndividualMetricsSummary reports in the option table.
     */
    public function cacheDataByArchiveNameReports()
    {
        $api = API::getInstance();
        $api->getIndividualReportsSummary(true);
        $api->getIndividualMetricsSummary(true);

        $now = Date::now()->getLocalized("%longYear%, %shortMonth% %day%");
        Option::set(self::TIME_OF_LAST_TASK_RUN_OPTION, $now);
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

    public function getDefaultViewTypeForReports(&$defaultViewTypes)
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

    public function getReportDisplayProperties(&$properties)
    {
        $properties['DBStats.getDatabaseUsageSummary'] = $this->getDisplayPropertiesForGetDatabaseUsageSummary('DBStats.getDatabaseUsageSummary');
        $properties['DBStats.getTrackerDataSummary'] = $this->getDisplayPropertiesForGetTrackerDataSummary('DBStats.getTrackerDataSummary');
        $properties['DBStats.getMetricDataSummary'] = $this->getDisplayPropertiesForGetMetricDataSummary('DBStats.getMetricDataSummary');
        $properties['DBStats.getMetricDataSummaryByYear'] = $this->getDisplayPropertiesForGetMetricDataSummaryByYear('DBStats.getMetricDataSummaryByYear');
        $properties['DBStats.getReportDataSummary'] = $this->getDisplayPropertiesForGetReportDataSummary('DBStats.getReportDataSummary');
        $properties['DBStats.getReportDataSummaryByYear'] = $this->getDisplayPropertiesForGetReportDataSummaryByYear('DBStats.getReportDataSummaryByYear');
        $properties['DBStats.getIndividualReportsSummary'] = $this->getDisplayPropertiesForGetIndividualReportsSummary('DBStats.getIndividualReportsSummary');
        $properties['DBStats.getIndividualMetricsSummary'] = $this->getDisplayPropertiesForGetIndividualMetricsSummary('DBStats.getIndividualMetricsSummary');
        $properties['DBStats.getAdminDataSummary'] = $this->getDisplayPropertiesForGetAdminDataSummary('DBStats.getAdminDataSummary');
    }

    private function getDefaultViewTypeForApiAction($apiAction)
    {
        $defaultTypes = array();
        $this->getDefaultViewTypeForReports($defaultTypes);

        return $defaultTypes[$apiAction];
    }

    private function getDisplayPropertiesForGetDatabaseUsageSummary($apiAction)
    {
        $result = array();
        $this->addBaseDisplayProperties($result);
        $this->addPresentationFilters($result, $apiAction, $addTotalSizeColumn = true, $addPercentColumn = true);

        $result['show_offset_information'] = false;
        $result['show_pagination_control'] = false;
        $result['visualization_properties']['graph']['show_all_ticks'] = true;

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

        $result['filters'][] = array('ColumnCallbackReplace', array('label', $translateSummaryLabel), $isPriority = true);

        return $result;
    }

    private function getDisplayPropertiesForGetTrackerDataSummary($apiAction)
    {
        $result = array();
        $this->addBaseDisplayProperties($result);
        $this->addPresentationFilters($result, $apiAction);

        $result['filter_sort_order'] = 'asc';
        $result['show_offset_information'] = false;
        $result['show_pagination_control'] = false;

        return $result;
    }

    private function getDisplayPropertiesForGetMetricDataSummary($apiAction)
    {
        $result = array();
        $this->addBaseDisplayProperties($result);
        $this->addPresentationFilters($result, $apiAction);

        $result['title'] = Piwik::translate('DBStats_MetricTables');
        $result['related_reports'] = array(
            'DBStats.getMetricDataSummaryByYear' => Piwik::translate('DBStats_MetricDataByYear')
        );

        return $result;
    }

    private function getDisplayPropertiesForGetMetricDataSummaryByYear($apiAction)
    {
        $result = array();
        $this->addBaseDisplayProperties($result);
        $this->addPresentationFilters($result, $apiAction);

        $result['translations']['label'] = Piwik::translate('CoreHome_PeriodYear');
        $result['title'] = Piwik::translate('DBStats_MetricDataByYear');
        $result['related_reports'] = array(
            'DBStats.getMetricDataSummary' => Piwik::translate('DBStats_MetricTables')
        );

        return $result;
    }

    private function getDisplayPropertiesForGetReportDataSummary($apiAction)
    {
        $result = array();
        $this->addBaseDisplayProperties($result);
        $this->addPresentationFilters($result, $apiAction);

        $result['title'] = Piwik::translate('DBStats_ReportTables');
        $result['related_reports'] = array(
            'DBStats.getReportDataSummaryByYear' => Piwik::translate('DBStats_ReportDataByYear')
        );

        return $result;
    }

    private function getDisplayPropertiesForGetReportDataSummaryByYear($apiAction)
    {
        $result = array();
        $this->addBaseDisplayProperties($result);
        $this->addPresentationFilters($result, $apiAction);

        $result['translations']['label'] = Piwik::translate('CoreHome_PeriodYear');
        $result['title'] = Piwik::translate('DBStats_ReportDataByYear');
        $result['related_reports'] = array(
            'DBStats.getReportDataSummary' => Piwik::translate('DBStats_ReportTables')
        );

        return $result;
    }

    private function getDisplayPropertiesForGetIndividualReportsSummary($apiAction)
    {
        $result = array();
        $this->addBaseDisplayProperties($result);
        $viewDataTable = $this->addPresentationFilters($result, $apiAction, $addTotalSizeColumn = false, $addPercentColumn = false,
            $sizeColumns = array('estimated_size'));

        $result['filter_sort_order'] = 'asc';
        $result['translations']['label'] = Piwik::translate('General_Report');

        // this report table has some extra columns that shouldn't be shown
        if ($viewDataTable == 'table') {
            $result['columns_to_display'] = array('label', 'row_count', 'estimated_size');
        }

        $this->setIndividualSummaryFooterMessage($result);

        return $result;
    }

    private function getDisplayPropertiesForGetIndividualMetricsSummary($apiAction)
    {
        $result = array();
        $this->addBaseDisplayProperties($result);
        $this->addPresentationFilters($result, $apiAction, $addTotalSizeColumn = false, $addPercentColumn = false,
            $sizeColumns = array('estimated_size'));

        $result['filter_sort_order'] = 'asc';
        $result['translations']['label'] = Piwik::translate('General_Metric');

        $this->setIndividualSummaryFooterMessage($result);

        return $result;
    }

    private function getDisplayPropertiesForGetAdminDataSummary($apiAction)
    {
        $result = array();
        $this->addBaseDisplayProperties($result);
        $this->addPresentationFilters($result, $apiAction);

        $result['filter_sort_order'] = 'asc';
        $result['show_offset_information'] = false;
        $result['show_pagination_control'] = false;

        return $result;
    }

    private function addBaseDisplayProperties(&$properties)
    {
        $properties['filter_sort_column'] = 'label';
        $properties['filter_sort_order'] = 'desc';
        $properties['filter_limit'] = 25;
        $properties['show_search'] = false;
        $properties['show_exclude_low_population'] = false;
        $properties['show_tag_cloud'] = false;
        $properties['show_table_all_columns'] = false;
        $properties['visualization_properties']['table']['keep_summary_row'] = true;
        $properties['visualization_properties']['table']['disable_row_evolution'] = true;
        $properties['visualization_properties']['table']['highlight_summary_row'] = true;
        $properties['translations'] = array(
            'label'          => Piwik::translate('DBStats_Table'),
            'year'           => Piwik::translate('CoreHome_PeriodYear'),
            'data_size'      => Piwik::translate('DBStats_DataSize'),
            'index_size'     => Piwik::translate('DBStats_IndexSize'),
            'total_size'     => Piwik::translate('DBStats_TotalSize'),
            'row_count'      => Piwik::translate('DBStats_RowCount'),
            'percent_total'  => '%&nbsp;' . Piwik::translate('DBStats_DBSize'),
            'estimated_size' => Piwik::translate('DBStats_EstimatedSize')
        );
    }

    private function addPresentationFilters(&$properties, $apiAction, $addTotalSizeColumn = true, $addPercentColumn = false,
                                            $sizeColumns = array('data_size', 'index_size'))
    {
        // add total_size column
        if ($addTotalSizeColumn) {
            $getTotalTableSize = function ($dataSize, $indexSize) {
                return $dataSize + $indexSize;
            };

            $properties['filters'][] = array('ColumnCallbackAddColumn',
                                             array(array('data_size', 'index_size'), 'total_size', $getTotalTableSize), $isPriority = true);

            $sizeColumns[] = 'total_size';
        }

        $runPrettySizeFilterBeforeGeneric = false;

        $defaultViewType = $this->getDefaultViewTypeForApiAction($apiAction);
        $viewDataTable   = empty($defaultViewType) ? 'table' : $defaultViewType;
        $viewDataTable   = Common::getRequestVar('viewDataTable', $viewDataTable);

        if ($viewDataTable == 'table') {
            // add summary row only if displaying a table
            $properties['filters'][] = array(
                'AddSummaryRow', array(0, Piwik::translate('General_Total'), 'label', false), $isPriority = true);

            // add percentage column if desired
            if ($addPercentColumn
                && $addTotalSizeColumn
            ) {
                $properties['filters'][] = array('ColumnCallbackAddColumnPercentage',
                                                 array('percent_total', 'total_size', 'total_size', $quotientPrecision = 0,
                                                       $shouldSkipRows = false, $getDivisorFromSummaryRow = true),
                                                 $isPriority = true
                );

                $properties['filter_sort_column'] = 'percent_total';
            }
        } else if (strpos($viewDataTable, 'graph') === 0) {
            if ($addTotalSizeColumn) {
                $properties['columns_to_display'] = array('label', 'total_size');

                // when displaying a graph, we force sizes to be shown as the same unit so axis labels
                // will be readable. NOTE: The unit should depend on the smallest value of the data table,
                // however there's no way to know this information, short of creating a custom filter. For
                // now, just assume KB.
                $fixedMemoryUnit = 'K';
                $properties['y_axis_unit'] = ' K';

                $properties['filter_sort_column'] = 'total_size';
                $properties['filter_sort_order'] = 'desc';

                $runPrettySizeFilterBeforeGeneric = true;
            } else {
                $properties['columns_to_display'] = array('label', 'row_count');
                $properties['y_axis_unit'] = ' ' . Piwik::translate('General_Rows');

                $properties['filter_sort_column'] = 'row_count';
                $properties['filter_sort_order'] = 'desc';
            }
        }

        $getPrettySize = array('\Piwik\MetricsFormatter', 'getPrettySizeFromBytes');
        $params = !isset($fixedMemoryUnit) ? array() : array($fixedMemoryUnit);
        $properties['filters'][] = array(
            'ColumnCallbackReplace', array($sizeColumns, $getPrettySize, $params), $runPrettySizeFilterBeforeGeneric);

        // jqPlot will display &nbsp; as, well, '&nbsp;', so don't replace the spaces when rendering as a graph
        if ($viewDataTable == 'table') {
            $replaceSpaces = function ($value) {
                return str_replace(' ', '&nbsp;', $value);
            };

            $properties['filters'][] = array('ColumnCallbackReplace', array($sizeColumns, $replaceSpaces));
        }

        $getPrettyNumber = array('\Piwik\MetricsFormatter', 'getPrettyNumber');
        $properties['filters'][] = array('ColumnCallbackReplace', array('row_count', $getPrettyNumber));

        return $viewDataTable;
    }

    /**
     * Sets the footer message for the Individual...Summary reports.
     */
    private function setIndividualSummaryFooterMessage($result)
    {
        $lastGenerated = self::getDateOfLastCachingRun();
        if ($lastGenerated !== false) {
            $result['show_footer_message'] = Piwik::translate('Mobile_LastUpdated', $lastGenerated);
        }
    }
}
