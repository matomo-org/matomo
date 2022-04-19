<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

use Piwik\DataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

/**
 * DataTable Visualization that derives from HtmlTable and sets show_extra_columns to true.
 */
class AllColumns extends HtmlTable
{
    const ID = 'tableAllColumns';
    const FOOTER_ICON       = 'icon-table-more';
    const FOOTER_ICON_TITLE = 'General_DisplayTableWithMoreMetrics';

    public function beforeRender()
    {
        $this->config->show_extra_columns  = true;

        parent::beforeRender();
    }

    public function beforeGenericFiltersAreAppliedToLoadedDataTable()
    {
        $this->config->datatable_css_class = 'dataTableVizAllColumns';
        
        $this->dataTable->filter('AddColumnsProcessedMetrics');

        $properties = $this->config;

        $this->dataTable->filter(function (DataTable $dataTable) use ($properties) {
            $columnsToDisplay = array('label', 'nb_visits');

            $columns = $dataTable->getColumns();

            if (in_array('nb_uniq_visitors', $columns)) {
                $columnsToDisplay[] = 'nb_uniq_visitors';
            }

            if (in_array('nb_users', $columns)) {
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

        parent::beforeGenericFiltersAreAppliedToLoadedDataTable();
    }

    public function beforeLoadDataTable()
    {
        parent::beforeLoadDataTable();

        unset($this->requestConfig->request_parameters_to_modify['pivotBy']);
        unset($this->requestConfig->request_parameters_to_modify['pivotByColumn']);
    }

    protected function isPivoted()
    {
        return false; // Pivot not supported
    }
}
