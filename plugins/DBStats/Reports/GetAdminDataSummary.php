<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;

/**
 * Shows a datatable that displays the amount of space each 'admin' table takes
 * up in the MySQL database.
 *
 * An 'admin' table is a table that is not central to analytics functionality.
 * So any table that isn't an archive table or a log table is an 'admin' table.
 */
class GetAdminDataSummary extends Base
{

    protected function init()
    {
        $this->name = Piwik::translate('DBStats_OtherTables');
    }

    public function configureView(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view);

        $view->requestConfig->filter_sort_order = 'asc';
        $view->config->show_offset_information  = false;
        $view->config->show_pagination_control  = false;
    }
}
