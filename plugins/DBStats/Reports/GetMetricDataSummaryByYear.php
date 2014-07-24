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

/**
 * Shows a datatable that displays the amount of space each numeric archive table
 * takes up in the MySQL database, for each year of numeric data.
 */
class GetMetricDataSummaryByYear extends Base
{
    protected function init()
    {
        $this->name = Piwik::translate('DBStats_MetricDataByYear');
    }

    public function configureView(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view);

        $view->config->title = $this->name;
        $view->config->addTranslation('label', Piwik::translate('CoreHome_PeriodYear'));
    }

    public function getRelatedReports()
    {
        return array(new GetMetricDataSummary());
    }

}
