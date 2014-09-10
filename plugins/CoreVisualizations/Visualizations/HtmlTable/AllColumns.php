<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\View;

/**
 * DataTable Visualization that derives from HtmlTable and sets show_extra_columns to true.
 */
class AllColumns extends HtmlTable
{
    const ID = 'tableAllColumns';
    const FOOTER_ICON       = 'plugins/Morpheus/images/table_more.png';
    const FOOTER_ICON_TITLE = 'General_DisplayTableWithMoreMetrics';

    public function beforeRender()
    {
        $this->config->show_extra_columns  = true;
        $this->config->datatable_css_class = 'dataTableVizAllColumns';
        $this->config->show_exclude_low_population = true;

        parent::beforeRender();
    }

    public function beforeGenericFiltersAreAppliedToLoadedDataTable()
    {
        $this->dataTable->filter('AddColumnsProcessedMetrics');

        $properties = $this->config;

        $this->dataTable->filter(function ($dataTable) use ($properties) {
            $columnsToDisplay = array('label', 'nb_visits');

            if (in_array('nb_uniq_visitors', $dataTable->getColumns())) {
                $columnsToDisplay[] = 'nb_uniq_visitors';
            }

            if (in_array('nb_users', $dataTable->getColumns())) {
                $columnsToDisplay[] = 'nb_users';
            }

            $columnsToDisplay = array_merge(
                $columnsToDisplay, array('nb_actions', 'nb_actions_per_visit', 'avg_time_on_site', 'bounce_rate')
            );

            // only display conversion rate for the plugins that do not provide "per goal" metrics
            // otherwise, conversion rate is meaningless as a whole (since we don't process 'cross goals' conversions)
            if (!$properties->show_goals) {
                $columnsToDisplay[] = 'conversion_rate';
            }

            $properties->columns_to_display = $columnsToDisplay;
        });
    }

    public function afterGenericFiltersAreAppliedToLoadedDataTable()
    {
        $prettifyTime = array('\Piwik\MetricsFormatter', 'getPrettyTimeFromSeconds');

        $this->dataTable->filter('ColumnCallbackReplace', array('avg_time_on_site', $prettifyTime));
    }
}
