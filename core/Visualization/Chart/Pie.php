<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Visualization\Chart;

use Piwik\Visualization\Chart;

/**
 * Customize & set values for the Flash Pie chart
 *
 * @package Piwik
 * @subpackage Piwik_Visualization
 */
class Pie extends Chart
{
    function customizeChartProperties()
    {
        if (count($this->data) == 0) {
            return;
        }

        // make sure we only have one series
        $series = & $this->series[0];
        $this->series = array(&$series);

        $data = & $this->data[0];
        $this->data = array(&$data);

        // we never plot empty pie slices (eg. visits by server time pie chart)
        foreach ($data as $i => $value) {
            if ($value <= 0) {
                unset($data[$i]);
                unset($this->axes['xaxis']['ticks'][$i]);
            }
        }
        $data = array_values($data);
        $this->axes['xaxis']['ticks'] = array_values($this->axes['xaxis']['ticks']);

        // prepare percentages for tooltip
        $sum = array_sum($data);
        foreach ($data as $i => $value) {
            $value = (float)$value;
            $this->tooltip['percentages'][0][$i] = round(100 * $value / $sum);
        }
    }
}
