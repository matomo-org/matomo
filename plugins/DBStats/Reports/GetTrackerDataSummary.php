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
 * Shows a datatable that displays the amount of space each individual log table
 * takes up in the MySQL database.
 */
class GetTrackerDataSummary extends Base
{
    protected function init()
    {
        $this->name = Piwik::translate('DBStats_TrackerTables');
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
