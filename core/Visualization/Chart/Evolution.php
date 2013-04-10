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

/**
 * Customize the Evolution chart style
 *
 * @package Piwik
 * @subpackage Piwik_Visualization
 */
class Piwik_Visualization_Chart_Evolution extends Piwik_Visualization_Chart
{

    protected $seriesColors = array('#5170AE', '#F29007', '#CC3399', '#9933CC', '#80a033',
                                    '#246AD2', '#FD16EA', '#49C100');

    public function customizeChartProperties()
    {
        parent::customizeChartProperties();

        // if one column is a percentage we set the grid accordingly
        // note: it is invalid to plot a percentage dataset along with a numeric dataset
        if ($this->yUnit == '%'
            && $this->maxValue > 90
        ) {
            $this->axes['yaxis']['ticks'] = array(0, 50, 100);
        }
    }

    public function getSeriesColors()
    {
        return $this->seriesColors;
    }

    public function setSelectableRows($selectableRows)
    {
        $this->seriesPicker['selectableRows'] = $selectableRows;
    }

}
