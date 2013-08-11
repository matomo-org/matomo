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
class Pie3D extends PieGraph
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
