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
 * Customize the Evolution chart style
 *
 * @package Piwik
 * @subpackage Piwik_Visualization
 */
class Evolution extends Chart
{
    const SERIES_COLOR_COUNT = 8;

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

    public function setSelectableRows($selectableRows)
    {
        $this->seriesPicker['selectableRows'] = $selectableRows;
    }
}