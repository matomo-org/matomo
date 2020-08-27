<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats\Reports;

use Piwik\Metrics\Formatter;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\DBStats\DBStats;

abstract class Base extends \Piwik\Plugin\Report
{
    public function isEnabled()
    {
        return Piwik::hasUserSuperUserAccess();
    }

    public function configureReportMetadata(&$availableReports, $info)
    {
        // DBStats is not supposed to appear in report metadata
    }

    protected function addBaseDisplayProperties(ViewDataTable $view)
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
            'year'           => Piwik::translate('Intl_PeriodYear'),
            'data_size'      => Piwik::translate('DBStats_DataSize'),
            'index_size'     => Piwik::translate('DBStats_IndexSize'),
            'total_size'     => Piwik::translate('DBStats_TotalSize'),
            'row_count'      => Piwik::translate('DBStats_RowCount'),
            'percent_total'  => '%&nbsp;' . Piwik::translate('DBStats_DBSize'),
            'estimated_size' => Piwik::translate('DBStats_EstimatedSize')
        ));
    }

    protected function addPresentationFilters(ViewDataTable $view, $addTotalSizeColumn = true, $addPercentColumn = false,
                                              $sizeColumns = array('data_size', 'index_size'))
    {
        // add total_size column
        if ($addTotalSizeColumn) {
            $getTotalTableSize = function ($dataSize, $indexSize) {
                return $dataSize + $indexSize;
            };

            $view->config->filters[] = array('ColumnCallbackAddColumn',
                array(array('data_size', 'index_size'), 'total_size', $getTotalTableSize), $isPriority = true);

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
                $view->config->filters[] = array(
                    'ColumnCallbackAddColumnPercentage',
                    array('percent_total', 'total_size', 'total_size', $quotientPrecision = 0,
                          $shouldSkipRows = false, $getDivisorFromSummaryRow = true),
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
            } else {
                $view->config->columns_to_display = array('label', 'row_count');
                $view->config->y_axis_unit        = ' ' . Piwik::translate('General_Rows');

                $view->requestConfig->filter_sort_column = 'row_count';
                $view->requestConfig->filter_sort_order  = 'desc';
            }
            $view->config->selectable_rows = array();
        }

        $formatter = new Formatter();

        $getPrettySize = array($formatter, 'getPrettySizeFromBytes');
        $params        = !isset($fixedMemoryUnit) ? array() : array($fixedMemoryUnit);

        $view->config->filters[] = function ($dataTable) use ($sizeColumns, $getPrettySize, $params) {
            $dataTable->filter('ColumnCallbackReplace', array($sizeColumns, $getPrettySize, $params));
        };

        // jqPlot will display &nbsp; as, well, '&nbsp;', so don't replace the spaces when rendering as a graph
        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $replaceSpaces = function ($value) {
                return str_replace(' ', '&nbsp;', $value);
            };

            $view->config->filters[] = array('ColumnCallbackReplace', array($sizeColumns, $replaceSpaces));
        }

        $getPrettyNumber = array($formatter, 'getPrettyNumber');
        $view->config->filters[] = array('ColumnCallbackReplace', array('row_count', $getPrettyNumber));
    }

    /**
     * Sets the footer message for the Individual...Summary reports.
     */
    protected function setIndividualSummaryFooterMessage(ViewDataTable $view)
    {
        $lastGenerated = self::getDateOfLastCachingRun();
        if ($lastGenerated !== false) {
            $view->config->show_footer_message = Piwik::translate('Mobile_LastUpdated', $lastGenerated);
        }
    }

    /** Returns the date when the cacheDataByArchiveNameReports was last run. */
    private static function getDateOfLastCachingRun()
    {
        return Option::get(DBStats::TIME_OF_LAST_TASK_RUN_OPTION);
    }
}
