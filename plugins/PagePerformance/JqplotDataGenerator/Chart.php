<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\PagePerformance\JqplotDataGenerator;

use Piwik\ProxyHttp;

/**
 *
 */
class Chart extends \Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\Chart
{
    // the data kept here conforms to the jqplot data layout
    // @see http://www.jqplot.com/docs/files/jqPlotOptions-txt.html
    protected $series = [];
    protected $data = [];
    protected $axes = [];

    // temporary
    public $properties;

    public function setAxisXLabels($xLabels, $xTicks = null, $index = 0)
    {
        $axisName = $this->getXAxis($index);

        $xSteps = $this->properties['x_axis_step_size'];
        $showAllTicks = $this->properties['show_all_ticks'];

        $this->axes[$axisName]['labels'] = array_values($xLabels);

        $ticks = array_values($xTicks ?: $xLabels);

        if (!$showAllTicks) {
            // unset labels so there are $xSteps number of blank ticks between labels
            foreach ($ticks as $i => &$label) {
                if ($i % $xSteps != 0) {
                    $label = ' ';
                }
            }
        }
        $this->axes[$axisName]['ticks'] = $ticks;
    }

    public function setAxisXOnClick(&$onClick)
    {
        $this->axes['xaxis']['onclick'] = & $onClick;
    }

    public function setAxisYUnits($yUnits)
    {
        $yUnits = array_values(array_map('strval', $yUnits));

        // generate axis IDs for each unique y unit
        $axesIds = [];
        foreach ($yUnits as $idx => $unit) {
            if (!isset($axesIds[$unit])) {
                // handle axes ids: first y[]axis, then y[2]axis, y[3]axis...
                $nextAxisId = empty($axesIds) ? '' : count($axesIds) + 1;

                $axesIds[$unit] = 'y' . $nextAxisId . 'axis';
            }
        }

        // generate jqplot axes config
        foreach ($axesIds as $unit => $axisId) {
            $this->axes[$axisId]['tickOptions']['formatString'] = '%s' . $unit;
        }

        // map each series to appropriate yaxis
        foreach ($yUnits as $idx => $unit) {
            $this->series[$idx]['yaxis'] = $axesIds[$unit];
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

    public function render()
    {
        ProxyHttp::overrideCacheControlHeaders();

        // See http://www.jqplot.com/docs/files/jqPlotOptions-txt.html
        $data = [
            'params' => [
                'axes'   => &$this->axes,
                'series' => &$this->series
            ],
            'data'   => &$this->data
        ];

        return $data;
    }

    public function setAxisXLabelsMultiple($xLabels, $seriesToXAxis, $ticks = null)
    {
        foreach ($xLabels as $index => $labels) {
            $this->setAxisXLabels($labels, $ticks === null ? null : $ticks[$index], $index);
        }

        foreach ($seriesToXAxis as $seriesIndex => $xAxisIndex) {
            $axisName = $this->getXAxis($xAxisIndex);

            // don't actually set xaxis otherwise jqplot will show too many axes. however, we need the xaxis labels,
            // so we add them to the jqplot config
            $this->series[$seriesIndex]['_xaxis'] = $axisName;
        }
    }

    private function getXAxis($index)
    {
        $axisName = 'xaxis';
        if ($index != 0) {
            $axisName = 'x' . ($index + 1) . 'axis';
        }
        return $axisName;
    }
}
