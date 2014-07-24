<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

/**
 * Shows a datatable that displays how many occurances there are of each individual
 * metric type stored in the MySQL database.
 *
 * Goal metrics, metrics of the format .*_[0-9]+ and 'done...' metrics are grouped together.
 */
class GetIndividualMetricsSummary extends Base
{
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
