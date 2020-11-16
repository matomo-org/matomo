<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\PagePerformance\Visualizations;

use Piwik\DataTable;
use Piwik\DbHelper;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\PagePerformance\Metrics;
use Piwik\Plugins\PagePerformance\PagePerformance;

/**
 * DataTable Visualization that derives from HtmlTable and show performance columns.
 */
class PerformanceColumns extends HtmlTable
{
    const ID                = 'tablePerformanceColumns';
    const FOOTER_ICON       = 'icon-page-performance';
    const FOOTER_ICON_TITLE = 'PagePerformance_PerformanceTable';

    public function beforeRender()
    {
        parent::beforeRender();
    }

    public static function canDisplayViewDataTable($viewDataTable)
    {
        $request = $viewDataTable->getRequestArray();

        if ($viewDataTable->config->show_table_performance === false) {
            return false;
        }

        $module = $request['module'] ?? '';
        $action = $request['action'] ?? '';

        if ($module === 'Widgetize') {
            $module = $request['moduleToWidgetize'] ?: $module;
            $action = $request['actionToWidgetize'] ?: $action;
        }

        if ('Actions' === $module && in_array($action, PagePerformance::$availableForMethods)) {
            return true;
        }

        return false;
    }

    public function beforeGenericFiltersAreAppliedToLoadedDataTable()
    {
        $this->config->datatable_css_class = 'dataTableVizAllColumns';
        
        $properties = $this->config;

        $this->dataTable->filter(function (DataTable $dataTable) use ($properties) {
            $properties->columns_to_display = array_merge([
                'label',
                'nb_visits',
            ], array_keys(Metrics::getAllPagePerformanceMetrics()));

            if (version_compare(DbHelper::getInstallVersion(),'4.0.0-b1', '<')) {
                $properties->columns_to_display[] = 'avg_time_generation';
            }
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
