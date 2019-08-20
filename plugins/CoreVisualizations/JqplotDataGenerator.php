<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations;

use Exception;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Period\Factory;
use Piwik\Piwik;
use Piwik\Plugins\API\Filter\DataComparisonFilter;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\Chart;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;

require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/JqplotDataGenerator/Evolution.php';

/**
 * Generates JSON data used to configure and populate JQPlot graphs.
 *
 * Supports pie graphs, bar graphs and time serieses (aka, evolution graphs).
 */
class JqplotDataGenerator
{
    /**
     * View properties. @see \Piwik\ViewDataTable for more info.
     *
     * @var array
     */
    protected $properties;

    protected $graphType;

    protected $isComparing;

    /**
     * @var array
     */
    protected $comparisonsForLabels;

    private $availableSegments;

    /**
     * @var JqplotGraph
     */
    protected $graph;

    /**
     * Creates a new JqplotDataGenerator instance for a graph type and view properties.
     *
     * @param string $type 'pie', 'bar', or 'evolution'
     * @param array $properties The view properties.
     * @throws \Exception
     * @return JqplotDataGenerator
     */
    public static function factory($type, $properties, JqplotGraph $graph)
    {
        switch ($type) {
            case 'evolution':
                return new JqplotDataGenerator\Evolution($properties, $type, $graph);
            case 'pie':
            case 'bar':
                return new JqplotDataGenerator($properties, $type, $graph);
            default:
                throw new Exception("Unknown JqplotDataGenerator type '$type'.");
        }
    }

    /**
     * Constructor.
     *
     * @param array $properties
     * @param string $graphType
     *
     * @internal param \Piwik\Plugin\ViewDataTable $visualization
     */
    public function __construct($properties, $graphType, JqplotGraph $graph)
    {
        $this->properties = $properties;
        $this->graphType = $graphType;
        $this->isComparing = $graph->isComparing();
        $this->graph = $graph;

        $this->setComparisonsForLabels();
        $this->availableSegments = Request::processRequest('SegmentEditor.getAll', $override = [], $default = []);
    }

    /**
     * Generates JSON graph data and returns it.
     *
     * @param DataTable|DataTable\Map $dataTable
     * @return string
     */
    public function generate($dataTable)
    {
        $visualization = new Chart();

        if ($dataTable->getRowsCount() > 0) {
            // if addTotalRow was called in GenerateGraphHTML, add a row containing totals of
            // different metrics
            if ($this->properties['add_total_row']) {
                $dataTable->queueFilter('AddSummaryRow', Piwik::translate('General_Total'));
            }

            $dataTable->applyQueuedFilters();
            $this->initChartObjectData($dataTable, $visualization);
        }

        return $visualization->render();
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     * @param Chart $visualization
     */
    protected function initChartObjectData($dataTable, Chart $visualization)
    {
        $xLabels = $dataTable->getColumn('label');

        $columnsToDisplay = $this->properties['columns_to_display'];
        if (($labelColumnIndex = array_search('label', $columnsToDisplay)) !== false) {
            unset($columnsToDisplay[$labelColumnIndex]);
        }

        if ($this->isComparing) {
            list($yLabels, $serieses) = $this->getComparisonTableSerieses($dataTable, $columnsToDisplay);
        } else {
            list($yLabels, $serieses) = $this->getMainTableSerieses($dataTable, $columnsToDisplay);
        }

        $visualization->dataTable = $dataTable;
        $visualization->properties = $this->properties;

        $visualization->setAxisXLabels($xLabels);
        $visualization->setAxisYValues($serieses);
        $visualization->setAxisYLabels($yLabels);

        $units = $this->getUnitsForSerieses($yLabels);
        $visualization->setAxisYUnits($units);
    }

    private function getMainTableSerieses(DataTable $dataTable, $columnNames)
    {
        $columnNameToTranslation = [];

        foreach ($columnNames as $columnName) {
            $columnNameToTranslation[$columnName] = @$this->properties['translations'][$columnName];
        }

        $columnNameToValue = array();
        foreach ($columnNames as $columnName) {
            $columnNameToValue[$columnName] = $dataTable->getColumn($columnName);
        }

        return [$columnNameToTranslation, $columnNameToValue];
    }

    private function getComparisonTableSerieses(DataTable $dataTable, $columnsToDisplay)
    {
        $seriesLabels = [];
        $serieses = [];

        foreach ($dataTable->getRows() as $row) {
            /** @var DataTable $comparisonTable */
            $comparisonTable = $row->getComparisons();
            if (empty($comparisonTable)) {
                continue;
            }

            foreach ($comparisonTable->getRows() as $index => $compareRow) {
                foreach ($columnsToDisplay as $columnName) {
                    $seriesId = $columnName . '|' . $index;

                    $seriesLabel = $this->getComparisonSeriesLabel($compareRow, $columnName);
                    $seriesLabels[$seriesId] = $seriesLabel;
                    $serieses[$seriesId][] = $compareRow->getColumn($columnName);
                }
            }
        }

        return [$seriesLabels, $serieses];
    }

    protected function getComparisonSeriesLabel(Row $compareRow, $columnName)
    {
        $columnTranslation = @$this->properties['translations'][$columnName];

        $label = $columnTranslation . ' ' . $compareRow->getMetadata('compareSeriesPretty');
        return $label;
    }

    protected function getUnitsForSerieses($yLabels)
    {
        // derive units from column names
        $units = $this->deriveUnitsFromRequestedColumnNames($yLabels);
        if (!empty($this->properties['y_axis_unit'])) {
            $units = array_fill(0, count($units), $this->properties['y_axis_unit']);
        }
        return $units;
    }

    private function deriveUnitsFromRequestedColumnNames($yLabels)
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');

        $units = array();
        foreach ($yLabels as $seriesId => $ignore) {
            $parts = explode('|', $seriesId, 2);
            $columnName = $parts[0];

            $derivedUnit = Metrics::getUnit($columnName, $idSite);
            $units[$seriesId] = empty($derivedUnit) ? false : $derivedUnit;
        }
        return $units;
    }

    private function setComparisonsForLabels()
    {
        $compareSegments = Common::getRequestVar('compareSegments', $default = [], $type = 'array');
        $compareSegments = Common::unsanitizeInputValues($compareSegments);

        $comparePeriods = Common::getRequestVar('comparePeriods', $default = [], $type = 'array');
        $compareDates = Common::getRequestVar('compareDates', $default = [], $type = 'array');

        $segment = Common::getRequestVar('segment', $default = '');

        array_unshift($compareSegments, $segment ? : '');
        array_unshift($compareDates, ''); // for date/period, we use the metadata in the table to avoid requesting multiple periods
        array_unshift($comparePeriods, '');

        $this->comparisonsForLabels = DataComparisonFilter::getReportsToCompare($compareSegments, $comparePeriods, $compareDates);
    }
}
