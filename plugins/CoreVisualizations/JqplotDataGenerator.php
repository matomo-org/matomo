<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreVisualizations
 */

namespace Piwik\Plugins\CoreVisualizations;

use Exception;
use Piwik\Common;
use Piwik\Metrics;
use Piwik\DataTable;
use Piwik\Visualization;
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
     * View properties. @see Piwik_ViewDataTable for more info.
     *
     * @var array
     */
    protected $properties;

    /**
     * This object does most of the work in generating the JQPlot JSON data.
     *
     * @var Visualization\
     */
    protected $visualization;

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
                return new JqplotDataGenerator\Evolution($properties);
            case 'pie':
            case 'bar':
                return new JqplotDataGenerator($properties);
            default:
                throw new Exception("Unknown JqplotDataGenerator type '$type'.");
        }
    }

    /**
     * Constructor.
     *
     * @param Visualization\ $visualization
     * @param array $properties
     */
    public function __construct($properties)
    {
        $this->visualization = new Chart();
        $this->properties = $properties;
    }

    /**
     * Generates JSON graph data and returns it.
     *
     * @param DataTable|DataTable\Map $dataTable
     * @return string
     */
    public function generate($dataTable)
    {
        if (!empty($this->properties['visualization_properties']->max_graph_elements)) {
            $offsetStartSummary = $this->properties['visualization_properties']->max_graph_elements - 1;
            $sortColumn = !empty($this->properties['filter_sort_column'])
                ? $this->properties['filter_sort_column']
                : Metrics::INDEX_NB_VISITS;

            $dataTable->filter(
                'AddSummaryRow', array($offsetStartSummary, Piwik_Translate('General_Others'), $sortColumn));
        }

        if ($dataTable->getRowsCount() > 0) {
            // if addTotalRow was called in GenerateGraphHTML, add a row containing totals of
            // different metrics
            if (!empty($this->properties['visualization_properties']->add_total_row)) {
                $dataTable->queueFilter('AddSummaryRow', array(0, Piwik_Translate('General_Total'), null, false));
            }

            $this->initChartObjectData($dataTable);
        }

        $this->visualization->customizeChartProperties();

        return $this->visualization->render();
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     */
    protected function initChartObjectData($dataTable)
    {
        $dataTable->applyQueuedFilters();

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

        $visualization = $this->visualization;
        $visualization->setAxisXLabels($xLabels);
        $visualization->setAxisYValues($columnNameToValue);
        $visualization->setAxisYLabels($columnNameToTranslation);
        $visualization->setAxisYUnit($this->properties['y_axis_unit']);
        $visualization->setDisplayPercentageInTooltip(
            $this->properties['visualization_properties']->display_percentage_in_tooltip);

        // show_all_ticks is not real query param, it is set by GenerateGraphHTML.
        if ($this->properties['visualization_properties']->show_all_ticks) {
            $visualization->showAllTicks();
        }

        $units = $this->getUnitsForColumnsToDisplay();
        $visualization->setAxisYUnits($units);

        $this->addSeriesPickerToView();
    }

    protected function getUnitsForColumnsToDisplay()
    {
        // derive units from column names
        $units = $this->deriveUnitsFromRequestedColumnNames();
        if (!empty($this->properties['y_axis_unit'])) {
            // force unit to the value set via $this->setAxisYUnit()
            foreach ($units as &$unit) {
                $unit = $this->properties['y_axis_unit'];
            }
        }

        // the bar charts contain the labels a first series
        // this series has to be removed from the units
        if ($this->visualization instanceof Visualization\Chart\VerticalBar) {
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

    /**
     * Used in initChartObjectData to add the series picker config to the view object
     */
    protected function addSeriesPickerToView()
    {
        $defaultShowSeriesPicker = $this->properties['visualization_properties']->show_series_picker;
        if (count($this->properties['visualization_properties']->selectable_columns)
            && Common::getRequestVar('showSeriesPicker', $defaultShowSeriesPicker) == 1
        ) {
            $selectableColumns = array();
            foreach ($this->properties['visualization_properties']->selectable_columns as $column) {
                $selectableColumns[] = array(
                    'column'      => $column,
                    'translation' => @$this->properties['translations'][$column],
                    'displayed'   => in_array($column, $this->properties['columns_to_display'])
                );
            }

            $this->visualization->setSelectableColumns(
                $selectableColumns, $this->properties['visualization_properties']->allow_multi_select_series_picker);
        }
    }
}
