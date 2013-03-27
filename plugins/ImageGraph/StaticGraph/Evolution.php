<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_ImageGraph_StaticGraph
 */


/**
 *
 * @package Piwik_ImageGraph_StaticGraph
 */
class Piwik_ImageGraph_StaticGraph_Evolution extends Piwik_ImageGraph_StaticGraph_GridGraph
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
