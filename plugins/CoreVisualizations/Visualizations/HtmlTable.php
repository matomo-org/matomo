<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\API\Request as ApiRequest;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\DataTable;
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

    public function beforeLoadDataTable()
    {
        $this->checkRequestIsNotForMultiplePeriods();

        if ($this->isComparing()) {
            $request = $this->getRequestArray();
            if (!empty($request['comparePeriods'])
                && count($request['comparePeriods']) == 1
            ) {
                $this->requestConfig->request_parameters_to_modify['invert_compare_change_compute'] = 1;
            }

            // forward the comparisonIdSubtables var if present so it will be used when next/prev links are clicked
            $comparisonIdSubtables = Common::getRequestVar('comparisonIdSubtables', false, 'string');
            if (!empty($comparisonIdSubtables)) {
                $comparisonIdSubtables = Common::unsanitizeInputValue($comparisonIdSubtables);
                $this->config->custom_parameters['comparisonIdSubtables'] = $comparisonIdSubtables;
            }
        }
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

        if ($this->isComparing()
            && !empty($this->dataTable)
        ) {
            $this->assignTemplateVar('comparisonTotals', $this->dataTable->getMetadata('comparisonTotals'));
        }

        // Note: This needs to be done right before rendering, as otherwise some plugins might change the columns to display again
        if ($this->isFlattened()) {
            $dimensions = $this->dataTable->getMetadata('dimensions');

            $hasMultipleDimensions = is_array($dimensions) && count($dimensions) > 1;
            $this->assignTemplateVar('hasMultipleDimensions', $hasMultipleDimensions);

            if ($hasMultipleDimensions) {
                if ($this->config->show_dimensions) {
                    // ensure first metric translation is used as label if other dimensions are in separate columns
                    $this->config->addTranslation('label', $this->config->translations[reset($dimensions)]);
                } else {
                    // concatenate dimensions if table is shown flattened
                    foreach ($dimensions as $dimension) {
                        $labels[] = $this->config->translations[$dimension];
                    }
                    $this->config->addTranslation('label', implode(' - ', $labels));
                }
            }

            if ($this->config->show_dimensions && $hasMultipleDimensions) {


                $properties = $this->config;
                array_shift($dimensions); // shift away first dimension, as that will be shown as label

                $this->dataTable->filter(function (DataTable $dataTable) use ($properties, $dimensions) {
                    if (empty($properties->columns_to_display)) {
                        $columns           = $dataTable->getColumns();
                        $hasNbVisits       = in_array('nb_visits', $columns);
                        $hasNbUniqVisitors = in_array('nb_uniq_visitors', $columns);

                        $properties->setDefaultColumnsToDisplay($columns, $hasNbVisits, $hasNbUniqVisitors);
                    }

                    $label = array_search('label', $properties->columns_to_display);
                    if ($label !== false) {
                        unset($properties->columns_to_display[$label]);
                    }

                    foreach (array_reverse($dimensions) as $dimension) {
                        array_unshift($properties->columns_to_display, $dimension);
                    }

                    array_unshift($properties->columns_to_display, 'label');
                });
            }
        }

        $this->assignTemplateVar('segmentTitlePretty', $this->dataTable->getMetadata('segmentPretty'));

        $period = $this->dataTable->getMetadata('period');
        $this->assignTemplateVar('periodTitlePretty', $period ? $period->getLocalizedShortString() : '');
    }

    public function beforeGenericFiltersAreAppliedToLoadedDataTable()
    {
        if ($this->isPivoted()) {
            $this->config->columns_to_display = $this->dataTable->getColumns();

            $this->dataTable->applyQueuedFilters();
        }

        parent::beforeGenericFiltersAreAppliedToLoadedDataTable();

        // Note: This needs to be done right before generic filter are applied, to make sorting such columns possible
        if ($this->isFlattened()) {
            $dimensions = $this->dataTable->getMetadata('dimensions');

            $hasMultipleDimensions = is_array($dimensions) && count($dimensions) > 1;

            if ($hasMultipleDimensions) {
                foreach (Dimension::getAllDimensions() as $dimension) {
                    $dimensionId = str_replace('.', '_', $dimension->getId());
                    $dimensionName = $dimension->getName();

                    if (!empty($dimensionId) && !empty($dimensionName) && in_array($dimensionId, $dimensions)) {
                        $this->config->translations[$dimensionId] = $dimensionName;
                    }
                }
            }


            if ($this->config->show_dimensions && $hasMultipleDimensions) {

                $this->dataTable->filter(function($dataTable) use ($dimensions) {
                    /** @var DataTable $dataTable */
                    $rows = $dataTable->getRows();
                    foreach ($rows as $row) {
                        foreach ($dimensions as $dimension) {
                            $row->setColumn($dimension, $row->getMetadata($dimension));
                        }
                    }
                });

                # replace original label column with first dimension
                $firstDimension = array_shift($dimensions);
                $this->dataTable->filter('ColumnCallbackAddMetadata', array('label', 'combinedLabel', function ($label) { return $label; }));
                $this->dataTable->filter('ColumnDelete', array('label'));
                $this->dataTable->filter('ReplaceColumnNames', array(array($firstDimension => 'label')));
            }
        }
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

    public function supportsComparison()
    {
        return true;
    }

    protected function isFlattened()
    {
        return $this->requestConfig->flat || Common::getRequestVar('flat', '');
    }
}
