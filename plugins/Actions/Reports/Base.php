<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Reports;

use Piwik\Common;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\Actions;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\API\Request;

abstract class Base extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->categoryId = 'General_Actions';
        $this->processedMetrics = false;
        $this->recursiveLabelSeparator = '/';
    }

    protected function addBaseDisplayProperties(ViewDataTable $view)
    {
        $view->config->datatable_js_type      = 'ActionsDataTable';
        $view->config->search_recursive       = true;
        $view->config->show_table_all_columns = false;
        $view->requestConfig->filter_limit    = Actions::ACTIONS_REPORT_ROWS_DISPLAY;
        $view->config->show_all_views_icons = false;

        if ($view->requestConfig->getRequestParam('performance') === '1') {
            $view->requestConfig->filter_limit = 25;
            // hide visualization selector
            $view->config->footer_icons = [[
                'class'   => 'tableAllColumnsSwitch',
                'buttons' => [],
            ]];
        }

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->show_embedded_subtable = true;
        }

        if (Request::shouldLoadExpanded()) {

            if ($view->isViewDataTableId(HtmlTable::ID)) {
                $view->config->show_expanded = true;
            }

            $view->config->filters[] = function ($dataTable) {
                Actions::setDataTableRowLevels($dataTable);
            };
        }

        $view->config->filters[] = function ($dataTable) use ($view) {
            if ($view->isViewDataTableId(HtmlTable::ID)) {
                $view->config->datatable_css_class = 'dataTableActions';
            }
        };
    }

    protected function addPageDisplayProperties(ViewDataTable $view)
    {
        $view->config->addTranslations(array(
            'nb_hits'             => Piwik::translate('General_ColumnPageviews'),
            'nb_visits'           => Piwik::translate('General_ColumnUniquePageviews')
        ));

        $formatter = new Formatter();

        // add avg_generation_time tooltip
        $tooltipCallback = function ($hits, $min, $max) use ($formatter) {
            if (!$hits) {
                return false;
            }

            return Piwik::translate("Actions_AvgGenerationTimeTooltip", array(
                $hits,
                "<br />",
                $formatter->getPrettyTimeFromSeconds($min, true),
                $formatter->getPrettyTimeFromSeconds($max, true)
            ));
        };
        $view->config->filters[] = array('ColumnCallbackAddMetadata',
            array(
                array('nb_hits_with_time_generation', 'min_time_generation', 'max_time_generation'),
                'avg_time_generation_tooltip',
                $tooltipCallback
            )
        );

        $this->addExcludeLowPopDisplayProperties($view);

        // hide the performance columns viz in page reports when not displayed as widget
        if ($view->requestConfig->getRequestParam('widget') != '1') {
            $view->config->show_table_performance = false;
        }
    }

    protected function addExcludeLowPopDisplayProperties(ViewDataTable $view)
    {
        if (Common::getRequestVar('enable_filter_excludelowpop', '0', 'string') != '0') {
            if (Common::getRequestVar('flat', 0, 'int') === 1) {
                $view->requestConfig->filter_excludelowpop = 'nb_hits';
            } else {
                $view->requestConfig->filter_excludelowpop = Metrics::INDEX_PAGE_NB_HITS;
            }
            $view->requestConfig->filter_excludelowpop_value = function () {
                // computing minimum value to exclude (2 percent of the total number of actions)
                $visitsInfo = \Piwik\Plugins\VisitsSummary\Controller::getVisitsSummary()->getFirstRow();
                $nbActions = $visitsInfo->getColumn('nb_actions');
                $nbActionsLowPopulationThreshold = floor(0.02 * $nbActions);

                // we remove 1 to make sure some actions/downloads are displayed in the case we have a very few of them
                // and each of them has 1 or 2 hits...
                return min($visitsInfo->getColumn('max_actions') - 1, $nbActionsLowPopulationThreshold - 1);
            };
        }
    }
}
