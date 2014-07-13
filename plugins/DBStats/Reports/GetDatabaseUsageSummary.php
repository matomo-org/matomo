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
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;

/**
 * Shows a datatable that displays how much space the tracker tables, numeric
 * archive tables, report tables and other tables take up in the MySQL database.
 */
class GetDatabaseUsageSummary extends Base
{
    public function getDefaultTypeViewDataTable()
    {
        return Pie::ID;
    }

    public function configureView(ViewDataTable $view)
    {
        $this->addBaseDisplayProperties($view);
        $this->addPresentationFilters($view, $addTotalSizeColumn = true, $addPercentColumn = true);

        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->show_all_ticks = true;
        }

        // translate the labels themselves
        $valueToTranslationStr = array(
            'tracker_data' => 'DBStats_TrackerTables',
            'report_data'  => 'DBStats_ReportTables',
            'metric_data'  => 'DBStats_MetricTables',
            'other_data'   => 'DBStats_OtherTables'
        );

        $translateSummaryLabel = function ($value) use ($valueToTranslationStr) {
            return isset($valueToTranslationStr[$value])
                ? Piwik::translate($valueToTranslationStr[$value])
                : $value;
        };

        $view->config->filters[] = array('ColumnCallbackReplace', array('label', $translateSummaryLabel), $isPriority = true);
    }

}
