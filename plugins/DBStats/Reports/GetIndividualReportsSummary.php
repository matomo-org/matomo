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
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

/**
 * Shows a datatable that displays how many occurrences there are of each individual
 * report type stored in the MySQL database.
 *
 * Goal reports and reports of the format: .*_[0-9]+ are grouped together.
 */
class GetIndividualReportsSummary extends Base
{

    protected function init()
    {
        $this->name = Piwik::translate('General_Reports');
    }

    public function configureView(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view, $addTotalSizeColumn = false, $addPercentColumn = false,
            $sizeColumns = array('estimated_size'));

        $view->requestConfig->filter_sort_order = 'asc';
        $view->config->addTranslation('label', Piwik::translate('General_Report'));

        // this report table has some extra columns that shouldn't be shown
        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->columns_to_display = array('label', 'row_count', 'estimated_size');
        }

        $this->setIndividualSummaryFooterMessage($view);
    }

}
