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
 * Shows a datatable that displays the amount of space each 'admin' table takes
 * up in the MySQL database.
 *
 * An 'admin' table is a table that is not central to analytics functionality.
 * So any table that isn't an archive table or a log table is an 'admin' table.
 */
class GetAdminDataSummary extends Base
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
