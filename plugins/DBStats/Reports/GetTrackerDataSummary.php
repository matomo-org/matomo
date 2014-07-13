<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats\Reports;

use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;

/**
 * Shows a datatable that displays the amount of space each individual log table
 * takes up in the MySQL database.
 */
class GetTrackerDataSummary extends Base
{
    public function configureView(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view);

        $view->requestConfig->filter_sort_order = 'asc';
        $view->config->show_offset_information  = false;
        $view->config->show_pagination_control  = false;
    }

}
