<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations;

use Exception;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\Chart;

require_once PIWIK_INCLUDE_PATH . '/plugins/CoreVisualizations/JqplotDataGenerator/Evolution.php';

/**
 * Generates JSON data used to configure and populate JQPlot graphs.
 *
 * Supports pie graphs, bar graphs and time serieses (aka, evolution graphs).
 */
class JqplotDataGenerator
{
    /**
     * View properties. @see Piwik\ViewDataTable for more info.
     *
     * @var array
     */
    protected $properties;

    protected $graphType;

    /**
     * Creates a new JqplotDataGenerator instance for a graph type and view properties.
     *
     * @param string $type 'pie', 'bar', or 'evolution'
     * @param array $properties The view properties.
     * @throws \Exception
     * @return JqplotDataGenerator
     */
    public static function factory($type, $properties)
    {
        switch ($type) {
            case 'evolution':
                return new JqplotDataGenerator\Evolution($properties, $type);
            case 'pie':
            case 'bar':
                return new JqplotDataGenerator($properties, $type);
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
    public function __construct($properties, $graphType)
    {
        $this->properties = $properties;
        $this->graphType = $graphType;
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
     * @param $visualization
     */
    protected function initChartObjectData($dataTable, $visualization)
    {
        // We apply a filter to the DataTable, decoding the label column (useful for keywords for example)
        $dataTable->filter('ColumnCallbackReplace', array('label', 'urldecode'));

        $xLabels = $dataTable->getColumn('label');

        $columnNames = $this->properties['columns_to_display'];
        if (($labelColumnIndex = array_search('label', $columnNames)) !== false) {
            unset($columnNames[$labelColumnIndex]);
        }

        $columnNameToTranslation = $columnNameToValue = array();
        foreach ($columnNames as $columnName) {
            $columnNameToTranslation[$columnName] = @$this->properties['translations'][$columnName];

            $columnNameToValue[$columnName] = $dataTable->getColumn($columnName);
        }

        $visualization->dataTable = $dataTable;
        $visualization->properties = $this->properties;

        $visualization->setAxisXLabels($xLabels);
        $visualization->setAxisYValues($columnNameToValue);
        $visualization->setAxisYLabels($columnNameToTranslation);

        $units = $this->getUnitsForColumnsToDisplay();
        $visualization->setAxisYUnits($units);
    }

    protected function getUnitsForColumnsToDisplay()
    {
        // derive units from column names
        $units = $this->deriveUnitsFromRequestedColumnNames();
        if (!empty($this->properties['y_axis_unit'])) {
            $units = array_fill(0, count($units), $this->properties['y_axis_unit']);
        }

        // the bar charts contain the labels a first series
        // this series has to be removed from the units
        reset($units);
        if ($this->graphType == 'bar'
            && key($units) == 'label'
        ) {
            array_shift($units);
        }

        return $units;
    }

    private function deriveUnitsFromRequestedColumnNames()
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');

        $units = array();
        foreach ($this->properties['columns_to_display'] as $columnName) {
            $derivedUnit = Metrics::getUnit($columnName, $idSite);
            $units[$columnName] = empty($derivedUnit) ? false : $derivedUnit;
        }
        return $units;
    }
}
