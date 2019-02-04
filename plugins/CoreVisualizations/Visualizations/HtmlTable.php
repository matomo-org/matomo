<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\API\Request as ApiRequest;
use Piwik\Common;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Plugin\Visualization;

/**
 * DataTable visualization that shows DataTable data in an HTML table.
 *
 * @property HtmlTable\Config $config
 */
class HtmlTable extends Visualization
{
    const ID = 'table';
    const TEMPLATE_FILE     = "@CoreVisualizations/_dataTableViz_htmlTable.twig";
    const FOOTER_ICON       = 'icon-table';
    const FOOTER_ICON_TITLE = 'General_DisplaySimpleTable';

    public static function getDefaultConfig()
    {
        return new HtmlTable\Config();
    }

    public static function getDefaultRequestConfig()
    {
        return new HtmlTable\RequestConfig();
    }

    public function beforeRender()
    {
        if ($this->requestConfig->idSubtable
            && $this->config->show_embedded_subtable) {

            $this->config->show_visualization_only = true;
        }

        if ($this->requestConfig->idSubtable) {
            $this->config->show_totals_row = false;
        }

        foreach (Metrics::getMetricIdsToProcessReportTotal() as $metricId) {
            $this->config->report_ratio_columns[] = Metrics::getReadableColumnName($metricId);
        }
        if (!empty($this->report)) {
            foreach ($this->report->getMetricNamesToProcessReportTotals() as $metricName) {
                $this->config->report_ratio_columns[] = $metricName;
            }
        }

        // we do not want to get a datatable\map
        $period = Common::getRequestVar('period', 'day', 'string');
        if (Period\Range::parseDateRange($period)) {
            $period = 'range';
        }

        if ($this->dataTable->getRowsCount()) {
            $request = new ApiRequest(array(
                'method' => 'API.get',
                'module' => 'API',
                'action' => 'get',
                'format' => 'original',
                'filter_limit'  => '-1',
                'disable_generic_filters' => 1,
                'expanded'      => 0,
                'flat'          => 0,
                'filter_offset' => 0,
                'period'        => $period,
                'showColumns'   => implode(',', $this->config->columns_to_display),
                'columns'       => implode(',', $this->config->columns_to_display),
                'pivotBy'       => ''
            ));

            $dataTable = $request->process();
            $this->assignTemplateVar('siteSummary', $dataTable);
        }

        if ($this->isPivoted()) {
            $this->config->columns_to_display = $this->dataTable->getColumns();
        }
    }

    public function beforeGenericFiltersAreAppliedToLoadedDataTable()
    {
        if ($this->isPivoted()) {
            $this->config->columns_to_display = $this->dataTable->getColumns();

            $this->dataTable->applyQueuedFilters();
        }

        parent::beforeGenericFiltersAreAppliedToLoadedDataTable();
    }

    protected function isPivoted()
    {
        return $this->requestConfig->pivotBy || Common::getRequestVar('pivotBy', '');
    }

    /**
     * Override to compute a custom cell HTML attributes (such as style).
     *
     * @param Row $row
     * @param $column
     * @return array Array of name => value pairs.
     */
    public function getCellHtmlAttributes(Row $row, $column)
    {
        return null;
    }
}
