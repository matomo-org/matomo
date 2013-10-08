<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package StaticGraph
 */
namespace Piwik\Plugins\ImageGraph\StaticGraph;


/**
 *
 * @package StaticGraph
 */
class Evolution extends GridGraph
{

    public function renderGraph()
    {
        $this->initGridChart(
            $displayVerticalGridLines = true,
            $bulletType = LEGEND_FAMILY_LINE,
            $horizontalGraph = false,
            $showTicks = true,
            $verticalLegend = true
        );

        $this->pImage->drawLineChart();
    }
}
