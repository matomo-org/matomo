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
 * Shows a datatable that displays how many occurrences there are of each individual
 * metric type stored in the MySQL database.
 *
 * Goal metrics, metrics of the format .*_[0-9]+ and 'done...' metrics are grouped together.
 */
class GetIndividualMetricsSummary extends Base
{

    protected function init()
    {
        $this->name = Piwik::translate('General_Metrics');
    }

    public function configureView(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view, $addTotalSizeColumn = false, $addPercentColumn = false,
            $sizeColumns = array('estimated_size'));

        $view->requestConfig->filter_sort_order = 'asc';
        $view->config->addTranslation('label', Piwik::translate('General_Metric'));

        $this->setIndividualSummaryFooterMessage($view);
    }

}
