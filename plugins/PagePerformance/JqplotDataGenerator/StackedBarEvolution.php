<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PagePerformance\JqplotDataGenerator;

use Piwik\DataTable;
use Piwik\Period;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

/**
 * Generates JQPlot JSON data/config for evolution graphs.
 */
class StackedBarEvolution extends JqplotDataGenerator\Evolution
{
    public function generate($dataTable)
    {
        $visualization = new Chart();

        if ($dataTable->getRowsCount() > 0) {
            $dataTable->applyQueuedFilters();
            $this->initChartObjectData($dataTable, $visualization);
        }

        return $visualization->render();
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     * @param Chart $visualization
     */
    protected function initChartObjectData($dataTable, $visualization)
    {
        // if the loaded datatable is a simple DataTable, it is most likely a plugin plotting some custom data
        // we don't expect plugin developers to return a well defined Set

        if ($dataTable instanceof DataTable) {
            parent::initChartObjectData($dataTable, $visualization);
            return;
        }

        $dataTables = $dataTable->getDataTables();

        // determine x labels based on both the displayed date range and the compared periods
        /** @var Period[][] $xLabels */
        $xLabels = [
            [], // placeholder for first series
        ];

        $this->addSelectedSeriesXLabels($xLabels, $dataTables);

        $columnsToDisplay = array_values($this->properties['columns_to_display']);

        // collect series data to show. each row-to-display/column-to-display permutation creates a series.
        $allSeriesData = array();
        foreach ($columnsToDisplay as $column) {
            $allSeriesData[$column] = $this->getSeriesData($column, $dataTable);
        }

        $visualization->dataTable = $dataTable;
        $visualization->properties = $this->properties;

        $seriesMetadata = [];
        foreach ($columnsToDisplay as $columnName) {
            $seriesMetadata[$columnName] = [
                'internalLabel' => $columnName,
                'label' => @$this->properties['translations'][$columnName] ?: $columnName
            ];
        }

        $visualization->setAxisYValues($allSeriesData, $seriesMetadata);
        $visualization->setAxisYUnits($this->getUnitsForColumnsToDisplay());

        $xLabelStrs = [];
        $xAxisTicks = [];
        foreach ($xLabels as $index => $seriesXLabels) {
            $xLabelStrs[$index] = array_map(function (Period $p) { return $p->getLocalizedLongString(); }, $seriesXLabels);
            $xAxisTicks[$index] = array_map(function (Period $p) { return $p->getLocalizedShortString(); }, $seriesXLabels);
        }

        $visualization->setAxisXLabelsMultiple($xLabelStrs, [], $xAxisTicks);
    }

    private function getSeriesData($column, DataTable\Map $dataTable)
    {
        $seriesData = array();
        foreach ($dataTable->getDataTables() as $childTable) {
            $row = $childTable->getFirstRow();

            // get series data point. defaults to 0 if no row or no column value.
            if ($row === false) {
                $seriesData[] = 0;
            } else {
                $seriesData[] = $row->getColumn($column) ? : 0;
            }
        }

        return $seriesData;
    }
}
