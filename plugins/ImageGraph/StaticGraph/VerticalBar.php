<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ImageGraph\StaticGraph;

/**
 *
 */
class VerticalBar extends GridGraph
{
    const INTERLEAVE = 0.10;

    public function renderGraph()
    {
        $this->initGridChart(
            $displayVerticalGridLines = false,
            $bulletType = LEGEND_FAMILY_BOX,
            $horizontalGraph = false,
            $showTicks = true,
            $verticalLegend = false
        );

        $this->pImage->drawBarChart(
            array(
                 'Interleave' => self::INTERLEAVE,
            )
        );
    }
}
