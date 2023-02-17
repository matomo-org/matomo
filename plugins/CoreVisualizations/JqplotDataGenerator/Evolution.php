<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

use Piwik\Archive\DataTableFactory;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Period\Factory;
use Piwik\Plugins\API\Filter\DataComparisonFilter;
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

        // determine x labels based on both the displayed date range and the compared periods
        /** @var Period[][] $xLabels */
        $xLabels = [
            [], // placeholder for first series
        ];

        $this->addComparisonXLabels($xLabels, reset($dataTables));
        $this->addSelectedSeriesXLabels($xLabels, $dataTables);

        $units = $this->getUnitsForColumnsToDisplay();

        // if rows to display are not specified, default to all rows (TODO: perhaps this should be done elsewhere?)
        $rowsToDisplay = $this->properties['rows_to_display']
            ? : array_unique($dataTable->getColumn('label'))
                ? : array(false) // make sure that a series is plotted even if there is no data
        ;

        $columnsToDisplay = array_values($this->properties['columns_to_display']);

        list($seriesMetadata, $seriesUnits, $seriesLabels, $seriesToXAxis) =
            $this->getSeriesMetadata($rowsToDisplay, $columnsToDisplay, $units, $dataTables);

        // collect series data to show. each row-to-display/column-to-display permutation creates a series.
        $allSeriesData = array();
        foreach ($rowsToDisplay as $rowLabel) {
            foreach ($columnsToDisplay as $columnName) {
                if (!$this->isComparing) {
                    $this->setNonComparisonSeriesData($allSeriesData, $rowLabel, $columnName, $dataTable);
                } else {
                    $this->setComparisonSeriesData($allSeriesData, $seriesLabels, $rowLabel, $columnName, $dataTable);
                }
            }
        }

        $visualization->properties = $this->properties;

        $units = null;
        if ($visualization->properties['request_parameters_to_modify']['format_metrics'] === 0) {
            $units = $seriesUnits;
        }
        $visualization->setAxisYValues($allSeriesData, $seriesMetadata, $units);
        $visualization->setAxisYUnits($seriesUnits);

        $xLabelStrs = [];
        $xAxisTicks = [];
        foreach ($xLabels as $index => $seriesXLabels) {
            $xLabelStrs[$index] = array_map(function (Period $p) { return $p->getLocalizedLongString(); }, $seriesXLabels);
            $xAxisTicks[$index] = array_map(function (Period $p) { return $p->getLocalizedShortString(); }, $seriesXLabels);
        }

        $visualization->setAxisXLabelsMultiple($xLabelStrs, $seriesToXAxis, $xAxisTicks);

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
    private function getSeriesLabel($rowLabel, $columnName)
    {
        $metricLabel = @$this->properties['translations'][$columnName];

        if ($rowLabel !== false) {
            // eg. "Yahoo! (Visits)"
            $label = "$rowLabel ($metricLabel)";
        } else {
            // eg. "Visits"
            $label = $metricLabel;
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

    /**
     * Each period comparison shows data over different data points than the main series (eg, 2014-02-03,1014-02-06 compared w/ 2015-03-04,2015-03-15).
     * Though we only display the selected period's x labels, we need to both have the labels for all these data points for tooltips and to stretch
     * out the selected period x axis, in case it is shorter than one of the compared periods (as in the example above).
     */
    private function addComparisonXLabels(array &$xLabels, DataTable $table)
    {
        $comparePeriods = $table->getMetadata('comparePeriods') ?: [];
        $compareDates = $table->getMetadata('compareDates') ?: [];

        // get rid of selected period
        array_shift($comparePeriods);
        array_shift($compareDates);

        foreach (array_values($comparePeriods) as $index => $period) {
            $date = $compareDates[$index];

            $range = Factory::build($period, $date);
            foreach ($range->getSubperiods() as $subperiod) {
                $xLabels[$index + 1][] = $subperiod;
            }
        }
    }

    /**
     * @param array $xLabels
     * @param DataTable[] $dataTables
     * @throws \Exception
     */
    protected function addSelectedSeriesXLabels(array &$xLabels, array $dataTables)
    {
        $xTicksCount = count($dataTables);
        foreach ($xLabels as $labelSeries) {
            $xTicksCount = max(count($labelSeries), $xTicksCount);
        }

        /** @var Date $startDate */
        $startDate = reset($dataTables)->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getDateStart();
        $periodType = reset($dataTables)->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getLabel();

        for ($i = 0; $i < $xTicksCount; ++$i) {
            $period = Factory::build($periodType, $startDate->addPeriod($i, $periodType));
            $xLabels[0][] = $period;
        }
    }

    private function setNonComparisonSeriesData(array &$allSeriesData, $rowLabel, $columnName, DataTable\Map $dataTable)
    {
        $seriesLabel = $this->getSeriesLabel($rowLabel, $columnName);

        $seriesData = $this->getSeriesData($rowLabel, $columnName, $dataTable);
        $allSeriesData[$seriesLabel] = $seriesData;
    }

    private function setComparisonSeriesData(array &$allSeriesData, array $seriesLabels, $rowLabel, $columnName, DataTable\Map $dataTable)
    {
        foreach ($dataTable->getDataTables() as $label => $childTable) {
            // get the row for this label (use the first if $rowLabel is false)
            if ($rowLabel === false) {
                $row = $childTable->getFirstRow();
            } else {
                $row = $childTable->getRowFromLabel($rowLabel);
            }

            if (empty($row)
                || empty($row->getComparisons())
            ) {
                foreach ($seriesLabels as $seriesIndex => $seriesLabelPrefix) {
                    $wholeSeriesLabel = $this->getComparisonSeriesLabelFromCompareSeries($seriesLabelPrefix, $columnName, $rowLabel);
                    $allSeriesData[$wholeSeriesLabel][] = 0;
                }

                continue;
            }

            /** @var DataTable $comparisonTable */
            $comparisonTable = $row->getComparisons();
            foreach ($comparisonTable->getRows() as $compareRow) {
                $seriesLabel = $this->getComparisonSeriesLabel($compareRow, $columnName, $rowLabel);
                $allSeriesData[$seriesLabel][] = $compareRow->getColumn($columnName);
            }

            $totalsRow = $comparisonTable->getTotalsRow();
            if ($totalsRow) {
                $seriesLabel = $this->getComparisonSeriesLabel($totalsRow, $columnName, $rowLabel);
                $allSeriesData[$seriesLabel][] = $totalsRow->getColumn($columnName);
            }
        }
    }

    private function getSeriesMetadata(array $rowsToDisplay, array $columnsToDisplay, array $units, array $dataTables)
    {
        $seriesMetadata = null; // maps series labels to any metadata of the series
        $seriesUnits = array(); // maps series labels to unit labels
        $seriesToXAxis = []; // maps series index to x-axis index (groups of metrics for a single comparison will use the same x-axis)

        $table = reset($dataTables);
        $seriesLabels = $table->getMetadata('comparisonSeries') ?: [];
        foreach ($rowsToDisplay as $rowIndex => $rowLabel) {
            foreach ($columnsToDisplay as $columnIndex => $columnName) {
                if ($this->isComparing) {
                    foreach ($seriesLabels as $seriesIndex => $seriesLabel) {
                        $wholeSeriesLabel = $this->getComparisonSeriesLabelFromCompareSeries($seriesLabel, $columnName, $rowLabel);

                        $allSeriesData[$wholeSeriesLabel] = [];

                        $metricIndex = $rowIndex * count($columnsToDisplay) + $columnIndex;
                        $seriesMetadata[$wholeSeriesLabel] = [
                            'metricIndex' => $metricIndex,
                            'seriesIndex' => $seriesIndex,
                        ];

                        $seriesUnits[$wholeSeriesLabel] = $units[$columnName];

                        list($periodIndex, $segmentIndex) = DataComparisonFilter::getIndividualComparisonRowIndices($table, $seriesIndex);
                        $seriesToXAxis[] = $periodIndex;
                    }
                } else {
                    $seriesLabel = $this->getSeriesLabel($rowLabel, $columnName);
                    $seriesUnits[$seriesLabel] = $units[$columnName];
                }
            }
        }

        return [$seriesMetadata, $seriesUnits, $seriesLabels, $seriesToXAxis];
    }
}
