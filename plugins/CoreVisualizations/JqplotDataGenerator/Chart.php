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
namespace Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

use Piwik\Piwik;
use Piwik\Common;

/**
 * Generates the data in the Open Flash Chart format, from the given data.
 */
class Chart
{
    // the data kept here conforms to the jqplot data layout
    // @see http://www.jqplot.com/docs/files/jqPlotOptions-txt.html
    protected $series = array();
    protected $data = array();
    protected $axes = array();
    protected $tooltip = array();
    protected $seriesPicker = array();

    // other attributes (not directly used for jqplot)
    protected $yUnit = '';
    protected $displayPercentageInTooltip = true;
    protected $xSteps = 2;

    /**
     * Whether to show every x-axis tick or only every other one.
     */
    protected $showAllTicks = false;

    public function setAxisXLabels(&$xLabels)
    {
        $this->axes['xaxis']['ticks'] = & $xLabels;
    }

    public function setAxisXOnClick(&$onClick)
    {
        $this->axes['xaxis']['onclick'] = & $onClick;
    }

    public function setAxisYValues(&$values)
    {
        foreach ($values as $label => &$data) {
            $this->series[] = array(
                // unsanitize here is safe since data gets outputted as JSON, not HTML
                // NOTE: this is a quick fix for a double-encode issue. if this file is refactored,
                // this fix can probably be removed (or at least made more understandable).
                'label'         => Common::unsanitizeInputValue($label),
                'internalLabel' => $label
            );

            array_walk($data, create_function('&$v', '$v = (float)$v;'));
            $this->data[] = & $data;
        }
    }

    protected function addTooltipToValue($seriesIndex, $valueIndex, $tooltipTitle, $tooltipText)
    {
        $this->tooltip[$seriesIndex][$valueIndex] = array($tooltipTitle, $tooltipText);
    }

    public function setAxisYUnit($yUnit)
    {
        $yUnits = array();
        for ($i = 0; $i < count($this->data); $i++) {
            $yUnits[] = $yUnit;
        }
        $this->setAxisYUnits($yUnits);
    }

    public function setAxisYUnits($yUnits)
    {
        // generate an axis config for each unit
        $axesIds = array();
        // associate each series with the appropriate axis
        $seriesAxes = array();
        // units for tooltips
        $seriesUnits = array();
        foreach ($yUnits as $unit) {
            // handle axes ids: first y[]axis, then y[2]axis, y[3]axis...
            $nextAxisId = empty($axesIds) ? '' : count($axesIds) + 1;

            $unit = $unit ? $unit : '';
            if (!isset($axesIds[$unit])) {
                $axesIds[$unit] = array('id' => $nextAxisId, 'unit' => $unit);
                $seriesAxes[] = 'y' . $nextAxisId . 'axis';
            } else {
                // reuse existing axis
                $seriesAxes[] = 'y' . $axesIds[$unit]['id'] . 'axis';
            }
            $seriesUnits[] = $unit;
        }

        // generate jqplot axes config
        foreach ($axesIds as $axis) {
            $axisKey = 'y' . $axis['id'] . 'axis';
            $this->axes[$axisKey]['tickOptions']['formatString'] = '%s' . $axis['unit'];
        }

        $this->tooltip['yUnits'] = $seriesUnits;

        // add axis config to series
        foreach ($seriesAxes as $i => $axisName) {
            $this->series[$i]['yaxis'] = $axisName;
        }
    }

    public function setAxisYLabels($labels)
    {
        foreach ($this->series as &$series) {
            $label = $series['internalLabel'];
            if (isset($labels[$label])) {
                $series['label'] = $labels[$label];
            }
        }
    }

    public function setSelectableColumns($selectableColumns, $multiSelect = true)
    {
        $this->seriesPicker['selectableColumns'] = $selectableColumns;
        $this->seriesPicker['multiSelect'] = $multiSelect;
    }

    public function setDisplayPercentageInTooltip($display)
    {
        $this->displayPercentageInTooltip = $display;
    }

    public function setXSteps($steps)
    {
        $this->xSteps = $steps;
    }

    /**
     * Show every x-axis tick instead of just every other one.
     */
    public function showAllTicks()
    {
        $this->showAllTicks = true;
    }

    public function render()
    {
        Piwik::overrideCacheControlHeaders();

        // See http://www.jqplot.com/docs/files/jqPlotOptions-txt.html
        $data = array(
            'params'       => array(
                'axes'   => &$this->axes,
                'series' => &$this->series
            ),
            'data'         => &$this->data,
            'tooltip'      => &$this->tooltip,
            'seriesPicker' => &$this->seriesPicker
        );

        return Common::json_encode($data);
    }

    public function customizeChartProperties()
    {
        // x axis labels with steps
        if (isset($this->axes['xaxis']['ticks'])) {
            foreach ($this->axes['xaxis']['ticks'] as $i => &$xLabel) {
                $this->axes['xaxis']['labels'][$i] = $xLabel;
                if (!$this->showAllTicks && ($i % $this->xSteps) != 0) {
                    $xLabel = ' ';
                }
            }
        }

        if ($this->displayPercentageInTooltip) {
            foreach ($this->data as $seriesIndex => &$series) {
                $sum = array_sum($series);

                foreach ($series as $valueIndex => $value) {
                    $value = (float)$value;

                    $percentage = 0;
                    if ($sum > 0) {
                        $percentage = round(100 * $value / $sum);
                    }

                    $this->tooltip['percentages'][$seriesIndex][$valueIndex] = $percentage;
                }
            }
        }
    }

    public function setSelectableRows($selectableRows)
    {
        $this->seriesPicker['selectableRows'] = $selectableRows;
    }
}