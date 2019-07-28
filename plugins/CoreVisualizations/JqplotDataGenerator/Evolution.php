<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

use Piwik\Archive\DataTableFactory;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Period\Factory;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;
use Piwik\Url;

/**
 * Generates JQPlot JSON data/config for evolution graphs.
 */
class Evolution extends JqplotDataGenerator
{
    protected function getUnitsForColumnsToDisplay()
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');

        $units = [];
        foreach ($this->properties['columns_to_display'] as $columnName) {
            $derivedUnit = Metrics::getUnit($columnName, $idSite);
            $units[$columnName] = empty($derivedUnit) ? false : $derivedUnit;
        }
        return $units;
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

        // the X label is extracted from the 'period' object in the table's metadata
        $xLabels[0] = [];
        foreach ($dataTable->getDataTables() as $metadataDataTable) {
            $xLabels[0][] = $metadataDataTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getLocalizedShortString(); // eg. "Aug 2009"
        }

        // TODO: explain
        $apiRequest = $this->graph->getRequestArray();

        $comparePeriods = Common::getRequestVar('comparePeriods', $default = [], $type = 'array', $apiRequest);
        $compareDates = Common::getRequestVar('compareDates', $default = [], $type = 'array', $apiRequest);
        foreach ($comparePeriods as $index => $period) {
            $date = $compareDates[$index];

            $range = Factory::build($period, $date);
            foreach ($range->getSubperiods() as $subperiod) {
                $xLabels[$index + 1][] = $subperiod->getLocalizedShortString();
            }
        }

        $units = $this->getUnitsForColumnsToDisplay();

        // if rows to display are not specified, default to all rows (TODO: perhaps this should be done elsewhere?)
        $rowsToDisplay = $this->properties['rows_to_display']
            ? : array_unique($dataTable->getColumn('label'))
                ? : array(false) // make sure that a series is plotted even if there is no data
        ;

        $seriesLabels = [];
        foreach ($this->comparisonsForLabels as $index => $ignore) {
            $seriesLabels[] = $this->getComparisonSeriesLabelSuffixFromIndex($index);
        }

        // collect series data to show. each row-to-display/column-to-display permutation creates a series.
        $allSeriesData = array();
        $seriesUnits = array();
        foreach ($rowsToDisplay as $rowLabel) {
            foreach ($this->properties['columns_to_display'] as $columnName) {
                if (!$this->isComparing) { // TODO: move this & to individual functions
                    $seriesLabel = $this->getSeriesLabel($rowLabel, $columnName);

                    $seriesData = $this->getSeriesData($rowLabel, $columnName, $dataTable);
                    $allSeriesData[$seriesLabel] = $seriesData;

                    $seriesUnits[$seriesLabel] = $units[$columnName];
                } else {
                    foreach ($dataTable->getDataTables() as $label => $childTable) {
                        // get the row for this label (use the first if $rowLabel is false)
                        if ($rowLabel === false) {
                            $row = $childTable->getFirstRow();
                        } else {
                            $row = $childTable->getRowFromLabel($rowLabel);
                        }

                        // TODO: what happens if label isn't found?

                        /** @var DataTable $comparisonTable */
                        $comparisonTable = $row->getComparisons();
                        if (empty($comparisonTable)) {
                            continue;
                        }

                        foreach ($seriesLabels as $seriesLabel) {
                            $allSeriesData[$seriesLabel][] = 0;
                            $seriesUnits[$seriesLabel] = $units[$columnName]; // TODO: doesn't have to be in every iteration
                        }

                        foreach ($comparisonTable->getRows() as $index => $compareRow) {
                            $seriesLabel = $seriesLabels[$index]; // $this->getSeriesLabel($rowLabel, $columnName, $compareRow, $index); TODO: deal w/ this
                            $lastIndex = count($allSeriesData[$seriesLabel]) - 1;
                            $allSeriesData[$seriesLabel][$lastIndex] = $compareRow->getColumn($columnName);
                        }
                    }
                }
            }
        }

        $visualization->dataTable = $dataTable;
        $visualization->properties = $this->properties;

        $visualization->setAxisYValues($allSeriesData);
        $visualization->setAxisYUnits($seriesUnits);
        $visualization->setAxisXLabelsMultiple($xLabels);

        if ($this->isLinkEnabled()) {
            $idSite = Common::getRequestVar('idSite', null, 'int');
            $periodLabel = reset($dataTables)->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getLabel();

            $axisXOnClick = array();
            foreach ($dataTable->getDataTables() as $metadataDataTable) {
                $dateInUrl = $metadataDataTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getDateStart();
                $parameters = array(
                    'idSite'  => $idSite,
                    'period'  => $periodLabel,
                    'date'    => $dateInUrl->toString(),
                    'segment' => \Piwik\API\Request::getRawSegmentFromRequest()
                );
                $link = Url::getQueryStringFromParameters($parameters);
                $axisXOnClick[] = $link;
            }
            $visualization->setAxisXOnClick($axisXOnClick);
        }
    }

    private function getSeriesData($rowLabel, $columnName, DataTable\Map $dataTable)
    {
        $seriesData = array();
        foreach ($dataTable->getDataTables() as $childTable) {
            // get the row for this label (use the first if $rowLabel is false)
            if ($rowLabel === false) {
                $row = $childTable->getFirstRow();
            } else {
                $row = $childTable->getRowFromLabel($rowLabel);
            }

            // get series data point. defaults to 0 if no row or no column value.
            if ($row === false) {
                $seriesData[] = 0;
            } else {
                $seriesData[] = $row->getColumn($columnName) ? : 0;
            }
        }
        return $seriesData;
    }

    /**
     * Derive the series label from the row label and the column name.
     * If the row label is set, both the label and the column name are displayed.
     * @param string $rowLabel
     * @param string $columnName
     * @return string
     */
    private function getSeriesLabel($rowLabel, $columnName, Row $compareRow = null, $comparisonIndex = null)
    {
        $metricLabel = @$this->properties['translations'][$columnName];

        if ($rowLabel !== false) {
            // eg. "Yahoo! (Visits)"
            $label = "$rowLabel ($metricLabel)";
        } else {
            // eg. "Visits"
            $label = $metricLabel;
        }


        if (!empty($compareRow)) {
            $label .= ' ' . $this->getComparisonSeriesLabelSuffixFromIndex($compareRow, $comparisonIndex);
        }

        return $label;
    }

    private function isLinkEnabled()
    {
        static $linkEnabled;
        if (!isset($linkEnabled)) {
            // 1) Custom Date Range always have link disabled, otherwise
            // the graph data set is way too big and fails to display
            // 2) disableLink parameter is set in the Widgetize "embed" code
            $linkEnabled = !Common::getRequestVar('disableLink', 0, 'int')
                && Common::getRequestVar('period', 'day') != 'range';
        }
        return $linkEnabled;
    }
}
