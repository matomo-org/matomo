<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreVisualizations
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

use Piwik\DataTable\DataTableInterface;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Visualization\Config;
use Piwik\Common;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Site;
use Piwik\View;
use Piwik\ViewDataTable\Visualization;
use Piwik\Visualization\Request;

/**
 * DataTable Visualization that derives from HtmlTable and sets show_extra_columns to true.
 */
class AllColumns extends HtmlTable
{
    const ID = 'tableAllColumns';

    public function configureVisualization(Config $properties)
    {
        $properties->visualization_properties->show_extra_columns = true;

        $properties->show_exclude_low_population = true;
        $properties->datatable_css_class = 'dataTableVizAllColumns';

        parent::configureVisualization($properties);
    }

    public function beforeGenericFiltersAreAppliedToLoadedDataTable(DataTableInterface $dataTable, Config $properties, Request $request)
    {
        $dataTable->filter('AddColumnsProcessedMetrics');

        $dataTable->filter(function ($dataTable) use ($properties) {
            $columnsToDisplay = array('label', 'nb_visits');

            if (in_array('nb_uniq_visitors', $dataTable->getColumns())) {
                $columnsToDisplay[] = 'nb_uniq_visitors';
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

        $prettifyTime = array('\Piwik\MetricsFormatter', 'getPrettyTimeFromSeconds');

        $dataTable->filter('ColumnCallbackReplace', array('avg_time_on_site', $prettifyTime));
    }

}