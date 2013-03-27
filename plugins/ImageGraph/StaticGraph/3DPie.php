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
class Piwik_ImageGraph_StaticGraph_3DPie extends Piwik_ImageGraph_StaticGraph_PieGraph
{
    public function renderGraph()
    {
        $this->initPieGraph(true);

        $this->pieChart->draw3DPie(
            $this->xPosition,
            $this->yPosition,
            $this->pieConfig
        );
    }
}
